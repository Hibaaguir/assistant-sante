<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modele qui represente une entree de journal dans la base de donnees
class JournalEntry extends Model
{
    protected $table = 'journal_entries';

    // Liste des champs qui peuvent etre remplis automatiquement lors de la creation ou de la mise a jour
    protected $fillable = [
        'user_id',
        'entry_date',
        'sleep',
        'stress',
        'energy',
        'caffeine',
        'hydration',
        'sugar_intake',
        'alcohol',
        'alcohol_glasses',
    ];

    // Definit le type de certaines donnees pour que Laravel les convertisse automatiquement
    protected $casts = [
        'entry_date' => 'date:Y-m-d',
        'caffeine' => 'integer',
        'hydration' => 'decimal:1',
        'sugar_intake' => 'string',
        'alcohol' => 'boolean',
        'alcohol_glasses' => 'integer',
    ];

    // Relation indiquant que chaque entree de journal appartient a un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation indiquant qu'une entree de journal peut contenir plusieurs repas
    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class, 'journal_entry_id');
    }

    // Relation indiquant qu'une entree de journal peut contenir plusieurs activites physiques
    public function physicalActivities(): HasMany
    {
        return $this->hasMany(PhysicalActivity::class, 'journal_entry_id');
    }

    // Relation indiquant qu'une entree de journal peut contenir plusieurs utilisations de tabac
    public function tobacco(): HasMany
    {
        return $this->hasMany(Tobacco::class, 'journal_entry_id');
    }
}
