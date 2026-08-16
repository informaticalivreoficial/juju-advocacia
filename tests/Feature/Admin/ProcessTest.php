<?php

namespace Tests\Feature\Admin;

use App\Enums\ProcessAreaEnum;
use App\Enums\ProcessPriorityEnum;
use App\Enums\ProcessStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Client;
use App\Models\Process;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function lawyer(): User
    {
        return User::factory()->role(UserRoleEnum::Lawyer)->create();
    }

    private function secretary(): User
    {
        return User::factory()->role(UserRoleEnum::Secretary)->create();
    }

    private function payload(array $overrides = []): array
    {
        $client = Client::factory()->individual()->create();
        $responsible = User::factory()->role(UserRoleEnum::Lawyer)->create();

        return array_merge([
            'client_id' => $client->id,
            'responsible_user_id' => $responsible->id,
            'title' => 'Ação de cobrança',
            'area' => ProcessAreaEnum::Civil->value,
            'status' => ProcessStatusEnum::Active->value,
            'priority' => ProcessPriorityEnum::Normal->value,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/processes')->assertRedirect('/login');
    }

    public function test_lawyer_can_view_processes_index(): void
    {
        $this->actingAs($this->lawyer())->get('/admin/processes')->assertOk();
    }

    public function test_lawyer_can_create_process(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/processes', $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('processes', ['title' => 'Ação de cobrança']);
    }

    public function test_secretary_cannot_create_process(): void
    {
        $this->actingAs($this->secretary())
            ->post('/admin/processes', $this->payload())
            ->assertForbidden();

        $this->assertDatabaseMissing('processes', ['title' => 'Ação de cobrança']);
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/processes', $this->payload(['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_rejects_invalid_area(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/processes', $this->payload(['area' => 'inexistente']))
            ->assertSessionHasErrors('area');
    }

    public function test_lawyer_can_update_process(): void
    {
        $lawyer = $this->lawyer();
        $process = Process::factory()->create();

        $this->actingAs($lawyer)->put("/admin/processes/{$process->id}", $this->payload([
            'title' => 'Título atualizado',
        ]))->assertRedirect();

        $this->assertDatabaseHas('processes', [
            'id' => $process->id,
            'title' => 'Título atualizado',
        ]);
    }

    public function test_lawyer_can_view_process_show(): void
    {
        $process = Process::factory()->create();

        $this->actingAs($this->lawyer())
            ->get("/admin/processes/{$process->id}")
            ->assertOk();
    }

    public function test_lawyer_cannot_delete_process(): void
    {
        $process = Process::factory()->create();

        $this->actingAs($this->lawyer())
            ->delete("/admin/processes/{$process->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('processes', ['id' => $process->id]);
    }

    public function test_admin_can_delete_process(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $process = Process::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/processes/{$process->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('processes', ['id' => $process->id]);
    }
}
