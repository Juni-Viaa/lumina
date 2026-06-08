<?php

namespace App\Http\Controllers;

use App\Models\QueryLog;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // RAG server base URL — must match rag_server.py port
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

        // 1. Check RAG server is reachable before doing anything ──────────────
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
            Log::error('QueryLog::create failed', ['error' => $e->getMessage()]);
            return response()->json([
                'answer' => null,
                'error'  => 'Database error: ' . $e->getMessage(),
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
                Log::error('RAG server error', [
                    'status'   => $response->status(),
                    'query_id' => $queryLog->query_id,
                    'result'   => $result,
                ]);
                return response()->json([
                    'answer'   => null,
                    'error'    => $result['error'] ?? 'RAG server returned an error.',
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
            Log::error('RAG server connection failed', ['error' => $e->getMessage()]);
            return response()->json([
                'answer' => null,
                'error'  => 'Could not connect to RAG server. Is rag_server.py running?',
            ], 503);
        } catch (\Throwable $e) {
            Log::error('RAG ask failed', ['error' => $e->getMessage()]);
            return response()->json([
                'answer' => null,
                'error'  => 'Unexpected error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function history(): View
    {
        $chatHistory = QueryLog::where('user_id', Auth::id())
            ->orderByDesc('created_at')->paginate(20);

        return view('history.index', compact('chatHistory'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function historyJson(): JsonResponse
    {
        $items = QueryLog::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['query_id', 'query_title', 'query_text', 'created_at'])
            ->map(fn ($q) => [
                'query_id'   => $q->query_id,
                'title'      => $q->display_title,        // truncated for sidebar
                'full_title' => $q->query_text,           // full text for tooltip
            ]);

        return response()->json(['items' => $items]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Check if the Flask RAG server is running.
     * Returns ['online' => true] or ['online' => false, 'error' => '...']
     */
    private function checkServer(): array
    {
        try {
            $response = Http::timeout(3)->get(self::RAG_SERVER . '/health');

            if ($response->successful()) {
                $body = $response->json();

                if (! ($body['model_loaded'] ?? false)) {
                    return [
                        'online' => false,
                        'error'  => 'RAG server is starting up — model not loaded yet. Wait a moment and try again.',
                    ];
                }

                return ['online' => true];
            }

            return [
                'online' => false,
                'error'  => 'RAG server returned HTTP ' . $response->status() . '. Check rag_server.py logs.',
            ];

        } catch (\Throwable) {
            return [
                'online' => false,
                'error'  => 'RAG server is not running. Start it with: python ai/rag_server.py',
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