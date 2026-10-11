<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    /**
     * Get the business that owns this model.
     *
     * @return BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Query records for a specific business.
     *
     * THIS METHOD REQUIRES an explicit $businessId.
     * Passing null will throw an exception, NOT return an unscoped query.
     *
     *
     * @throws \InvalidArgumentException when $businessId is null
     */
    public static function forBusiness(Builder $query, int $businessId): Builder
    {
        if (empty($businessId)) {
            throw new \InvalidArgumentException(
                'Business ID is required for business-scoped query. '
                .'Do not call forBusiness(null) - explicitly provide the business ID.'
            );
        }

        return $query->where('business_id', $businessId);
    }

    /**
     * Scope a query to only include records for the given business.
     *
     * Usage: Model::whereBusiness($businessId)->get()
     */
    public function scopeWhereBusiness(Builder $query, int $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }
}
