<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSigns extends Model
{
    protected $table = 'vital_signs';

    protected $fillable = [
        'user_id',
        'measured_at',
        'heart_rate',
        'systolic_pressure',
        'diastolic_pressure',
        'oxygen_saturation',
        'temperature',
        'weight',
        'height',
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'heart_rate' => 'integer',
        'systolic_pressure' => 'integer',
        'diastolic_pressure' => 'integer',
        'oxygen_saturation' => 'decimal:2',
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation: vital signs belong to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
