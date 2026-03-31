<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitePhysique extends Model
{
    protected $table = 'activites_physiques';

    protected $fillable = [
        'id_journal_quotidien',
        'type_activite',
        'duree_activite',
        'intensite',
    ];

    protected $casts = [
        'duree_activite' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation : chaque activité physique appartient à une entrée du journal
    public function JournalQuotidien(): BelongsTo
    {
        return $this->belongsTo(JournalQuotidien::class, 'id_journal_quotidien');
    }
}
