<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Traitement extends Model
{
    protected $table = 'traitements';

    protected $fillable = [
        'profil_sante_id',
        'catalogue_traitements_id',
        'dose',
        'frequence',
        'nombre_prises',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'nombre_prises' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation : le traitement appartient à un profil de santé
    public function profilSante(): BelongsTo
    {
        return $this->belongsTo(ProfilSante::class);
    }

    // Relation : référence un article du catalogue de traitements
    public function catalogueArticle(): BelongsTo
    {
        return $this->belongsTo(CatalogueTraitement::class, 'catalogue_traitements_id');
    }

    // Relation : un traitement peut avoir plusieurs suivis
    public function suivis(): HasMany
    {
        return $this->hasMany(SuiviTraitement::class);
    }
}
