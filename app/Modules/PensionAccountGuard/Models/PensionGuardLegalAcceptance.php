<?php

namespace App\Modules\PensionAccountGuard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PensionGuardLegalAcceptance extends Model
{
    protected $table = 'pension_guard_legal_acceptances';

    protected $fillable = [
        'pension_guard_user_id',
        'terms_version',
        'terms_accepted_at',
        'privacy_version',
        'privacy_acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'terms_accepted_at' => 'datetime',
            'privacy_acknowledged_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            PensionGuardUser::class,
            'pension_guard_user_id'
        );
    }
}