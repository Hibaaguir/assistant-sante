<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalActivity extends Model
{
    protected $table = 'physical_activities';

    protected $fillable = [
        'journal_entry_id',
        'activity_type',
        'duration_minutes',
        'intensity',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation: chaque activite physique appartient a une entree de journal
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
