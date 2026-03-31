<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilSante extends Model
{
    protected $table = 'profils_sante';

    protected $fillable = [
        'id_utilisateur',
        'genre',
        'taille',
        'poids',
        'groupe_sanguin',
        'objectifs',
        'allergies',
        'maladies_chroniques',
        'fumeur',
        'alcoolique',
        'inviter_medecin',
        'medecin_email',
        
    ];

    protected $casts = [
        'allergies' => 'array',
        'maladies_chroniques' => 'array',
        'traitements' => 'array',
        'objectifs' => 'array',
        'inviter_medecin' => 'boolean',
        'fumeur' => 'boolean',
        'alcoolique' => 'boolean',
    ];

    // Relation indiquant que chaque profil santé appartient à un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id');
    }

    // Relation : un profil de santé peut avoir plusieurs traitements
    public function traitements(): HasMany
    {
        return $this->hasMany(Traitement::class);
    }
}
