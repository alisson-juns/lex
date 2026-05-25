<?php

namespace App\Policies;

use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function view(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
