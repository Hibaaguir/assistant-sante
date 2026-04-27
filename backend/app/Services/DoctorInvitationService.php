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
    // Verifier si email doctor a une invitation en attente existe
    public function existsForEmail(string $email): bool
    {
        return DoctorInvitation::whereRaw('LOWER(doctor_email) = ?', [strtolower($email)])//lena where raw bch tnajam testaamel LOWER() snn testaamel where aadia
            ->where('status', 'pending')
            ->exists();
    }

    // Lier les invitations en attente au compte du medecin
    public function linkToDoctor(User $doctor): bool
    {
        if ($doctor->role !== 'doctor' || !$doctor->account?->email) {//verification lele role w aandou email wela 
            return false;
        }

        return DoctorInvitation::whereRaw('LOWER(doctor_email) = ?', [strtolower($doctor->account->email)])
            ->where(fn ($q) => $q->whereNull('doctor_user_id')->orWhere('doctor_user_id', '!=', $doctor->id))
            ->update(['doctor_user_id' => $doctor->id]) > 0;
    }

    // Creer, mettre a jour ou revoquer l'invitation du profil de sante
    public function sync(HealthProfile $profile, ?string $previousEmail, User $patient, bool $doctorInvited = false, ?string $doctorEmail = null): void
    {
        $shouldInvite = $doctorInvited && !empty($doctorEmail);

        if (!$shouldInvite) {
            DoctorInvitation::where('patient_user_id', $patient->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->update(['status' => 'revoked', 'revoked_at' => now()]);
            return;
        }

        $doctorEmail  = strtolower(trim((string) $doctorEmail));
        $emailChanged = $doctorEmail !== ($previousEmail !== null ? strtolower(trim($previousEmail)) : null);

        // Revoquer les anciennes invitations si l'email a change
        if ($emailChanged) {
            DoctorInvitation::where('patient_user_id', $patient->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->where('doctor_email', '!=', $doctorEmail)
                ->update(['status' => 'revoked', 'revoked_at' => now()]);
        }

        $doctor = $this->resolveDoctor($doctorEmail);

        // Eviter l'auto-invitation
        if ($doctor?->id === $patient->id) {
            return;
        }

        // Promouvoir en role medecin si le compte existe
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
                    'doctor_invited' => true,
                    'status'         => 'pending',
                    'token'          => Str::random(64),
                    'accepted_at'    => null,
                    'rejected_at'    => null,
                    'revoked_at'     => null,
                ]);
            } else {
                // Toujours actif - maintenir la synchronisation
                $existing->update(['doctor_user_id' => $doctor?->id, 'doctor_invited' => true]);
            }
        } else {
            DoctorInvitation::create([
                'patient_user_id' => $patient->id,
                'doctor_user_id'  => $doctor?->id,
                'doctor_email'    => $doctorEmail,
                'doctor_invited'  => true,
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

    // Trouver une invitation acceptee entre un medecin et un patient
    public function findAccepted(int $doctorId, int $patientId): ?DoctorInvitation
    {
        return DoctorInvitation::where('doctor_user_id', $doctorId)
            ->where('patient_user_id', $patientId)
            ->where('status', 'accepted')
            ->latest('accepted_at')
            ->latest('id')
            ->first();
    }

    // Verifier si un medecin possede une invitation et lier son compte
    public function authorizeAndLink(DoctorInvitation $invitation, User $doctor): bool
    {
        // Deja lie a ce medecin
        if ($invitation->doctor_user_id === $doctor->id) {
            return true;
        }

        // Pas encore lie - correspondre par email et lier maintenant
        $doctorEmail     = strtolower($doctor->account?->email ?? '');
        $invitationEmail = strtolower($invitation->doctor_email);

        if ($doctorEmail !== '' && $doctorEmail === $invitationEmail && is_null($invitation->doctor_user_id)) {
            $invitation->update(['doctor_user_id' => $doctor->id]);
            return true;
        }

        return false;
    }

    // Resoudre un compte utilisateur par adresse email
    private function resolveDoctor(string $email): ?User
    {
        return User::whereHas('account', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]))->first();
    }
}
