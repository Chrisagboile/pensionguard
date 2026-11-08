<?php

namespace App\Modules\PensionAccountGuard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PensionGuardSessionAvailability extends Model
{
    protected $table = 'pension_guard_session_availability';

    protected $fillable = [
        'created_by',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            PensionGuardUser::class,
            'created_by'
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(
            PensionGuardSessionBooking::class,
            'availability_id'
        );
    }
}