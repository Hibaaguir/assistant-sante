<?php

namespace App\Services;

use App\Models\DoctorInvitation;
use App\Models\User;

class DoctorInvitationLinker
{
    public function linkForUser(User $user): bool
    {
        if ($user->role !== 'medecin') {
            return false;
        }

        $updated = DoctorInvitation::query()
            ->whereRaw('LOWER(doctor_email) = ?', [strtolower($user->email)])
            ->where(function ($query) use ($user) {
                $query
                    ->whereNull('doctor_user_id')
                    ->orWhere('doctor_user_id', '!=', $user->id);
            })
            ->update([
                'doctor_user_id' => $user->id,
            ]);

        return $updated > 0;
    }
}
