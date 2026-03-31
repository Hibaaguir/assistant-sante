<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuiviTraitement extends Model
{
    protected $table = 'suivi_traitement';

    protected $fillable = [
        'traitement_id',
        'date_controle',
        'pris',
        'verifie_a',
    ];

    protected $casts = [
        'date_controle' => 'date:Y-m-d',
        'pris' => 'boolean',
        'verifie_a' => 'datetime',
    ];

    public function traitement(): BelongsTo
    {
        return $this->belongsTo(Traitement::class);
    }
}
