<?php

namespace App\Modules\PensionAccountGuard\Models;

use App\Modules\PensionAccountGuard\Notifications\PensionGuardResetPassword;
use App\Modules\PensionAccountGuard\Notifications\PensionGuardVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PensionGuardUser extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, MustVerifyEmailTrait;

    protected $table = 'pension_guard_users';

    protected $fillable = [
        'name',
        'username',
        'email',
	'email_verified_at',
        'password',
        'is_admin',
        'stripe_customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PensionGuardResetPassword($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new PensionGuardVerifyEmail());
    }

    public function trustedContacts(): HasMany
    {
        return $this->hasMany(
            PensionGuardTrustedContact::class,
            'user_id'
        );
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            PensionGuardSubscription::class,
            'user_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            PensionGuardPayment::class,
            'user_id'
        );
    }

    public function sessionBookings(): HasMany
    {
                    return $this->hasMany(
                                    PensionGuardSessionBooking::class,
                                    'user_id'
                    );
    }

}
