<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model that represents a journal entry in the database
class JournalEntry extends Model
{
    protected $table = 'journal_entries';

    // List of fields that can be automatically filled upon creation or update
    protected $fillable = [
        'user_id',
        'entry_date',
        'sleep',
        'stress',
        'energy',
        'caffeine',
        'hydration',
        'alcohol',
        'alcohol_glasses',
    ];

    // Defines the type of certain data so Laravel converts them automatically
    protected $casts = [
        'entry_date' => 'date:Y-m-d',
        'caffeine' => 'integer',
        'hydration' => 'decimal:1',
        'alcohol' => 'boolean',
        'alcohol_glasses' => 'integer',
    ];

    // Relation indicating that each journal entry belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation indicating that a journal entry can contain multiple meals
    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class, 'journal_entry_id');
    }

    // Relation indicating that a journal entry can contain multiple physical activities
    public function physicalActivities(): HasMany
    {
        return $this->hasMany(PhysicalActivity::class, 'journal_entry_id');
    }

    // Relation indicating that a journal entry can contain multiple tobacco uses
    public function tobacco(): HasMany
    {
        return $this->hasMany(Tobacco::class, 'journal_entry_id');
    }
}
