<?php

namespace App\Policies;

use App\Models\Deadline;
use App\Models\User;

class DeadlinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('deadlines.view');
    }

    public function view(User $user, Deadline $deadline): bool
    {
        return $user->hasPermission('deadlines.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('deadlines.create');
    }

    public function update(User $user, Deadline $deadline): bool
    {
        return $user->hasPermission('deadlines.update');
    }

    public function delete(User $user, Deadline $deadline): bool
    {
        return $user->hasPermission('deadlines.delete');
    }
}
