<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tabac extends Model
{
    protected $table = 'tabacs';

    protected $fillable = [
        'id_journal_quotidien',
        'type_tabac',
        'cigarettes_par_jour',
        'bouffees_par_jour',
    ];

    protected $casts = [
        'cigarettes_par_jour' => 'integer',
        'bouffees_par_jour' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation : chaque consommation de tabac appartient à une entrée du journal
    public function entreeJournal(): BelongsTo
    {
        return $this->belongsTo(JournalQuotidien::class, 'id_journal_quotidien');
    }
}
