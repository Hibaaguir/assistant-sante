<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
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

    protected $casts = [
        'entry_date' => 'date:Y-m-d',
        'meals' => 'array',
        'tobacco_types' => 'array',
        'hydration' => 'decimal:1',
        'tobacco' => 'boolean',
        'alcohol' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

