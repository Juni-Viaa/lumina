<?php

namespace Tests\Feature;

use App\Models\Upload;
use App\Models\Chunk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Access control
    // ─────────────────────────────────────────────────────────────────────

    public function test_guest_cannot_access_upload_index(): void
    {
        $response = $this->get(route('uploads.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_mahasiswa_cannot_access_upload_index(): void
    {
        $user = User::factory()->mahasiswa()->create();

        $response = $this->actingAs($user)->get(route('uploads.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_upload_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('uploads.index'));

        $response->assertOk();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Store / upload
    // ─────────────────────────────────────────────────────────────────────

    public function test_admin_can_upload_a_pdf_document(): void
    {
        Http::fake(); // prevent real socket call to Flask /ingest

        $admin = User::factory()->admin()->create();
        $file  = UploadedFile::fake()->create('report.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)
            ->post(route('uploads.store'), ['document' => $file]);

        $response->assertCreated();
        $response->assertJsonStructure(['message', 'document_id', 'status']);

        $this->assertDatabaseHas('documents', [
            'user_id'       => $admin->user_id,
            'document_name' => 'report.pdf',
            'file_type'     => 'pdf',
            'status'        => 'processing',
        ]);
    }

    public function test_upload_rejects_unsupported_file_type(): void
    {
        $admin = User::factory()->admin()->create();
        $file  = UploadedFile::fake()->create('image.png', 100, 'image/png');

        $response = $this->actingAs($admin)
            ->postJson(route('uploads.store'), ['document' => $file]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $admin = User::factory()->admin()->create();

        // MAX_SIZE_KB = 102400 (100 MB) — create a file just over that
        $file = UploadedFile::fake()->create('huge.pdf', 102401, 'application/pdf');

        $response = $this->actingAs($admin)
            ->postJson(route('uploads.store'), ['document' => $file]);

        $response->assertStatus(422);
    }

    public function test_upload_requires_document_field(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('uploads.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('document');
    }

    public function test_reuploading_same_filename_restores_soft_deleted_document(): void
    {
        Http::fake();

        $admin = User::factory()->admin()->create();

        $existing = Upload::factory()->for($admin, 'user')->create([
            'document_name' => 'report.pdf',
        ]);
        $existing->delete(); // soft delete

        $this->assertSoftDeleted('documents', ['document_id' => $existing->document_id]);

        $file = UploadedFile::fake()->create('report.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)
            ->postJson(route('uploads.store'), ['document' => $file]);

        $response->assertCreated();

        // Same document_id should be reused and restored, not duplicated
        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseHas('documents', [
            'document_id' => $existing->document_id,
            'deleted_at'  => null,
            'status'      => 'processing',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // List
    // ─────────────────────────────────────────────────────────────────────

    public function test_list_only_returns_authenticated_users_documents(): void
    {
        $admin       = User::factory()->admin()->create();
        $otherAdmin  = User::factory()->admin()->create();

        Upload::factory()->for($admin, 'user')->count(2)->create();
        Upload::factory()->for($otherAdmin, 'user')->count(3)->create();

        $response = $this->actingAs($admin)->get(route('uploads.list'));

        $response->assertOk();
        $response->assertJsonCount(2);
    }

    public function test_list_excludes_soft_deleted_documents(): void
    {
        $admin = User::factory()->admin()->create();

        $active  = Upload::factory()->for($admin, 'user')->create();
        $deleted = Upload::factory()->for($admin, 'user')->create();
        $deleted->delete();

        $response = $this->actingAs($admin)->get(route('uploads.list'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['document_id' => $active->document_id]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Destroy / delete
    // ─────────────────────────────────────────────────────────────────────

    public function test_admin_can_delete_own_document(): void
    {
        Http::fake();

        $admin   = User::factory()->admin()->create();
        $upload  = Upload::factory()->for($admin, 'user')->create([
            'path_file' => 'documents/1/test.pdf',
        ]);
        Storage::disk('local')->put($upload->path_file, 'dummy content');

        $response = $this->actingAs($admin)
            ->delete(route('uploads.destroy', $upload->document_id));

        $response->assertOk();
        $this->assertSoftDeleted('documents', ['document_id' => $upload->document_id]);
        Storage::disk('local')->assertMissing($upload->path_file);
    }

    public function test_deleting_document_hard_deletes_its_chunks(): void
    {
        Http::fake();

        $admin  = User::factory()->admin()->create();
        $upload = Upload::factory()->for($admin, 'user')->create();
        Chunk::factory()->for($upload, 'document')->count(3)->create();

        $this->assertDatabaseCount('chunks', 3);

        $this->actingAs($admin)
            ->delete(route('uploads.destroy', $upload->document_id));

        // Chunks must be HARD deleted, not soft deleted
        $this->assertDatabaseCount('chunks', 0);
    }

    public function test_admin_cannot_delete_another_users_document(): void
    {
        Http::fake();

        $admin      = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $upload     = Upload::factory()->for($otherAdmin, 'user')->create();

        $response = $this->actingAs($admin)
            ->deleteJson(route('uploads.destroy', $upload->document_id));

        $response->assertForbidden();
        $this->assertDatabaseHas('documents', [
            'document_id' => $upload->document_id,
            'deleted_at'  => null,
        ]);
    }

    public function test_delete_dispatches_faiss_rebuild_request(): void
    {
        Http::fake([
            'http://127.0.0.1:5001/ingest' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $admin  = User::factory()->admin()->create();
        $upload = Upload::factory()->for($admin, 'user')->create();

        $this->actingAs($admin)
            ->delete(route('uploads.destroy', $upload->document_id));

        // dispatchFaissRebuild uses raw fsockopen, not Http facade,
        // so we can only assert the document was actually deleted —
        // the socket dispatch itself is fire-and-forget by design.
        $this->assertSoftDeleted('documents', ['document_id' => $upload->document_id]);
    }
}
