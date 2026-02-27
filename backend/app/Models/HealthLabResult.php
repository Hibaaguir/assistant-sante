<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthLabResult extends Model
{
    protected $fillable = [
        'user_id',
        'analysis_type',
        'value',
        'unit',
        'analysis_date',
        'notes',
    ];

    protected $casts = [
        'analysis_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
