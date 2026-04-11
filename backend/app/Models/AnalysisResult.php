<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisResult extends Model
{
    protected $table = 'analysis_results';

    protected $fillable = [
        'health_data_id',
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

    // Relation: analysis result belongs to a health_data entry (owner is health_data.user_id)
    public function healthData(): BelongsTo
    {
        return $this->belongsTo(HealthData::class, 'health_data_id');
    }
}
