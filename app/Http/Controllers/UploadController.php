<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    private const ALLOWED_MIMES = [
        'application/pdf'                                                          => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/msword'                                                       => 'doc',
        'text/plain'                                                               => 'txt',
    ];

    private const MAX_SIZE_KB = 102400;

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
                'max:'    . self::MAX_SIZE_KB,
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
        $existing = \DB::table('documents')
            ->where('user_id', $userId)
            ->where('document_name', $file->getClientOriginalName())
            ->first();

        if ($existing) {
            \DB::table('documents')
                ->where('document_id', $existing->document_id)
                ->update([
                    'deleted_at' => null,
                    'path_file'  => $storedPath,
                    'file_type'  => $file->getClientOriginalExtension(),
                    'size'       => $file->getSize(),
                    'status'     => 'processing',
                    'edited_at'  => now(),
                ]);
            $documentId = $existing->document_id;
        } else {
            $documentId = \DB::table('documents')->insertGetId([
                'user_id'       => $userId,
                'document_name' => $file->getClientOriginalName(),
                'path_file'     => $storedPath,
                'file_type'     => $file->getClientOriginalExtension(),
                'size'          => $file->getSize(),
                'status'        => 'processing',
                'created_at'    => now(),
                'edited_at'     => now(),
                'deleted_at'    => null,
            ]);
        }

        $ingestResult = $this->dispatchIngest($documentId, $absolutePath, $userId);

        Log::info('Upload complete', [
            'document_id'  => $documentId,
            'file_exists'  => file_exists($absolutePath),
            'ingest_error' => $ingestResult['error'] ?? null,
        ]);

        return response()->json([
            'message'     => 'Dokumen berhasil diupload dan sedang diproses.',
            'document_id' => $documentId,
            'status'      => 'processing',
            'debug'       => $ingestResult,
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(Upload $upload): JsonResponse
    {
        if ((int) $upload->user_id !== (int) Auth::id()) {
            abort(403);
        }

        Storage::disk('local')->delete($upload->path_file);
        $upload->delete();

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
            Log::error('Upload list error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function dispatchIngest(int $documentId, string $absolutePath, int $userId): array
    {
        $python  = config('rag.python_path', 'python');
        $script  = config('rag.ingest_script', base_path('ai/ingest.py'));
        $logOut  = storage_path('logs/ingest_out.log');
        $logErr  = storage_path('logs/ingest_err.log');

        if (! file_exists($script)) {
            $error = "ingest.py not found at: {$script}";
            Log::error($error);
            return ['command' => null, 'error' => $error];
        }

        if (! file_exists($absolutePath)) {
            $error = "Uploaded file not found at: {$absolutePath}";
            Log::error($error);
            return ['command' => null, 'error' => $error];
        }

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Write a temporary .bat file and execute it.
                // This avoids ALL PowerShell/cmd escaping hell — the bat file
                // contains the exact command with no quoting layers.
                $batPath = storage_path("logs/ingest_{$documentId}.bat");
                $logOutWin = str_replace('/', '\\', $logOut);
                $logErrWin = str_replace('/', '\\', $logErr);

                $batContent = "@echo off\r\n"
                    . "set PYTHONIOENCODING=utf-8\r\n"
                    . "set PYTHONUTF8=1\r\n"
                    . "set DB_HOST=" . env('DB_HOST', '127.0.0.1') . "\r\n"
                    . "set DB_PORT=" . env('DB_PORT', '3306') . "\r\n"
                    . "set DB_USERNAME=" . env('DB_USERNAME', 'root') . "\r\n"
                    . "set DB_PASSWORD=" . env('DB_PASSWORD', '') . "\r\n"
                    . "set DB_DATABASE=" . env('DB_DATABASE', 'lumina') . "\r\n"
                    . "\"{$python}\" -X utf8 \"{$script}\""
                    . " --document-id {$documentId}"
                    . " --user-id {$userId}"
                    . " \"{$absolutePath}\""
                    . " 1>\"{$logOutWin}\""
                    . " 2>\"{$logErrWin}\"\r\n";

                file_put_contents($batPath, $batContent);

                // start /B runs the bat in background, detached from PHP
                $cmd = "start /B \"\" \"{$batPath}\"";
                pclose(popen($cmd, 'r'));

                Log::info('Ingest bat dispatched', [
                    'document_id' => $documentId,
                    'bat_file'    => $batPath,
                    'bat_content' => $batContent,
                ]);

                return ['command' => $batContent, 'bat_path' => $batPath, 'error' => null];

            } else {
                $cmd = implode(' ', [
                    escapeshellarg($python),
                    escapeshellarg($script),
                    '--document-id', (string) $documentId,
                    '--user-id',     (string) $userId,
                    escapeshellarg($absolutePath),
                    '>>', escapeshellarg($logOut),
                    '2>>', escapeshellarg($logErr),
                    '&',
                ]);
                exec($cmd);

                return ['command' => $cmd, 'error' => null];
            }

        } catch (\Throwable $e) {
            Log::error('Ingest dispatch failed', ['error' => $e->getMessage()]);
            return ['command' => null, 'error' => $e->getMessage()];
        }
    }
}