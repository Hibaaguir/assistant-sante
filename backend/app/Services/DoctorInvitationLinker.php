<?php

namespace App\Services;

use App\Models\DoctorInvitation;
use App\Models\User;

class DoctorInvitationLinker
{
    public function lierPourUtilisateur(User $user): bool
    {
        if ($user->role !== 'medecin') {
            return false;
        }

        return DoctorInvitation::query()
            ->whereRaw('LOWER(doctor_email) = ?', [strtolower($user->email)])
            ->where(fn ($q) => $q->whereNull('doctor_user_id')->orWhere('doctor_user_id', '!=', $user->id))
            ->update(['doctor_user_id' => $user->id]) > 0;
    }
}
