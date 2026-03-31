<?php

namespace App\Services;

use App\Models\DoctorInvitation;
use App\Models\Utilisateur;

class DoctorInvitationLinker
{
    // Link pending doctor invitations to a user account
    public function linkForUser(Utilisateur $utilisateur): bool
    {
        if ($utilisateur->role !== 'medecin') {
            return false;
        }

        // Accéder à l'email via la relation compte
        $email = $utilisateur->compte?->email;
        if (!$email) {
            return false;
        }

        return DoctorInvitation::query()
            ->whereRaw('LOWER(doctor_email) = ?', [strtolower($email)])
            ->where(fn ($q) => $q->whereNull('id_medecin_utilisateur')->orWhere('id_medecin_utilisateur', '!=', $utilisateur->id))
            ->update(['id_medecin_utilisateur' => $utilisateur->id]) > 0;
    }
}
