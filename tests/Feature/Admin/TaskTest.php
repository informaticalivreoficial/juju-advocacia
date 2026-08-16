<?php

namespace Tests\Feature\Admin;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
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
        $responsible = User::factory()->role(UserRoleEnum::Lawyer)->create();

        return array_merge([
            'responsible_user_id' => $responsible->id,
            'title' => 'Redigir petição inicial',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::Normal->value,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/tasks')->assertRedirect('/login');
    }

    public function test_lawyer_can_view_tasks_index(): void
    {
        $this->actingAs($this->lawyer())->get('/admin/tasks')->assertOk();
    }

    public function test_lawyer_can_create_task(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/tasks', $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['title' => 'Redigir petição inicial']);
    }

    public function test_secretary_can_create_task(): void
    {
        $this->actingAs($this->secretary())
            ->post('/admin/tasks', $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['title' => 'Redigir petição inicial']);
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/tasks', $this->payload(['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_rejects_invalid_status(): void
    {
        $this->actingAs($this->lawyer())
            ->post('/admin/tasks', $this->payload(['status' => 'inexistente']))
            ->assertSessionHasErrors('status');
    }

    public function test_lawyer_can_update_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->lawyer())
            ->put("/admin/tasks/{$task->id}", $this->payload([
                'title' => 'Título atualizado',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título atualizado',
        ]);
    }

    public function test_lawyer_can_toggle_complete_task(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatusEnum::Pending,
        ]);

        $this->actingAs($this->lawyer())
            ->patch("/admin/tasks/{$task->id}/toggle-complete")
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatusEnum::Completed->value,
        ]);

        $this->actingAs($this->lawyer())
            ->patch("/admin/tasks/{$task->id}/toggle-complete")
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatusEnum::Pending->value,
        ]);
    }

    public function test_lawyer_cannot_delete_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->lawyer())
            ->delete("/admin/tasks/{$task->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_admin_can_delete_task(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $task = Task::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/tasks/{$task->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
