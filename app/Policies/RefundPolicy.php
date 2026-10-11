<?php

namespace App\Policies;

use App\Models\Refund;
use App\Models\User;

class RefundPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('refunds view');
    }

    public function view(User $user, Refund $refund): bool
    {
        return $this->authorizeBusiness($user, $refund) && $user->can('refunds view');
    }

    public function create(User $user): bool
    {
        return $user->can('refunds create');
    }

    public function update(User $user, Refund $refund): bool
    {
        return $this->authorizeBusiness($user, $refund) && $user->can('refunds edit');
    }

    public function delete(User $user, Refund $refund): bool
    {
        // Refunds are financial records - not deletable
        return false;
    }
}
