<?php

namespace App\Services;

use App\Mail\DoctorInvitationMail;
use App\Models\DoctorInvitation;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DoctorInvitationService
{
    // Check if a pending invitation exists for a given email
    public function existsForEmail(string $email): bool
    {
        return DoctorInvitation::whereRaw('LOWER(doctor_email) = ?', [strtolower($email)])
            ->where('status', 'pending')
            ->exists();
    }

    // Link all pending invitations matching the doctor's email to their account
    public function linkToDoctor(User $doctor): bool
    {
        if ($doctor->role !== 'doctor' || !$doctor->account?->email) {
            return false;
        }

        return DoctorInvitation::whereRaw('LOWER(doctor_email) = ?', [strtolower($doctor->account->email)])
            ->where(fn ($q) => $q->whereNull('doctor_user_id')->orWhere('doctor_user_id', '!=', $doctor->id))
            ->update(['doctor_user_id' => $doctor->id]) > 0;
    }

    // Create, update, or revoke the invitation when a health profile is saved
    public function sync(HealthProfile $profile, ?string $previousEmail, User $patient): void
    {
        $shouldInvite = (bool) $profile->doctor_invited && !empty($profile->doctor_email);

        if (!$shouldInvite) {
            DoctorInvitation::where('patient_user_id', $patient->id)
                ->where('status', 'pending')
                ->update(['status' => 'revoked', 'revoked_at' => now()]);
            return;
        }

        $doctorEmail  = strtolower(trim((string) $profile->doctor_email));
        $emailChanged = $doctorEmail !== ($previousEmail !== null ? strtolower(trim($previousEmail)) : null);

        // Revoke old invitations when the doctor email changed
        if ($emailChanged) {
            DoctorInvitation::where('patient_user_id', $patient->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->where('doctor_email', '!=', $doctorEmail)
                ->update(['status' => 'revoked', 'revoked_at' => now()]);
        }

        $doctor = $this->resolveDoctor($doctorEmail);

        // Prevent self-invitation
        if ($doctor?->id === $patient->id) {
            return;
        }

        // Promote to doctor role if the account exists but has a different role
        if ($doctor && $doctor->role !== 'doctor') {
            try {
                $doctor->update(['role' => 'doctor']);
            } catch (\Throwable $e) {
                Log::warning('Could not promote user to doctor role.', [
                    'email' => $doctorEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $existing = DoctorInvitation::where('patient_user_id', $patient->id)
            ->where('doctor_email', $doctorEmail)
            ->first();

        if ($existing) {
            $needsReset = $emailChanged || in_array($existing->status, ['revoked', 'rejected']);

            if ($needsReset) {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'status'         => 'pending',
                    'token'          => Str::random(64),
                    'accepted_at'    => null,
                    'rejected_at'    => null,
                    'revoked_at'     => null,
                ]);
            } else {
                // Still active — keep doctor_user_id in sync
                $existing->update(['doctor_user_id' => $doctor?->id]);
            }
        } else {
            DoctorInvitation::create([
                'patient_user_id' => $patient->id,
                'doctor_user_id'  => $doctor?->id,
                'doctor_email'    => $doctorEmail,
                'status'          => 'pending',
                'token'           => Str::random(64),
            ]);
        }

        // Send email only when the doctor email changed and they have no account yet
        if ($emailChanged && !$doctor) {
            try {
                Mail::to($doctorEmail)->send(new DoctorInvitationMail($patient, $doctorEmail));
            } catch (\Throwable $e) {
                Log::error('Doctor invitation email failed.', [
                    'doctor_email' => $doctorEmail,
                    'patient_id'   => $patient->id,
                    'error'        => $e->getMessage(),
                ]);
            }
        }
    }

    // Find an accepted invitation between a doctor and a patient
    public function findAccepted(int $doctorId, int $patientId): ?DoctorInvitation
    {
        return DoctorInvitation::where('doctor_user_id', $doctorId)
            ->where('patient_user_id', $patientId)
            ->where('status', 'accepted')
            ->latest('accepted_at')
            ->latest('id')
            ->first();
    }

    // Check if a doctor owns an invitation, linking their account if not yet linked
    public function authorizeAndLink(DoctorInvitation $invitation, User $doctor): bool
    {
        // Already linked to this doctor
        if ($invitation->doctor_user_id === $doctor->id) {
            return true;
        }

        // Not linked yet — match by email and link now
        $doctorEmail     = strtolower($doctor->account?->email ?? '');
        $invitationEmail = strtolower($invitation->doctor_email);

        if ($doctorEmail !== '' && $doctorEmail === $invitationEmail && is_null($invitation->doctor_user_id)) {
            $invitation->update(['doctor_user_id' => $doctor->id]);
            return true;
        }

        return false;
    }

    // Resolve a user account by email address
    private function resolveDoctor(string $email): ?User
    {
        return User::whereHas('account', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]))->first();
    }
}
