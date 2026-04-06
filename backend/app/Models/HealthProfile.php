<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthProfile extends Model
{
    protected $table = 'health_profiles';

    protected $fillable = [
        'user_id',
        'gender',
        'height',
        'weight',
        'blood_type',
        'goals',
        'allergies',
        'chronic_diseases',
        'smoker',
        'alcoholic',
        'consults_doctor',
        'doctor_can_consult',
        'doctor_email',
    ];

    protected $casts = [
        'allergies' => 'array',
        'chronic_diseases' => 'array',
        'goals' => 'array',
        'smoker' => 'boolean',
        'alcoholic' => 'boolean',
        'consults_doctor' => 'boolean',
        'doctor_can_consult' => 'boolean',
    ];

    // Relation indicating that each health profile belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



    // Check if health profile is complete (all required fields are filled)
    public function isComplete(): bool
    {
        return $this->gender !== null 
            && $this->height !== null 
            && $this->weight !== null 
            && $this->blood_type !== null;
    }
}
