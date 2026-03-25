<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorInvitation extends Model
{

//protected $guarded = [];
    protected $fillable = [
        'patient_user_id',
        'doctor_user_id',
        'doctor_email',
        'status',
        'token',
        'accepted_at',
        'rejected_at',
        'revoked_at',
        'general_observation',
        'general_observation_updated_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revoked_at' => 'datetime',
        'general_observation_updated_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }
}
