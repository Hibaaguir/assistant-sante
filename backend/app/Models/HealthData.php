<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthData extends Model
{
    protected $table = 'health_data';

    protected $fillable = [
        'user_id',
        'date',
        'doctor_observation',
    ];

    protected $casts = [
        'date'       => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSigns::class, 'health_data_id');
    }

    public function analysisResults(): HasMany
    {
        return $this->hasMany(AnalysisResult::class, 'health_data_id');
    }

    public function treatmentChecks(): HasMany
    {
        return $this->hasMany(TreatmentCheck::class, 'health_data_id');
    }
}
