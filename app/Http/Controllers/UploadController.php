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

    private const MAX_SIZE_KB = 102400;
    private const RAG_SERVER  = 'http://127.0.0.1:5001';

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
            return response()->json([
                'message' => 'Tipe file tidak didukung.',
            ], 422);
        }

        $userId    = Auth::id();
        $directory = "documents/{$userId}";
        $safeName  = preg_replace('/[^A-Za-z0-9._\-]/', '_', $file->getClientOriginalName());

        try {
            $storedPath   = $file->storeAs($directory, $safeName, 'local');
            $absolutePath = Storage::disk('local')->path($storedPath);
        } catch (\Throwable $e) {
            Log::error('UploadController@store: File storage failed', [
                'user_id'   => $userId,
                'filename'  => $file->getClientOriginalName(),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Gagal menyimpan file. Silakan coba lagi.',
            ], 500);
        }

        try {
            // Upsert — bypass Eloquent events to prevent soft-delete-on-insert bug
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
        } catch (\Throwable $e) {
            Log::error('UploadController@store: Database upsert failed', [
                'user_id'   => $userId,
                'filename'  => $file->getClientOriginalName(),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Gagal menyimpan data dokumen. Silakan coba lagi.',
            ], 500);
        }

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

        try {
            // 1. Remove physical file
            Storage::disk('local')->delete($upload->path_file);

            // 2. Hard-delete all chunks (must be hard-deleted for FAISS rebuild to work)
            DB::table('chunks')
                ->where('document_id', $upload->document_id)
                ->delete();

            // 3. Soft-delete the document record
            $upload->delete();

        } catch (\Throwable $e) {
            Log::error('UploadController@destroy: Failed to delete document', [
                'document_id' => $upload->document_id,
                'user_id'     => Auth::id(),
                'exception'   => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Gagal menghapus dokumen. Silakan coba lagi.',
            ], 500);
        }

        // 4. Schedule FAISS rebuild in background (fire-and-forget)
        $this->dispatchFaissRebuild($upload->document_id);

        return response()->json(['message' => 'Dokumen berhasil dihapus.']);
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
            Log::error('UploadController@list: Failed to fetch document list', [
                'user_id'   => Auth::id(),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fire-and-forget ingest request to Flask via raw socket.
     * Returns immediately — Laravel does not wait for Flask to finish.
     * The UI polls /upload/list for status changes.
     */
    private function dispatchIngest(int $documentId, string $absolutePath, int $userId): void
    {
        $payload = json_encode([
            'file_path'   => $absolutePath,
            'document_id' => $documentId,
            'user_id'     => $userId,
        ]);

        try {
            $host   = '127.0.0.1';
            $port   = 5001;
            $length = strlen($payload);

            $http = "POST /ingest HTTP/1.1\r\n"
                  . "Host: {$host}:{$port}\r\n"
                  . "Content-Type: application/json\r\n"
                  . "Content-Length: {$length}\r\n"
                  . "Connection: close\r\n"
                  . "\r\n"
                  . $payload;

            $socket = fsockopen($host, $port, $errno, $errstr, 3);
            if ($socket) {
                fwrite($socket, $http);
                fclose($socket);

                Log::info('UploadController@dispatchIngest: Ingest dispatched to Flask', [
                    'document_id' => $documentId,
                    'file'        => basename($absolutePath),
                ]);
            } else {
                // fsockopen failed to connect — Flask not running
                Log::error('UploadController@dispatchIngest: Cannot connect to Flask server', [
                    'document_id' => $documentId,
                    'errno'       => $errno,
                    'errstr'      => $errstr,
                ]);

                DB::table('documents')
                    ->where('document_id', $documentId)
                    ->update(['status' => 'failed']);
            }
        } catch (\Throwable $e) {
            Log::error('UploadController@dispatchIngest: Exception during socket dispatch', [
                'document_id' => $documentId,
                'exception'   => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            DB::table('documents')
                ->where('document_id', $documentId)
                ->update(['status' => 'failed']);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fire-and-forget FAISS rebuild request after document deletion.
     * Uses 60-second debounce on the Flask side — rapid deletions
     * trigger only one rebuild.
     */
    private function dispatchFaissRebuild(int $documentId): void
    {
        $body = '{}';

        try {
            $host = '127.0.0.1';
            $port = 5001;

            $http = "POST /rebuild-index HTTP/1.1\r\n"
                  . "Host: {$host}:{$port}\r\n"
                  . "Content-Type: application/json\r\n"
                  . "Content-Length: " . strlen($body) . "\r\n"
                  . "Connection: close\r\n"
                  . "\r\n"
                  . $body;

            $socket = fsockopen($host, $port, $errno, $errstr, 3);
            if ($socket) {
                fwrite($socket, $http);
                fclose($socket);

                Log::info('UploadController@dispatchFaissRebuild: Rebuild scheduled', [
                    'document_id' => $documentId,
                ]);
            } else {
                Log::warning('UploadController@dispatchFaissRebuild: Cannot connect to Flask — index may be stale', [
                    'document_id' => $documentId,
                    'errno'       => $errno,
                    'errstr'      => $errstr,
                ]);
            }
        } catch (\Throwable $e) {
            // Non-fatal — deletion already succeeded in the DB.
            // Index will be stale until Flask restarts or next rebuild.
            Log::warning('UploadController@dispatchFaissRebuild: Exception during socket dispatch', [
                'document_id' => $documentId,
                'exception'   => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
        }
    }
}