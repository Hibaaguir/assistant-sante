<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultatAnalyse extends Model
{
    protected $table = 'resultats_analyses';

    protected $fillable = [
        'id_utilisateur',
        'type_analyse',
        'resultat_analyse',
        'valeur',
        'unite',
        'date_analyse',
    ];

    protected $casts = [
        'date_analyse' => 'date',
        'valeur' => 'decimal:2',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }
}
