<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \Illuminate\Notifications\Notifiable;
    use \Laravel\Sanctum\HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'account_id',
        'name',
        'date_of_birth',
        'profile_photo',
        'age',
        'role',
        'specialty',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'age' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function healthProfile()
    {
        return $this->hasOne(HealthProfile::class, 'user_id', 'id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'user_id', 'id');
    }

    // Enregistrements de donnees de sante (un par jour) — les signes vitaux/analyses/verifications sont des enfants de health_data
    public function healthDataEntries()
    {
        return $this->hasMany(HealthData::class, 'user_id', 'id');
    }

    public function invitationsAsPatient()
    {
        return $this->hasMany(DoctorInvitation::class, 'patient_user_id', 'id');
    }

    public function invitationsAsDoctor()
    {
        return $this->hasMany(DoctorInvitation::class, 'doctor_user_id', 'id');
    }

    // Relations avec les traitements
    public function treatments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Treatment::class,
            HealthData::class,
            'user_id',
            'health_data_id',
            'id',
            'id'
        );
    }
}
