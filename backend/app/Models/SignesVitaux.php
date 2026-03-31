<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignesVitaux extends Model
{
    protected $table = 'signes_vitaux';

    protected $fillable = [
        'id_utilisateur',
        'mesure_a',
        'frequence_cardiaque',
        'pression_systolique',
        'pression_diastolique',
        'saturation_oxygene',
    ];

    protected $casts = [
        'mesure_a' => 'datetime',
        'frequence_cardiaque' => 'integer',
        'pression_systolique' => 'integer',
        'pression_diastolique' => 'integer',
        'saturation_oxygene' => 'integer',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }
}
