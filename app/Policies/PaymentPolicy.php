<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payments view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->authorizeBusiness($user, $payment) && $user->can('payments view');
    }

    public function create(User $user): bool
    {
        return $user->can('payments create');
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $this->authorizeBusiness($user, $payment) && $user->can('payments refund');
    }
}
