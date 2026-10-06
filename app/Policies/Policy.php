<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class Policy
{
    use HandlesAuthorization;

    /**
     * Check if the user owns the business of the given resource.
     *
     * @param  \App\Models\User  $user
     * @param  mixed  $resource
     * @return bool
     */
    protected function ownsBusiness(User $user, $resource): bool
    {
        return $user->business_id === $resource->business_id;
    }

    /**
     * Authorize business ownership.
     *
     * @param  \App\Models\User  $user
     * @param  mixed  $resource
     * @return bool
     */
    protected function authorizeBusiness(User $user, $resource): bool
    {
        return $this->ownsBusiness($user, $resource);
    }
}