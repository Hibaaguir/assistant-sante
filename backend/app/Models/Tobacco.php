<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tobacco extends Model
{
    protected $table = 'tobacco';

    protected $fillable = [
        'journal_entry_id',
        'tobacco_type',
        'cigarettes_per_day',
        'puffs_per_day',
    ];

    protected $casts = [
        'cigarettes_per_day' => 'integer',
        'puffs_per_day'      => 'integer',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    // Relation: chaque entree de tabac appartient a une entree de journal
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
