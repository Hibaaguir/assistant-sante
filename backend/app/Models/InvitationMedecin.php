<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationMedecin extends Model
{
    protected $table = 'invitations_medecin';

    protected $fillable = [
        'id_utilisateur_patient',
        'id_utilisateur_medecin',
        'email_medecin',
        'statut',
        'jeton',
        'accepted_at',
        'rejected_at',
        'revoked_at',
        'observation_generale',
        'observation_generale_mis_a_jour',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revoked_at' => 'datetime',
        'observation_generale_mis_a_jour' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur_patient');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur_medecin');
    }
}
