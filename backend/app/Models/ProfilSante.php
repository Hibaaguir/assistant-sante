<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilSante extends Model
{
    protected $table = 'profils_sante';

    protected $fillable = [
        'user_id',
        'sexe',
        'taille',
        'poids',
        'groupe_sanguin',
        'objectif',
        'objectifs',
        'allergies',
        'maladies_chroniques',
        'traitements',
        'prend_medicament',
        'nom_medicament',
        'fumeur',
        'alcool',
        'consulte_medecin',
        'medecin_email',
        'medecin_peut_consulter',
    ];

    protected $casts = [
        'allergies' => 'array',
        'maladies_chroniques' => 'array',
        'traitements' => 'array',
        'objectifs' => 'array',
        'prend_medicament' => 'boolean',
        'consulte_medecin' => 'boolean',
        'medecin_peut_consulter' => 'boolean',
        'fumeur' => 'boolean',
        'alcool' => 'boolean',
    ];
}
