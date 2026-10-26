<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->can('users view')
            && $this->ownsBusiness($user, $target);
    }

    public function create(User $user): bool
    {
        return $user->can('users create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('users edit')
            && $this->ownsBusiness($user, $target)
            && ! $this->isProtectedAdmin($user, $target);
    }

    public function deactivate(User $user, User $target): bool
    {
        return $user->can('users deactivate')
            && $this->ownsBusiness($user, $target)
            && ! $this->isProtectedAdmin($user, $target);
    }

    /**
     * Prevent non-admin staff from modifying an admin account.
     */
    private function isProtectedAdmin(User $user, User $target): bool
    {
        return $target->hasRole('admin') && ! $user->hasRole('admin');
    }
}
