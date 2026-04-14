<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'treatment_id',
        'kind',
        'target_date',
        'message',
        'read_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'read_at'     => 'datetime',
    ];

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }
}
