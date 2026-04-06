<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentCatalog extends Model
{
    protected $table = 'treatment_catalogs';

    protected $fillable = [
        'medication_type',
        'medication_name',
        'created_by_user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    // Relation: a catalog entry can have multiple treatments
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'treatment_catalog_id');
    }
}
