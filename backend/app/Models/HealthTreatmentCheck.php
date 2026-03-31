<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthTreatmentCheck extends Model
{
    protected $fillable = [
        'traitement_id',
        'date_controle',
        'pris',
        'verifie_a',
    ];

    protected $casts = [
        'date_controle' => 'date:Y-m-d',
        'pris' => 'boolean',
        'verifie_a' => 'datetime',
    ];

    // Relation : chaque suivi appartient à un traitement
    public function traitement(): BelongsTo
    {
        return $this->belongsTo(Traitement::class);
    }

    // Relation : accès indirect à l'utilisateur via le traitement
    public function user()
    {
        return $this->traitement->utilisateur();
    }
}
