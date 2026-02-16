<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilSante extends Model
{
    protected $table = 'profils_sante';

    protected $fillable = [
        'user_id',
        'age',
        'sexe',
        'taille',
        'poids',
        'groupe_sanguin',
        'objectif',
        'allergies',
        'maladies_chroniques',
        'traitements',
        'prend_medicament',
        'nom_medicament',
        'fumeur',
        'alcool',
    ];

    protected $casts = [
        'allergies' => 'array',
        'maladies_chroniques' => 'array',
        'traitements' => 'array',
        'prend_medicament' => 'boolean',
        'fumeur' => 'boolean',
        'alcool' => 'boolean',
    ];
}

