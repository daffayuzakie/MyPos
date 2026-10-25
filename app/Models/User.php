<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pin',
        'business_id',
        'branch_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'business_id' => 'integer',
        'branch_id' => 'integer',
        'status' => 'string',
    ];

    /**
     * Deterministic lookup key for a plain PIN.
     *
     * Allows a single indexed lookup instead of bcrypt-checking every user
     * during PIN login. The PIN itself remains stored as a slow hash.
     */
    public static function pinLookup(string $pin): string
    {
        return hash_hmac('sha256', $pin, (string) config('app.key'));
    }

    /**
     * Hash the plain PIN and maintain its deterministic lookup value.
     *
     * @return Attribute<array{pin: string|null, pin_lookup: string|null}>
     */
    protected function pin(): Attribute
    {
        return Attribute::make(
            set: function (?string $value): array {
                if ($value === null || $value === '') {
                    return ['pin' => null, 'pin_lookup' => null];
                }

                return [
                    'pin' => Hash::make($value),
                    'pin_lookup' => self::pinLookup($value),
                ];
            },
        );
    }

    /**
     * Get the business that owns this user.
     *
     * @return BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the branch this user is assigned to.
     *
     * @return BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
