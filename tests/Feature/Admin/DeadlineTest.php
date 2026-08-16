<?php

namespace Tests\Feature\Admin;

use App\Enums\DeadlinePriorityEnum;
use App\Enums\DeadlineStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Deadline;
use App\Models\Process;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeadlineTest extends TestCase
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
        $process = Process::factory()->create();
        $responsible = User::factory()->role(UserRoleEnum::Lawyer)->create();

        return array_merge([
            'process_id' => $process->id,
            'responsible_user_id' => $responsible->id,
            'title' => 'Contestar prazo',
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => DeadlineStatusEnum::Pending->value,
            'priority' => DeadlinePriorityEnum::Normal->value,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/deadlines')->assertRedirect('/login');
    }

    public function test_lawyer_can_view_deadlines_index(): void
    {
        $this->actingAs($this->lawyer())->get('/admin/deadlines')->assertOk();
    }

    public function test_lawyer_can_create_deadline(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/deadlines', $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('deadlines', ['title' => 'Contestar prazo']);
    }

    public function test_secretary_can_create_deadline(): void
    {
        $this->actingAs($this->secretary())
            ->post('/admin/deadlines', $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('deadlines', ['title' => 'Contestar prazo']);
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/deadlines', $this->payload(['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_due_date_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/deadlines', $this->payload(['due_date' => '']))
            ->assertSessionHasErrors('due_date');
    }

    public function test_lawyer_can_update_deadline(): void
    {
        $deadline = Deadline::factory()->create();

        $this->actingAs($this->lawyer())
            ->put("/admin/deadlines/{$deadline->id}", $this->payload([
                'title' => 'Título atualizado',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('deadlines', [
            'id' => $deadline->id,
            'title' => 'Título atualizado',
        ]);
    }

    public function test_lawyer_can_toggle_complete_deadline(): void
    {
        $deadline = Deadline::factory()->create([
            'status' => DeadlineStatusEnum::Pending,
        ]);

        $this->actingAs($this->lawyer())
            ->patch("/admin/deadlines/{$deadline->id}/toggle-complete")
            ->assertRedirect();

        $this->assertDatabaseHas('deadlines', [
            'id' => $deadline->id,
            'status' => DeadlineStatusEnum::Completed->value,
        ]);

        $this->actingAs($this->lawyer())
            ->patch("/admin/deadlines/{$deadline->id}/toggle-complete")
            ->assertRedirect();

        $this->assertDatabaseHas('deadlines', [
            'id' => $deadline->id,
            'status' => DeadlineStatusEnum::Pending->value,
        ]);
    }

    public function test_expired_scope_returns_only_overdue_pending_deadlines(): void
    {
        Deadline::factory()->expired()->create();
        Deadline::factory()->create(['due_date' => now()->addDays(5)->toDateString()]);
        Deadline::factory()->completed()->create();

        $this->assertCount(1, Deadline::expired()->get());
    }

    public function test_lawyer_cannot_delete_deadline(): void
    {
        $deadline = Deadline::factory()->create();

        $this->actingAs($this->lawyer())
            ->delete("/admin/deadlines/{$deadline->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('deadlines', ['id' => $deadline->id]);
    }

    public function test_admin_can_delete_deadline(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $deadline = Deadline::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/deadlines/{$deadline->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('deadlines', ['id' => $deadline->id]);
    }
}
