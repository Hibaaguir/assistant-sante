<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property int $compte_id
 * @property \Illuminate\Support\Carbon|null $date_naissance
 * @property string|null $profile_photo
 * @property int|null $age
 * @property-read \App\Models\Compte|null $compte
 * @property-read \App\Models\ProfilSante|null $profilSante
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'compte_id',
        'date_naissance',
        'profile_photo',
        'age',
    ];

    // Aucun champ caché nécessaire ici

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'age' => 'integer',
        ];
    }
    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }
//relation des table 
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
