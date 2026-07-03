<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\QueryLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────
    // Access control
    // ─────────────────────────────────────────────────────────────────────

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.index'));

        $response->assertOk();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Ask — happy path
    // ─────────────────────────────────────────────────────────────────────

    public function test_ask_returns_answer_when_rag_server_responds_successfully(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'http://127.0.0.1:5001/health' => Http::response([
                'status'       => 'ok',
                'model_loaded' => true,
            ], 200),
            'http://127.0.0.1:5001/ask' => Http::response([
                'success'          => true,
                'answer'           => 'This is a mocked RAG answer.',
                'sources'          => [['source' => 'doc.pdf', 'page' => 1, 'score' => 0.9]],
                'response_time_ms' => 1200,
                'answer_id'        => 99,
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.ask'), ['question' => 'Apa itu PBL?']);

        $response->assertOk();
        $response->assertJson([
            'answer'           => 'This is a mocked RAG answer.',
            'response_time_ms' => 1200,
            'answer_id'        => 99,
        ]);
        $response->assertJsonStructure(['answer', 'sources', 'response_time_ms', 'query_id', 'answer_id']);

        $this->assertDatabaseHas('queries', [
            'user_id'    => $user->user_id,
            'query_text' => 'Apa itu PBL?',
            'status'     => 'pending', // status update happens in Flask, not Laravel
        ]);
    }

    public function test_ask_creates_query_log_with_generated_title(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'http://127.0.0.1:5001/health' => Http::response(['model_loaded' => true], 200),
            'http://127.0.0.1:5001/ask'    => Http::response(['success' => true, 'answer' => 'OK'], 200),
        ]);

        $question = 'Apa saja syarat kelulusan untuk program sarjana terapan semester delapan';

        $this->actingAs($user)
            ->postJson(route('dashboard.ask'), ['question' => $question]);

        $log = QueryLog::first();
        $this->assertNotNull($log);
        $this->assertStringEndsWith('…', $log->query_title); // truncated with ellipsis
        $this->assertLessThan(mb_strlen($question), mb_strlen($log->query_title));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Ask — RAG server down / error paths
    // ─────────────────────────────────────────────────────────────────────

    public function test_ask_returns_503_when_rag_server_health_check_fails(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'http://127.0.0.1:5001/health' => Http::response(null, 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.ask'), ['question' => 'Test question?']);

        $response->assertStatus(503);
        $response->assertJsonStructure(['answer', 'error']);

        // No query should be persisted if the server isn't even reachable
        $this->assertDatabaseCount('queries', 0);
    }

    public function test_ask_returns_503_when_model_not_loaded_yet(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'http://127.0.0.1:5001/health' => Http::response([
                'status'       => 'ok',
                'model_loaded' => false,
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.ask'), ['question' => 'Test question?']);

        $response->assertStatus(503);
        $response->assertJsonFragment([
            'error' => 'RAG server is starting up — model not loaded yet. Wait a moment and try again.',
        ]);
    }

    public function test_ask_returns_500_when_rag_server_returns_error(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'http://127.0.0.1:5001/health' => Http::response(['model_loaded' => true], 200),
            'http://127.0.0.1:5001/ask'    => Http::response([
                'success' => false,
                'error'   => 'FAISS index not found.',
            ], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.ask'), ['question' => 'Test question?']);

        $response->assertStatus(500);
        $response->assertJsonFragment(['error' => 'FAISS index not found.']);

        // Query row IS created before calling Flask, so it persists even on failure
        $this->assertDatabaseHas('queries', ['query_text' => 'Test question?']);
    }

    public function test_ask_validates_question_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('dashboard.ask'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('question');
    }

    public function test_ask_validates_question_minimum_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.ask'), ['question' => 'a']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('question');
    }

    public function test_ask_validates_question_maximum_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.ask'), ['question' => str_repeat('a', 2001)]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('question');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Show (history detail)
    // ─────────────────────────────────────────────────────────────────────

    public function test_user_can_view_own_query_history_detail(): void
    {
        $user     = User::factory()->create();
        $queryLog = QueryLog::factory()->for($user, 'user')->create();
        Answer::factory()->for($queryLog, 'queryLog')->create();

        $response = $this->actingAs($user)
            ->get(route('dashboard.show', $queryLog->query_id));

        $response->assertOk();
    }

    public function test_user_cannot_view_another_users_query_history(): void
    {
        $owner  = User::factory()->create();
        $intruder = User::factory()->create();
        $queryLog = QueryLog::factory()->for($owner, 'user')->create();

        $response = $this->actingAs($intruder)
            ->get(route('dashboard.show', $queryLog->query_id));

        $response->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────────────
    // History JSON (sidebar)
    // ─────────────────────────────────────────────────────────────────────

    public function test_history_json_returns_max_5_items(): void
    {
        $user = User::factory()->create();
        QueryLog::factory()->for($user, 'user')->count(8)->create();

        $response = $this->actingAs($user)->getJson(route('dashboard.history-json'));

        $response->assertOk();
        $response->assertJsonCount(5, 'items');
    }

    public function test_history_json_only_returns_authenticated_users_queries(): void
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        QueryLog::factory()->for($user, 'user')->count(2)->create();
        QueryLog::factory()->for($otherUser, 'user')->count(3)->create();

        $response = $this->actingAs($user)->getJson(route('dashboard.history-json'));

        $response->assertOk();
        $response->assertJsonCount(2, 'items');
    }

    public function test_history_json_orders_by_most_recent_first(): void
    {
        $user = User::factory()->create();

        $old = QueryLog::factory()->for($user, 'user')->create([
            'created_at' => now()->subDays(2),
        ]);
        $new = QueryLog::factory()->for($user, 'user')->create([
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('dashboard.history-json'));

        $items = $response->json('items');
        $this->assertEquals($new->query_id, $items[0]['query_id']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // History page
    // ─────────────────────────────────────────────────────────────────────

    public function test_history_page_paginates_results(): void
    {
        $user = User::factory()->create();
        QueryLog::factory()->for($user, 'user')->count(25)->create();

        $response = $this->actingAs($user)->get(route('history.index'));

        $response->assertOk();
        // paginate(20) means only 20 of the 25 should appear in the view data
        $response->assertViewHas('chatHistory', function ($paginator) {
            return $paginator->count() === 20;
        });
    }
}