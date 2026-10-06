<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expenses view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $this->authorizeBusiness($user, $expense) && $user->can('expenses view');
    }

    public function create(User $user): bool
    {
        return $user->can('expenses create');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $this->authorizeBusiness($user, $expense) && $user->can('expenses edit');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $this->authorizeBusiness($user, $expense) && $user->can('expenses delete');
    }
}