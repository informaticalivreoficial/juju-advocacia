<?php

namespace App\Policies;

use App\Models\User;

class FinancialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('financial.view');
    }

    public function view(User $user, $model): bool
    {
        return $user->hasPermission('financial.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('financial.create');
    }

    public function update(User $user, $model): bool
    {
        return $user->hasPermission('financial.update');
    }

    public function delete(User $user, $model): bool
    {
        return $user->hasPermission('financial.delete');
    }
}