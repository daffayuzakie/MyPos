<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('suppliers view');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $this->authorizeBusiness($user, $supplier) && $user->can('suppliers view');
    }

    public function create(User $user): bool
    {
        return $user->can('suppliers create');
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->authorizeBusiness($user, $supplier) && $user->can('suppliers edit');
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->authorizeBusiness($user, $supplier) && $user->can('suppliers delete');
    }
}