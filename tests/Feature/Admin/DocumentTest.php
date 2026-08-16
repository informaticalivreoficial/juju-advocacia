<?php

namespace Tests\Feature\Admin;

use App\Enums\DocumentCategoryEnum;
use App\Enums\UserRoleEnum;
use App\Models\Document;
use App\Models\Process;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
    }

    private function lawyer(): User
    {
        return User::factory()->role(UserRoleEnum::Lawyer)->create();
    }

    private function payload(array $overrides = []): array
    {
        $process = Process::factory()->create();

        return array_merge([
            'process_id' => $process->id,
            'title' => 'Petição inicial',
            'category' => DocumentCategoryEnum::Petition->value,
            'file' => UploadedFile::fake()->create('peticao.pdf', 100, 'application/pdf'),
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/documents')->assertRedirect('/login');
    }

    public function test_lawyer_can_view_documents_index(): void
    {
        $this->actingAs($this->lawyer())->get('/admin/documents')->assertOk();
    }

    public function test_lawyer_can_upload_document(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/documents', $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('documents', ['title' => 'Petição inicial']);
        Storage::disk('local')->assertExists(Document::first()->file_path);
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/documents', $this->payload(['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_file_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/documents', $this->payload(['file' => null]))
            ->assertSessionHasErrors('file');
    }

    public function test_rejects_invalid_file_type(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/documents', $this->payload([
                'file' => UploadedFile::fake()->create('script.exe', 100),
            ]))
            ->assertSessionHasErrors('file');
    }

    public function test_lawyer_can_download_document(): void
    {
        $lawyer = $this->lawyer();
        $this->actingAs($lawyer)->post('/admin/documents', $this->payload());

        $document = Document::first();

        $response = $this->actingAs($lawyer)
            ->get("/admin/documents/{$document->id}/download");

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_lawyer_cannot_delete_document(): void
    {
        $this->actingAs($this->lawyer())->post('/admin/documents', $this->payload());
        $document = Document::first();

        $this->actingAs($this->lawyer())
            ->delete("/admin/documents/{$document->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('documents', ['id' => $document->id]);
    }

    public function test_admin_can_delete_document_and_file(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $this->actingAs($admin)->post('/admin/documents', $this->payload());
        $document = Document::first();

        $this->actingAs($admin)
            ->delete("/admin/documents/{$document->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($document->file_path);
    }
}
