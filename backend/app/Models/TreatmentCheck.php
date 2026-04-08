<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentCheck extends Model
{
    protected $table = 'treatment_checks';

    protected $fillable = [
        'treatment_id',
        'user_id',
        'check_date',
        'medication_key',
        'medication_name',
        'dose',
        'taken',
        'checked_at',
        'notes',
        'doctor_report',
    ];

    protected $casts = [
        'check_date' => 'date',
        'checked_at' => 'datetime',
        'taken' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation: check belongs to a treatment
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }

    // Relation: check belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
