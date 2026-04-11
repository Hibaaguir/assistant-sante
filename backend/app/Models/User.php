<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // Health data records (one per day) — vitals/labs/checks are children of health_data
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

    // Relations with treatments
    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'user_id', 'id');
    }
}
