<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TacheBienEtre extends Model
{
    protected $table = 'taches_bien_etre';

    protected $fillable = [
        'user_id',
        'titre',
        'categorie',
        'date_echeance',
        'est_complete',
        'terminee_le',
    ];

    protected $casts = [
        'date_echeance' => 'date:Y-m-d',
        'est_complete' => 'boolean',
        'terminee_le' => 'datetime',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
