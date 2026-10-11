<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy extends Policy
{
    public function view(User $user, Business $business): bool
    {
        // Only admin can view business settings
        return $user->hasRole('admin')
            && $this->authorizeBusiness($user, $business)
            && $user->can('business settings');
    }

    public function update(User $user, Business $business): bool
    {
        return $user->hasRole('admin')
            && $this->authorizeBusiness($user, $business)
            && $user->can('business settings');
    }

    /**
     * For Business model, the resource IS the business, so check user's business_id against business id.
     */
    protected function authorizeBusiness(User $user, $resource): bool
    {
        if (! $resource instanceof Business) {
            return false;
        }

        return $user->business_id === $resource->id;
    }
}
