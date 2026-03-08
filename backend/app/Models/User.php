<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'date_of_birth',
        'role',
        'specialite',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    public function profilSante()
    {
        return $this->hasOne(ProfilSante::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function healthVitals()
    {
        return $this->hasMany(HealthVital::class);
    }

    public function healthLabResults()
    {
        return $this->hasMany(HealthLabResult::class);
    }

    public function healthTreatmentChecks()
    {
        return $this->hasMany(HealthTreatmentCheck::class);
    }

    public function doctorInvitationsReceived()
    {
        return $this->hasMany(DoctorInvitation::class, 'doctor_user_id');
    }

    public function doctorInvitationsSent()
    {
        return $this->hasMany(DoctorInvitation::class, 'patient_user_id');
    }
}
