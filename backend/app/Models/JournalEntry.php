<?php

// Définit l'emplacement du modèle dans l'application
namespace App\Models;

// Import des classes nécessaires de Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modèle qui représente une entrée du journal dans la base de données
class JournalEntry extends Model
{
    // Liste des champs que l'on peut remplir automatiquement lors de la création ou mise à jour
    protected $fillable = [
        'user_id',
        'entry_date',
        'sleep',
        'stress',
        'energy',
        'sugar',
        'caffeine',
        'hydration',
        'meals',
        'activity_type',
        'activity_duration',
        'intensity',
        'tobacco',
        'alcohol',
        'tobacco_types',
        'cigarettes_per_day',
        'vape_frequency',
        'vape_liquid_ml',
        'alcohol_drinks',
    ];

    // Définit le type de certaines données pour que Laravel les convertisse automatiquement
    protected $casts = [
        'entry_date' => 'date:Y-m-d',
        'meals' => 'array',
        'tobacco_types' => 'array',
        'hydration' => 'decimal:1',
        'tobacco' => 'boolean',
        'alcohol' => 'boolean',
    ];

    // Relation indiquant que chaque entrée du journal appartient à un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}