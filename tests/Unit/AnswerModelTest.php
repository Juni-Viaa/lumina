<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Chunk;
use App\Models\QueryLog;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_query_log(): void
    {
        $queryLog = QueryLog::factory()->create();
        $answer   = Answer::factory()->for($queryLog, 'queryLog')->create();

        $this->assertInstanceOf(QueryLog::class, $answer->queryLog);
        $this->assertEquals($queryLog->query_id, $answer->queryLog->query_id);
    }

    public function test_uses_soft_deletes(): void
    {
        $answer = Answer::factory()->create();
        $id     = $answer->answer_id;

        $answer->delete();

        $this->assertSoftDeleted('answers', ['answer_id' => $id]);
    }
}