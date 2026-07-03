<?php

namespace Tests\Unit;

use App\Models\Chunk;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $user   = User::factory()->create();
        $upload = Upload::factory()->for($user, 'user')->create();

        $this->assertInstanceOf(User::class, $upload->user);
        $this->assertEquals($user->user_id, $upload->user->user_id);
    }

    public function test_has_many_chunks(): void
    {
        $upload = Upload::factory()->create();
        Chunk::factory()->for($upload, 'document')->count(4)->create();

        $this->assertCount(4, $upload->chunks);
        $this->assertInstanceOf(Chunk::class, $upload->chunks->first());
    }

    public function test_size_human_formats_bytes(): void
    {
        $upload = Upload::factory()->make(['size' => 500]);
        $this->assertEquals('500 B', $upload->size_human);
    }

    public function test_size_human_formats_kilobytes(): void
    {
        $upload = Upload::factory()->make(['size' => 2048]); // 2 KB
        $this->assertEquals('2 KB', $upload->size_human);
    }

    public function test_size_human_formats_megabytes(): void
    {
        $upload = Upload::factory()->make(['size' => 5_242_880]); // 5 MB
        $this->assertEquals('5 MB', $upload->size_human);
    }

    public function test_uses_soft_deletes(): void
    {
        $upload = Upload::factory()->create();
        $id     = $upload->document_id;

        $upload->delete();

        $this->assertSoftDeleted('documents', ['document_id' => $id]);
        $this->assertNull(Upload::find($id)); // excluded from default query
        $this->assertNotNull(Upload::withTrashed()->find($id)); // still in DB
    }

    public function test_restore_brings_back_soft_deleted_document(): void
    {
        $upload = Upload::factory()->create();
        $upload->delete();
        $upload->restore();

        $this->assertNotNull(Upload::find($upload->document_id));
        $this->assertNull($upload->fresh()->deleted_at);
    }

    public function test_uses_custom_timestamp_column_names(): void
    {
        $upload = Upload::factory()->create();

        $this->assertNotNull($upload->created_at);
        
        $original = $upload->updated_at;
        sleep(1);
        $upload->update(['status' => 'failed']);

        $this->assertTrue($upload->fresh()->updated_at->gt($original));
    }
}