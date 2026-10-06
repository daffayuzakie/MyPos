<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales view-all') || $user->can('sales view-own');
    }

    public function view(User $user, Sale $sale): bool
    {
        // Admin can view all, kasir can view only their own
        if ($user->hasRole('admin')) {
            return $this->authorizeBusiness($user, $sale) && $user->can('sales view-all');
        }

        return $this->authorizeBusiness($user, $sale) 
            && $user->can('sales view-own')
            && $sale->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('sales create');
    }

    public function cancel(User $user, Sale $sale): bool
    {
        if ($user->hasRole('admin')) {
            return $this->authorizeBusiness($user, $sale) && $user->can('sales cancel');
        }

        return $this->authorizeBusiness($user, $sale) 
            && $sale->user_id === $user->id
            && $user->can('sales cancel');
    }

    public function refund(User $user, Sale $sale): bool
    {
        return $this->authorizeBusiness($user, $sale) && $user->can('sales refund');
    }
}