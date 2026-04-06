<?php

namespace App\Services;

use App\Models\DoctorInvitation;
use App\Models\User;

class DoctorInvitationLinker
{
    // Link pending doctor invitations to a user account
    public function linkForUser(User $user): bool
    {
        if ($user->role !== 'doctor') {
            return false;
        }

        // Access email via the account relation
        $email = $user->account?->email;
        if (!$email) {
            return false;
        }

        return DoctorInvitation::query()
            ->whereRaw('LOWER(doctor_email) = ?', [strtolower($email)])
            ->where(fn ($q) => $q->whereNull('doctor_user_id')->orWhere('doctor_user_id', '!=', $user->id))
            ->update(['doctor_user_id' => $user->id]) > 0;
    }
}
