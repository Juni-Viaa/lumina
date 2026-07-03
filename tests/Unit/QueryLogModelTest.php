<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\QueryLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueryLogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $user     = User::factory()->create();
        $queryLog = QueryLog::factory()->for($user, 'user')->create();

        $this->assertInstanceOf(User::class, $queryLog->user);
        $this->assertEquals($user->user_id, $queryLog->user->user_id);
    }

    public function test_has_one_answer(): void
    {
        $queryLog = QueryLog::factory()->create();
        $answer   = Answer::factory()->for($queryLog, 'queryLog')->create();

        $this->assertInstanceOf(Answer::class, $queryLog->answer);
        $this->assertEquals($answer->answer_id, $queryLog->answer->answer_id);
    }

    public function test_display_title_uses_query_title_when_present(): void
    {
        $queryLog = QueryLog::factory()->make([
            'query_title' => 'Custom Title',
            'query_text'  => 'Some very long question text here',
        ]);

        $this->assertEquals('Custom Title', $queryLog->display_title);
    }

    public function test_display_title_falls_back_to_truncated_query_text(): void
    {
        $longText = str_repeat('a', 100);

        $queryLog = QueryLog::factory()->make([
            'query_title' => null,
            'query_text'  => $longText,
        ]);

        $this->assertNotEquals($longText, $queryLog->display_title);
        $this->assertLessThanOrEqual(63, mb_strlen($queryLog->display_title)); // Str::limit default + "..."
    }

    public function test_uses_soft_deletes(): void
    {
        $queryLog = QueryLog::factory()->create();
        $id       = $queryLog->query_id;

        $queryLog->delete();

        $this->assertSoftDeleted('queries', ['query_id' => $id]);
    }

    public function test_default_status_can_be_pending(): void
    {
        $queryLog = QueryLog::factory()->pending()->create();

        $this->assertEquals('pending', $queryLog->status);
        $this->assertNull($queryLog->response_time_ms);
    }

    public function test_response_time_ms_is_cast_to_integer(): void
    {
        $queryLog = QueryLog::factory()->create(['response_time_ms' => '1500']);

        $this->assertIsInt($queryLog->response_time_ms);
        $this->assertEquals(1500, $queryLog->response_time_ms);
    }
}
