<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_admin_can_view_users_index(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_partner_can_view_users_index(): void
    {
        $partner = User::factory()->role(UserRoleEnum::Partner)->create();

        $this->actingAs($partner)->get('/admin/users')->assertOk();
    }

    public function test_lawyer_without_permission_cannot_view_users(): void
    {
        $lawyer = User::factory()->role(UserRoleEnum::Lawyer)->create();

        $this->actingAs($lawyer)->get('/admin/users')->assertForbidden();
    }

    public function test_lawyer_without_permission_cannot_create_user(): void
    {
        $lawyer = User::factory()->role(UserRoleEnum::Lawyer)->create();

        $this->actingAs($lawyer)->post('/admin/users', [
            'name' => 'Invasor',
            'email' => 'invasor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRoleEnum::Assistant->value,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'invasor@example.com']);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRoleEnum::Assistant->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'novo@example.com']);
    }

    public function test_create_user_validates_unique_email(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Duplicado',
            'email' => $admin->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRoleEnum::Assistant->value,
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $target = User::factory()->role(UserRoleEnum::Secretary)->create();

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => 'Nome Alterado',
            'email' => 'alterado@example.com',
            'role' => UserRoleEnum::Lawyer->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Nome Alterado',
            'email' => 'alterado@example.com',
            'role' => UserRoleEnum::Lawyer->value,
        ]);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$admin->id}/active")
            ->assertSessionHasErrors('user');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_deactivate_another_user(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $target = User::factory()->role(UserRoleEnum::Secretary)->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/active")
            ->assertSessionHasNoErrors();

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::factory()
            ->role(UserRoleEnum::Secretary)
            ->inactive()
            ->create(['password' => 'password']);

        $this->post('/login', [
            'email' => $inactive->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_view_permissions(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->get('/admin/permissions')->assertOk();
    }

    public function test_lawyer_cannot_view_permissions(): void
    {
        $lawyer = User::factory()->role(UserRoleEnum::Lawyer)->create();

        $this->actingAs($lawyer)->get('/admin/permissions')->assertForbidden();
    }
}
