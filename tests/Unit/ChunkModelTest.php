<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Chunk;
use App\Models\QueryLog;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChunkModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_document(): void
    {
        $upload = Upload::factory()->create();
        $chunk  = Chunk::factory()->for($upload, 'document')->create();

        $this->assertInstanceOf(Upload::class, $chunk->document);
        $this->assertEquals($upload->document_id, $chunk->document->document_id);
    }

    public function test_uses_soft_deletes(): void
    {
        $chunk = Chunk::factory()->create();
        $id    = $chunk->chunk_id;

        $chunk->delete();

        $this->assertSoftDeleted('chunks', ['chunk_id' => $id]);
    }

    public function test_chunk_text_is_required_in_fillable(): void
    {
        $chunk = Chunk::factory()->make(['chunk_text' => 'Sample text content']);
        $this->assertEquals('Sample text content', $chunk->chunk_text);
    }
}