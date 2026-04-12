<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentCheck extends Model
{
    protected $table = 'treatment_checks';

    protected $fillable = [
        'treatment_id',
        'health_data_id',
        'check_date',
        'medication_key',
        'taken',
        'checked_at',
    ];

    protected $casts = [
        'check_date' => 'date',
        'checked_at' => 'datetime',
        'taken' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation: la verification appartient a un traitement
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }

    // Relation: la verification appartient a une entree health_data (le proprietaire est health_data.user_id)
    public function healthData(): BelongsTo
    {
        return $this->belongsTo(HealthData::class, 'health_data_id');
    }
}
