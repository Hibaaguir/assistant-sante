<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogueTraitement extends Model
{
    protected $table = 'catalogue_traitements';

    protected $fillable = [
        'type',
        'nom',
        'created_by_id_utilisateur',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'created_by_id_utilisateur');
    }

    // Relation : un article du catalogue peut être utilisé dans plusieurs traitements
    public function traitements(): HasMany
    {
        return $this->hasMany(Traitement::class, 'catalogue_traitements_id');
    }
}

