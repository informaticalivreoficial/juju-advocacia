<?php

namespace App\Policies;

use App\Models\Process;
use App\Models\User;

class ProcessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('processes.view');
    }

    public function view(User $user, Process $process): bool
    {
        return $user->hasPermission('processes.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('processes.create');
    }

    public function update(User $user, Process $process): bool
    {
        return $user->hasPermission('processes.update');
    }

    public function delete(User $user, Process $process): bool
    {
        return $user->hasPermission('processes.delete');
    }
}
