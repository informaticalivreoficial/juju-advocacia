<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    private array $modules = [
        'clients' => 'Clientes',
        'processes' => 'Processos',
        'deadlines' => 'Prazos',
        'tasks' => 'Tarefas',
        'calendar' => 'Agenda',
        'documents' => 'Documentos',
        'users' => 'Usuários',
        'audit' => 'Auditoria',
    ];

    private array $actions = [
        'view' => 'Visualizar',
        'create' => 'Criar',
        'update' => 'Editar',
        'delete' => 'Excluir',
    ];

    private array $actionsByModule = [
        'documents' => ['view', 'create', 'delete'],
        'audit' => ['view'],
    ];

    private array $rolePermissions = [
        'admin' => ['*'],
        'partner' => ['*'],
        'lawyer' => [
            'clients.view', 'clients.create', 'clients.update',
            'processes.view', 'processes.create', 'processes.update',
            'deadlines.view', 'deadlines.create', 'deadlines.update',
            'tasks.view', 'tasks.create', 'tasks.update',
            'calendar.view', 'calendar.create', 'calendar.update',
            'documents.view', 'documents.create',
        ],
        'assistant' => [
            'clients.view', 'clients.update',
            'processes.view', 'processes.update',
            'deadlines.view', 'deadlines.create', 'deadlines.update',
            'tasks.view', 'tasks.create', 'tasks.update',
            'calendar.view', 'calendar.create', 'calendar.update',
            'documents.view', 'documents.create',
        ],
        'secretary' => [
            'clients.view',
            'processes.view',
            'deadlines.view', 'deadlines.create', 'deadlines.update',
            'tasks.view', 'tasks.create', 'tasks.update',
            'calendar.view', 'calendar.create', 'calendar.update',
            'documents.view', 'documents.create',
        ],
    ];

    public function run(): void
    {
        $permissionNames = $this->permissionNames();

        $permissions = collect($permissionNames)->mapWithKeys(function (string $name) {
            [$module, $action] = explode('.', $name);

            $permission = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'module' => $module,
                    'label' => "{$this->modules[$module]} — {$this->actions[$action]}",
                ]
            );

            return [$name => $permission];
        });

        foreach ($this->rolePermissions as $roleName => $allowed) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['label' => $this->roleLabel($roleName)]
            );

            $names = $allowed === ['*'] ? $permissionNames : $allowed;
            $role->permissions()->sync($permissions->only($names)->pluck('id'));
        }
    }

    private function permissionNames(): array
    {
        $names = [];

        foreach ($this->modules as $module => $label) {
            foreach ($this->actionsByModule[$module] ?? ['view', 'create', 'update', 'delete'] as $action) {
                $names[] = "{$module}.{$action}";
            }
        }

        return $names;
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrador',
            'partner' => 'Sócio(a)',
            'lawyer' => 'Advogado(a)',
            'assistant' => 'Assistente',
            'secretary' => 'Secretário(a)',
            default => ucfirst($role),
        };
    }
}
