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
        return $this->authorizeBusiness($user, $target) && $user->can('users view');
    }

    public function create(User $user): bool
    {
        return $user->can('users create');
    }

    public function update(User $user, User $target): bool
    {
        return $this->authorizeBusiness($user, $target) && $user->can('users edit');
    }

    public function deactivate(User $user, User $target): bool
    {
        // Prevent self-deactivation
        if ($user->id === $target->id) {
            return false;
        }

        return $this->authorizeBusiness($user, $target) && $user->can('users deactivate');
    }

    public function assignRole(User $user, User $target, string $role): bool
    {
        // Valid roles for business
        $validRoles = ['admin', 'kasir'];

        if (!in_array($role, $validRoles)) {
            return false;
        }

        // Prevent self-role change
        if ($user->id === $target->id) {
            return false;
        }

        // Must own the target user's business
        if (!$this->authorizeBusiness($user, $target)) {
            return false;
        }

        // Must have edit permission
        if (!$user->can('users edit')) {
            return false;
        }

        return true;
    }
}