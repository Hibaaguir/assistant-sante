<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthProfile extends Model
{
    protected $table = 'health_profiles';

    protected $fillable = [
        'user_id',
        'gender',
        'height',
        'initial_weight',
        'current_weight',
        'blood_type',
        'goals',
        'allergies',
        'chronic_diseases',
        'smoker',
        'alcoholic',
    ];

    protected $casts = [
        'allergies' => 'array',
        'chronic_diseases' => 'array',
        'goals' => 'array',
        'smoker' => 'boolean',
        'alcoholic' => 'boolean',
    ];

    // Relation indiquant que chaque profil de sante appartient a un utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



    // Verifier si le profil de sante est complet (tous les champs requis sont remplis)
    public function isComplete(): bool
    {
        return $this->gender !== null 
            && $this->height !== null 
            && $this->initial_weight !== null
            && $this->blood_type !== null;
    }
}
