<?php

namespace App\Http\Controllers;

use App\Models\QueryLog;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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

        // 3. Call Flask RAG server ─────────────────────────────────────────────
        try {
            $response = Http::timeout(120)
                ->post(self::RAG_SERVER . '/ask', [
                    'question' => $question,
                    'query_id' => $queryLog->query_id,
                ]);

            $result = $response->json();

            if (! $response->successful() || ! ($result['success'] ?? false)) {
                Log::error('DashboardController@ask: RAG server returned error', [
                    'query_id'    => $queryLog->query_id,
                    'http_status' => $response->status(),
                    'rag_error'   => $result['error'] ?? null,
                    'rag_body'    => $result,
                ]);

                return response()->json([
                    'answer'   => null,
                    'error'    => 'Lumina tidak dapat memproses pertanyaan Anda saat ini. Silakan coba lagi.',
                    'query_id' => $queryLog->query_id,
                ], 500);
            }

            return response()->json([
                'answer'           => $result['answer'],
                'sources'          => $result['sources']          ?? [],
                'response_time_ms' => $result['response_time_ms'] ?? null,
                'query_id'         => $queryLog->query_id,
                'answer_id'        => $result['answer_id']        ?? null,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DashboardController@ask: Cannot connect to RAG server', [
                'query_id'  => $queryLog->query_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'answer' => null,
                'error'  => 'Server AI tidak dapat dihubungi. Pastikan rag_server.py sedang berjalan.',
            ], 503);

        } catch (\Throwable $e) {
            Log::error('DashboardController@ask: Unexpected error', [
                'query_id'  => $queryLog->query_id,
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'answer' => null,
                'error'  => 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
            ], 500);
        }
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
                'error'  => 'Server AI tidak aktif. Jalankan: python ai/rag_server.py',
            ];
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