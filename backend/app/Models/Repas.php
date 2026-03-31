<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repas extends Model
{
    protected $table = 'repas';

    protected $fillable = [
        'id_journal_quotidien',
        'type_repas',
        'description',
        'calories',
        'apport_sucre',
    ];

    protected $casts = [
        'calories' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation : chaque repas appartient à une entrée du journal
    public function entreeJournal(): BelongsTo
    {
        return $this->belongsTo(JournalQuotidien::class, 'id_journal_quotidien');
    }
}
