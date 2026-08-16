<?php

namespace Tests\Feature\Admin;

use App\Enums\ClientTypeEnum;
use App\Enums\UserRoleEnum;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/clients')->assertRedirect('/login');
    }

    public function test_lawyer_can_view_clients_index(): void
    {
        $lawyer = User::factory()->role(UserRoleEnum::Lawyer)->create();

        $this->actingAs($lawyer)->get('/admin/clients')->assertOk();
    }

    public function test_secretary_cannot_create_client(): void
    {
        $secretary = User::factory()->role(UserRoleEnum::Secretary)->create();

        $this->actingAs($secretary)->post('/admin/clients', [
            'type' => ClientTypeEnum::Individual->value,
            'name' => 'Cliente Teste',
            'document' => '52998224725',
        ])->assertForbidden();

        $this->assertDatabaseMissing('clients', ['name' => 'Cliente Teste']);
    }

    public function test_admin_can_create_individual_client(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->post('/admin/clients', [
            'type' => ClientTypeEnum::Individual->value,
            'name' => 'Maria Silva',
            'document' => '529.982.247-25',
            'email' => 'maria@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name' => 'Maria Silva',
            'document' => '52998224725',
            'email' => 'maria@example.com',
        ]);
    }

    public function test_admin_can_create_company_client(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->post('/admin/clients', [
            'type' => ClientTypeEnum::Company->value,
            'company_name' => 'Empresa Exemplo LTDA',
            'document' => '11.222.333/0001-81',
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'company_name' => 'Empresa Exemplo LTDA',
            'document' => '11222333000181',
        ]);
    }

    public function test_individual_requires_name(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->post('/admin/clients', [
            'type' => ClientTypeEnum::Individual->value,
            'document' => '52998224725',
        ])->assertSessionHasErrors('name');
    }

    public function test_company_requires_company_name(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->post('/admin/clients', [
            'type' => ClientTypeEnum::Company->value,
            'document' => '11222333000181',
        ])->assertSessionHasErrors('company_name');
    }

    public function test_rejects_invalid_document(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();

        $this->actingAs($admin)->post('/admin/clients', [
            'type' => ClientTypeEnum::Individual->value,
            'name' => 'João',
            'document' => '12345678900',
        ])->assertSessionHasErrors('document');
    }

    public function test_rejects_duplicate_document(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        Client::factory()->individual()->create(['document' => '52998224725']);

        $this->actingAs($admin)->post('/admin/clients', [
            'type' => ClientTypeEnum::Individual->value,
            'name' => 'Outra Pessoa',
            'document' => '52998224725',
        ])->assertSessionHasErrors('document');
    }

    public function test_admin_can_update_client(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $client = Client::factory()->individual()->create();

        $this->actingAs($admin)->put("/admin/clients/{$client->id}", [
            'type' => ClientTypeEnum::Individual->value,
            'name' => 'Nome Atualizado',
            'document' => '52998224725',
            'email' => 'novo@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Nome Atualizado',
            'email' => 'novo@example.com',
        ]);
    }

    public function test_admin_can_delete_client(): void
    {
        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $client = Client::factory()->individual()->create();

        $this->actingAs($admin)
            ->delete("/admin/clients/{$client->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_lawyer_cannot_delete_client(): void
    {
        $lawyer = User::factory()->role(UserRoleEnum::Lawyer)->create();
        $client = Client::factory()->individual()->create();

        $this->actingAs($lawyer)
            ->delete("/admin/clients/{$client->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('clients', ['id' => $client->id]);
    }
}
