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
        'consulte_medecin',
        'medecin_peut_consulter',
        'medecin_email',
    ];

    protected $casts = [
        'allergies' => 'array',
        'maladies_chroniques' => 'array',
        'objectifs' => 'array',
        'fumeur' => 'boolean',
        'alcoolique' => 'boolean',
        'consulte_medecin' => 'boolean',
        'medecin_peut_consulter' => 'boolean',
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

    // Vérifier si le profil santé est complet (tous les champs obligatoires sont remplis)
    public function isComplete(): bool
    {
        return $this->genre !== null 
            && $this->taille !== null 
            && $this->poids !== null 
            && $this->groupe_sanguin !== null;
    }
}
