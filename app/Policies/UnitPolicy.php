<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('units view');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->authorizeBusiness($user, $unit) && $user->can('units view');
    }

    public function create(User $user): bool
    {
        return $user->can('units create');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->authorizeBusiness($user, $unit) && $user->can('units edit');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->authorizeBusiness($user, $unit) && $user->can('units delete');
    }
}
