<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases view');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $this->authorizeBusiness($user, $purchase) && $user->can('purchases view');
    }

    public function create(User $user): bool
    {
        return $user->can('purchases create');
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $this->authorizeBusiness($user, $purchase) && $user->can('purchases edit');
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        return $this->authorizeBusiness($user, $purchase) && $user->can('purchases delete');
    }
}