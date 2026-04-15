<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function treatmentChecks(): HasManyThrough
    {
        return $this->hasManyThrough(
            TreatmentCheck::class,
            Treatment::class,
            'health_data_id',
            'treatment_id',
            'id',
            'id'
        );
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'health_data_id');
    }

}
