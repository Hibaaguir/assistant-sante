<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treatment extends Model
{
    protected $table = 'treatments';

    protected $fillable = [
        'user_id',
        'treatment_catalog_id',
        'dose',
        'frequency',
        'daily_doses',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
        'daily_doses' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation: treatment belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relation: references a treatment catalog item
    public function treatmentCatalog(): BelongsTo
    {
        return $this->belongsTo(TreatmentCatalog::class, 'treatment_catalog_id');
    }

    // Relation: a treatment can have multiple checks
    public function checks(): HasMany
    {
        return $this->hasMany(TreatmentCheck::class);
    }

    // Relation: a treatment can have multiple notifications
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'treatment_id');
    }
}
