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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation: une entree du catalogue peut avoir plusieurs traitements
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'treatment_catalog_id');
    }
}
