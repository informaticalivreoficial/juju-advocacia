<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $roles = Role::with('permissions:id,name')->orderBy('name')->get();

        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module')
            ->map(fn ($group) => $group->map(fn (Permission $permission) => [
                'name' => $permission->name,
                'label' => $permission->label,
                'roles' => $roles->filter(
                    fn (Role $role) => $role->permissions->contains('name', $permission->name)
                )->pluck('name')->values(),
            ])->values());

        return Inertia::render('Admin/Permissions/Index', [
            'modules' => $permissions,
            'roles' => $roles->map(fn (Role $role) => [
                'name' => $role->name,
                'label' => $role->label,
            ]),
        ]);
    }
}
