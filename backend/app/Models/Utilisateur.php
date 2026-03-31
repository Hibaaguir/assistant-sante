<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Utilisateur extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \Illuminate\Notifications\Notifiable;
    use \Laravel\Sanctum\HasApiTokens;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'compte_id',
        'nom',
        'date_naissance',
        'photo_profil',
        'age',
        'role',
        'specialite',
        'statut_admin',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'age' => 'integer',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }

    public function profilSante()
    {
        return $this->hasOne(ProfilSante::class, 'id_utilisateur', 'id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalQuotidien::class, 'id_utilisateur', 'id');
    }

    // Relations avec les tables de santé
    public function signesVitaux()
    {
        return $this->hasMany(SignesVitaux::class, 'id_utilisateur', 'id');
    }

    public function resultatsAnalyses()
    {
        return $this->hasMany(ResultatAnalyse::class, 'id_utilisateur', 'id');
    }

    public function invitationsMedecinAsPatient()
    {
        return $this->hasMany(InvitationMedecin::class, 'id_utilisateur_patient', 'id');
    }

    public function invitationsMedecinAsDoctor()
    {
        return $this->hasMany(InvitationMedecin::class, 'id_utilisateur_medecin', 'id');
    }
}
