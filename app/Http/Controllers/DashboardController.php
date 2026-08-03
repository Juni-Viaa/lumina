<?php

namespace App\Http\Controllers;

use App\Models\QueryLog;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const RAG_SERVER = 'http://127.0.0.1:5001';

    // ─────────────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $initialMessages = [];
        return view('dashboard.index', compact('initialMessages'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function show(QueryLog $queryLog): View
    {
        if ((int) $queryLog->user_id !== (int) Auth::id()) abort(403);

        $answer          = Answer::where('query_id', $queryLog->query_id)->first();
        $initialMessages = [
            ['role' => 'user',      'content' => $queryLog->query_text],
            ['role' => 'assistant', 'content' => $answer?->answer_text ?? ''],
        ];

        return view('dashboard.index', compact('initialMessages'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Kicks off a question. Fire-and-forget ke Flask — tidak menunggu jawaban
     * selesai di sini. Frontend mengambil progres real-time lewat SSE di
     * streamQueryLogs() dan menerima jawaban akhir lewat event 'done'.
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $question = trim($request->input('question'));
        $userId   = Auth::id();

        // 1. Check RAG server health before doing anything ────────────────────
        $serverCheck = $this->checkServer();
        if (! $serverCheck['online']) {
            return response()->json([
                'answer' => null,
                'error'  => $serverCheck['error'],
            ], 503);
        }

        // 2. Persist query row ─────────────────────────────────────────────────
        try {
            $queryLog = QueryLog::create([
                'user_id'     => $userId,
                'query_text'  => $question,
                'query_title' => $this->makeTitle($question),
                'status'      => 'pending',
            ]);
        } catch (\Throwable $e) {
            Log::error('DashboardController@ask: QueryLog::create failed', [
                'user_id'   => $userId,
                'question'  => $question,
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'answer' => null,
                'error'  => 'Terjadi kesalahan saat menyimpan pertanyaan. Silakan coba lagi.',
            ], 500);
        }

        // 3. Dispatch ke Flask, TIDAK menunggu jawabannya di sini ──────────────
        $this->dispatchAsk($queryLog->query_id, $question);

        return response()->json([
            'query_id' => $queryLog->query_id,
            'status'   => 'processing',
        ], 202);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * SSE stream untuk progres satu pertanyaan — menampilkan tahap nyata
     * yang sedang dijalankan Python (retrieve/generate/save), lalu event
     * 'done' berisi jawaban akhir begitu selesai.
     */
    /**
     * Endpoint status sederhana untuk di-poll frontend (fetch biasa, bukan
     * SSE) — jauh lebih tahan banting lintas hosting/proxy. Begitu status
     * jadi answered/failed, jawabannya ikut disertakan di respons ini.
     */
    public function queryStatus(Request $request, $queryId): JsonResponse
    {
        $query = DB::table('queries')
            ->where('query_id', $queryId)
            ->where('user_id', Auth::id())
            ->first(['status', 'current_step', 'response_time_ms']);

        if (! $query) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $payload = [
            'status' => $query->status,
            'step'   => $query->current_step,
        ];

        if ($query->status === 'answered') {
            $answer = DB::table('answers')
                ->where('query_id', $queryId)
                ->orderByDesc('answer_id')
                ->first();

            $payload['answer']           = $answer->answer_text ?? '';
            $payload['answer_id']        = $answer->answer_id ?? null;
            $payload['response_time_ms'] = $query->response_time_ms;
        }

        return response()->json($payload);
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function history(): View
    {
        $chatHistory = QueryLog::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('history.index', compact('chatHistory'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function historyJson(): JsonResponse
    {
        try {
            $items = QueryLog::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['query_id', 'query_title', 'query_text', 'created_at'])
                ->map(fn ($q) => [
                    'query_id'   => $q->query_id,
                    'title'      => $q->display_title,
                    'full_title' => $q->query_text,
                ]);

            return response()->json(['items' => $items]);

        } catch (\Throwable $e) {
            Log::error('DashboardController@historyJson: Failed to fetch history', [
                'user_id'   => Auth::id(),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json(['items' => []]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function checkServer(): array
    {
        try {
            $response = Http::timeout(3)->get(self::RAG_SERVER . '/health');

            if ($response->successful()) {
                $body = $response->json();

                if (! ($body['model_loaded'] ?? false)) {
                    Log::warning('DashboardController@checkServer: Model not loaded yet', [
                        'health_body' => $body,
                    ]);

                    return [
                        'online' => false,
                        'error'  => 'Server AI sedang memuat model. Mohon tunggu sebentar dan coba lagi.',
                    ];
                }

                return ['online' => true];
            }

            Log::warning('DashboardController@checkServer: Health check returned non-200', [
                'http_status' => $response->status(),
                'body'        => $response->body(),
            ]);

            return [
                'online' => false,
                'error'  => 'Server AI merespons dengan error. Periksa log rag_server.py.',
            ];

        } catch (\Throwable $e) {
            Log::warning('DashboardController@checkServer: Cannot reach RAG server', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'online' => false,
                'error'  => 'Server AI tidak aktif. Jalankan: python ai/flask_api.py',
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fire-and-forget dispatch ke Flask /ask — sama polanya dengan
     * UploadController::dispatchIngest(). Laravel tidak menunggu balasan
     * di sini; progres diambil lewat streamQueryLogs().
     */
    private function dispatchAsk(int $queryId, string $question): void
    {
        $payload = json_encode([
            'question' => $question,
            'query_id' => $queryId,
        ]);

        try {
            $host   = '127.0.0.1';
            $port   = 5001;
            $length = strlen($payload);

            $http = "POST /ask HTTP/1.1\r\n"
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

                Log::info('DashboardController@dispatchAsk: Ask dispatched to Flask', [
                    'query_id' => $queryId,
                ]);
            } else {
                Log::error('DashboardController@dispatchAsk: Cannot connect to Flask server', [
                    'query_id' => $queryId,
                    'errno'    => $errno,
                    'errstr'   => $errstr,
                ]);

                DB::table('queries')->where('query_id', $queryId)->update(['status' => 'failed']);
            }
        } catch (\Throwable $e) {
            Log::error('DashboardController@dispatchAsk: Exception during socket dispatch', [
                'query_id'  => $queryId,
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            DB::table('queries')->where('query_id', $queryId)->update(['status' => 'failed']);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function makeTitle(string $question): string
    {
        $words = explode(' ', $question);
        $title = implode(' ', array_slice($words, 0, 8));
        return mb_strlen($title) < mb_strlen($question) ? $title . '…' : $title;
    }
}