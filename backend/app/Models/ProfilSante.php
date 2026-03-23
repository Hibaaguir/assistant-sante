<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $consulte_medecin
 * @property bool $medecin_peut_consulter
 * @property string|null $medecin_email
 */
class ProfilSante extends Model
{
    protected $table = 'profils_sante';

    protected $fillable = [
        'user_id',
        'sexe',
        'taille',
        'poids',
        'groupe_sanguin',
        'objectifs',
        'allergies',
        'maladies_chroniques',
        'traitements',
        'prend_medicament',
        'nom_medicament',
        'fumeur',
        'alcool',
        'activite_physique',
        'activites_physiques',
        'frequence_activite_physique',
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
        'activite_physique' => 'boolean',
        'activites_physiques' => 'array',
    ];
}
