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
        'taken',
        'checked_at',
    ];

    protected $casts = [
        'check_date' => 'date',
        'checked_at' => 'datetime',
        'taken'      => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
