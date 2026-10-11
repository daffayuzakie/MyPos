<?php

namespace App\Policies;

use App\Models\StockMovement;
use App\Models\User;

class StockMovementPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('stock view');
    }

    public function view(User $user, StockMovement $movement): bool
    {
        return $this->authorizeBusiness($user, $movement) && $user->can('stock view');
    }

    public function create(User $user): bool
    {
        return $user->can('stock create');
    }

    public function update(User $user, StockMovement $movement): bool
    {
        return false; // Immutable - no updates
    }

    public function delete(User $user, StockMovement $movement): bool
    {
        return false; // Immutable - no deletes
    }
}
