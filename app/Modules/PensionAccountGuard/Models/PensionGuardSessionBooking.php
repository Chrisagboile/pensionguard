<?php

namespace App\Modules\PensionAccountGuard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PensionGuardSessionBooking extends Model
{
    public const PRICE_PER_30_MINUTES_PENCE = 2500;

    protected $table = 'pension_guard_session_bookings';

    protected $fillable = [
        'user_id',
        'availability_id',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'channel',
        'amount_pence',
        'currency',
        'status',
        'payment_status',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'checkout_expires_at',
        'paid_at',
        'confirmed_at',
        'cancelled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'checkout_expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
            'duration_minutes' => 'integer',
            'amount_pence' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            PensionGuardUser::class,
            'user_id'
        );
    }

    public function availability(): BelongsTo
    {
        return $this->belongsTo(
            PensionGuardSessionAvailability::class,
            'availability_id'
        );
    }

    /**
     * Calculate the booking price.
     *
     * £25 per 30 minutes.
     */
    public static function calculateAmount(int $durationMinutes): int
    {
        if (
            $durationMinutes < 30
            || $durationMinutes % 30 !== 0
        ) {
            throw new \InvalidArgumentException(
                'Duration must be at least 30 minutes and in 30-minute increments.'
            );
        }

        return (int) ($durationMinutes / 30)
            * self::PRICE_PER_30_MINUTES_PENCE;
    }
}
