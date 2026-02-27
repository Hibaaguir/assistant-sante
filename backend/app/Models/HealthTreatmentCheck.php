<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthTreatmentCheck extends Model
{
    protected $fillable = [
        'user_id',
        'check_date',
        'medication_key',
        'medication_name',
        'dose',
        'taken',
        'checked_at',
    ];

    protected $casts = [
        'check_date' => 'date:Y-m-d',
        'taken' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
