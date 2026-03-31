<?php

// Définit l'emplacement du modèle dans l'application
namespace App\Models;

// Import des classes nécessaires de Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modèle qui représente une entré du journal dans la base de données
class JournalQuotidien extends Model
{
    protected $table = 'journal_quotidien';

    // Liste des champs que l'on peut remplir automatiquement lors de la création ou mise à jour
    protected $fillable = [
        'id_utilisateur',
        'date_entree',
        'sommeil',
        'stress',
        'energie',
        'cafeine',
        'hydratation',
        'alcool',
        'nb_verres_alcool',
    ];

    // Définit le type de certaines données pour que Laravel les convertisse automatiquement
    protected $casts = [
        'date_entree' => 'date:Y-m-d',
        'cafeine' => 'integer',
        'hydratation' => 'decimal:1',
        'alcool' => 'boolean',
        'nb_verres_alcool' => 'integer',
    ];

    // Relation indiquant que chaque entrée du journal appartient à un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    // Relation indiquant qu'une entrée du journal peut contenir plusieurs repas
    public function repas(): HasMany
    {
        return $this->hasMany(Repas::class, 'id_journal_quotidien');
    }

    // Relation indiquant qu'une entrée du journal peut contenir plusieurs activités physiques
    public function activitesPhysiques(): HasMany
    {
        return $this->hasMany(ActivitePhysique::class, 'id_journal_quotidien');
    }

    // Relation indiquant qu'une entrée du journal peut contenir plusieurs consommations de tabac
    public function tabacs(): HasMany
    {
        return $this->hasMany(Tabac::class, 'id_journal_quotidien');
    }
}