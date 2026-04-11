<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSigns extends Model
{
    protected $table = 'vital_signs';

    protected $fillable = [
        'health_data_id',
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

    // Relation: vital signs belong to a health_data entry (owner is health_data.user_id)
    public function healthData(): BelongsTo
    {
        return $this->belongsTo(HealthData::class, 'health_data_id');
    }
}
