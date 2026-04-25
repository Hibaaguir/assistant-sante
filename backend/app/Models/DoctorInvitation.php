<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorInvitation extends Model
{
    protected $table = 'doctor_invitations';

    protected $fillable = [
        'patient_user_id',
        'doctor_user_id',
        'doctor_email',
        'doctor_invited',
        'status',
        'token',
        'accepted_at',
        'rejected_at',
        'revoked_at',
    ];

    protected $casts = [
        'patient_user_id' => 'integer',
        'doctor_user_id'  => 'integer',
        'doctor_invited'  => 'boolean',
        'accepted_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'revoked_at'      => 'datetime',
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
