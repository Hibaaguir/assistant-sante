<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property-read \App\Models\ProfilSante|null $profilSante
 */
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
}
