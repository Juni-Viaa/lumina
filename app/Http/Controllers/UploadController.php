<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UploadController extends Controller
{
    private const ALLOWED_MIMES = [
        'application/pdf'                                                          => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/msword'                                                       => 'doc',
        'text/plain'                                                               => 'txt',
    ];

    private const MAX_SIZE_KB  = 102400;
    private const RAG_SERVER   = 'http://127.0.0.1:5001';

    // ─────────────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $documents = Upload::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('upload.index', compact('documents'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'document' => [
                'required',
                'file',
                'max:'  . self::MAX_SIZE_KB,
                'mimes:pdf,doc,docx,txt',
            ],
        ]);

        $file = $request->file('document');

        if (! array_key_exists($file->getMimeType(), self::ALLOWED_MIMES)) {
            return response()->json(['message' => 'Tipe file tidak didukung.'], 422);
        }

        $userId    = Auth::id();
        $directory = "documents/{$userId}";
        $safeName  = preg_replace('/[^A-Za-z0-9._\-]/', '_', $file->getClientOriginalName());

        $storedPath   = $file->storeAs($directory, $safeName, 'local');
        $absolutePath = Storage::disk('local')->path($storedPath);

        // Upsert — bypass Eloquent events entirely using raw DB
        $existing = DB::table('documents')
            ->where('user_id', $userId)
            ->where('document_name', $file->getClientOriginalName())
            ->first();

        if ($existing) {
            DB::table('documents')
                ->where('document_id', $existing->document_id)
                ->update([
                    'deleted_at' => null,
                    'path_file'  => $storedPath,
                    'file_type'  => $file->getClientOriginalExtension(),
                    'size'       => $file->getSize(),
                    'status'     => 'processing',
                    'updated_at'  => now(),
                ]);
            $documentId = $existing->document_id;
        } else {
            $documentId = DB::table('documents')->insertGetId([
                'user_id'       => $userId,
                'document_name' => $file->getClientOriginalName(),
                'path_file'     => $storedPath,
                'file_type'     => $file->getClientOriginalExtension(),
                'size'          => $file->getSize(),
                'status'        => 'processing',
                'created_at'    => now(),
                'updated_at'     => now(),
                'deleted_at'    => null,
            ]);
        }

        // Call Flask /ingest endpoint
        // Run async so the HTTP response returns immediately to the UI
        // The UI polls the document status separately
        $this->dispatchIngest($documentId, $absolutePath, $userId);

        return response()->json([
            'message'     => 'Dokumen berhasil diupload dan sedang diproses.',
            'document_id' => $documentId,
            'status'      => 'processing',
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(Upload $upload): JsonResponse
    {
        if ((int) $upload->user_id !== (int) Auth::id()) {
            abort(403);
        }

        // 1. Remove physical file from storage
        Storage::disk('local')->delete($upload->path_file);

        // 2. Hard-delete all chunks from MySQL
        DB::table('chunks')
            ->where('document_id', $upload->document_id)
            ->delete();

        // 3. Soft-delete the document record
        $upload->delete();

        // 4. Dispatch FAISS rebuild in the background (fire and forget)
        // The HTTP response returns immediately — user doesn't wait for rebuild
        $this->dispatchFaissRebuild();

        return response()->json(['message' => 'Dokumen berhasil dihapus.']);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fire a non-blocking socket request to /rebuild-index.
     * PHP closes the socket immediately without reading the response,
     * so Laravel returns to the user while Flask rebuilds in the background.
     */
    private function dispatchFaissRebuild(): void
    {
        try {
            $host = '127.0.0.1';
            $port = 5001;
            $path = '/rebuild-index';
            $body = '{}';

            $http = "POST {$path} HTTP/1.1\r\n"
                  . "Host: {$host}:{$port}\r\n"
                  . "Content-Type: application/json\r\n"
                  . "Content-Length: " . strlen($body) . "\r\n"
                  . "Connection: close\r\n"
                  . "\r\n"
                  . $body;

            $socket = fsockopen($host, $port, $errno, $errstr, 3);
            if ($socket) {
                fwrite($socket, $http);
                fclose($socket); // close immediately — don't wait for response
            }

            Log::info('FAISS rebuild dispatched in background after document deletion');

        } catch (\Throwable $e) {
            // Non-fatal — DB deletion already succeeded
            // Index will be stale until next rebuild or server restart
            Log::error('FAISS rebuild dispatch failed', ['error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function list(): JsonResponse
    {
        try {
            $documents = Upload::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->get(['document_id', 'document_name', 'file_type', 'size', 'status', 'created_at']);

            return response()->json($documents);
        } catch (\Throwable $e) {
            Log::error('Upload list error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Dispatch ingest to Flask server as a fire-and-forget background call.
     * We use a non-blocking socket so the upload HTTP response returns
     * immediately while Flask processes the document in the background.
     */
    private function dispatchIngest(int $documentId, string $absolutePath, int $userId): void
    {
        $payload = json_encode([
            'file_path'   => $absolutePath,
            'document_id' => $documentId,
            'user_id'     => $userId,
        ]);

        // Fire-and-forget using raw socket — does NOT wait for response.
        // Flask processes the ingest in the background.
        // The UI polls /upload/list to detect when status changes to 'indexed'.
        try {
            $host    = '127.0.0.1';
            $port    = 5001;
            $path    = '/ingest';
            $length  = strlen($payload);

            $http = "POST {$path} HTTP/1.1\r\n"
                  . "Host: {$host}:{$port}\r\n"
                  . "Content-Type: application/json\r\n"
                  . "Content-Length: {$length}\r\n"
                  . "Connection: close\r\n"
                  . "\r\n"
                  . $payload;

            $socket = fsockopen($host, $port, $errno, $errstr, 3);
            if ($socket) {
                fwrite($socket, $http);
                fclose($socket); // Close immediately — don't wait for response
            }

            Log::info('Ingest dispatched to Flask', [
                'document_id' => $documentId,
                'file'        => basename($absolutePath),
            ]);

        } catch (\Throwable $e) {
            // If Flask isn't running, mark document as failed
            Log::error('Ingest dispatch failed', [
                'document_id' => $documentId,
                'error'       => $e->getMessage(),
            ]);

            DB::table('documents')
                ->where('document_id', $documentId)
                ->update(['status' => 'failed']);
        }
    }
}