<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisResult extends Model
{
    protected $table = 'analysis_results';

    protected $fillable = [
        'user_id',
        'analysis_type',
        'result_name',
        'value',
        'unit',
        'analysis_date',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relation: analysis results belong to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
