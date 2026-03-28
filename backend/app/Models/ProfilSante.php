<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'consulte_medecin' => 'boolean',
        'medecin_peut_consulter' => 'boolean',
        'fumeur' => 'boolean',
        'alcool' => 'boolean',
    ];

    // Relation indiquant que chaque profil santé appartient à un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
