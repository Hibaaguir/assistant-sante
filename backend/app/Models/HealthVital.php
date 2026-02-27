<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthVital extends Model
{
    protected $fillable = [
        'user_id',
        'measured_at',
        'heart_rate',
        'systolic_pressure',
        'diastolic_pressure',
        'oxygen_saturation',
        'notes',
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'oxygen_saturation' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
