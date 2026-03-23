<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilSante;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfilSanteController extends Controller
{
    public function enregistrer(Request $request)
    {
        try {
            if (is_string($request->input('sexe'))) {
                $request->merge([
                    'sexe' => strtolower(trim($request->input('sexe'))),
                ]);
            }

            $validated = $request->validate([
                'sexe' => ['required', Rule::in(['homme', 'femme'])],
                'taille' => ['required', 'numeric', 'min:30', 'max:250'],
                'poids' => ['required', 'numeric', 'min:1', 'max:300'],
                'groupe_sanguin' => ['required', 'string', 'max:5'],
                'objectifs' => ['nullable', 'array'],
                'objectifs.*' => ['string', 'max:120'],
                'allergies' => ['nullable', 'array'],
                'allergies.*' => ['string', 'max:100'],
                'maladies_chroniques' => ['nullable', 'array'],
                'maladies_chroniques.*' => ['string', 'max:120'],
                'traitements' => ['nullable', 'array'],
                'traitements.*.type' => ['required_with:traitements', 'string', 'max:120'],
                'traitements.*.name' => ['nullable', 'string', 'max:255'],
                'traitements.*.dose' => ['nullable', 'string', 'max:120'],
                'traitements.*.frequency_unit' => ['nullable', Rule::in(['jour', 'semaine', 'mois'])],
                'traitements.*.frequency_count' => ['nullable', 'integer', 'min:1'],
                'traitements.*.duration' => ['nullable', 'string', 'max:120'],
                'prend_medicament' => ['required', 'boolean'],
                'nom_medicament' => ['nullable', 'string', 'max:255', 'required_if:prend_medicament,1'],
                'fumeur' => ['required', 'boolean'],
                'alcool' => ['required', 'boolean'],
                'activite_physique' => ['required', 'boolean'],
                'activites_physiques' => ['nullable', 'array'],
                'activites_physiques.*' => ['string', 'max:120'],
                'frequence_activite_physique' => ['nullable', Rule::in(['1-2 fois', '3-4 fois', '5+ fois'])],
            ]);

            if (! ($validated['activite_physique'] ?? false)) {
                $validated['activites_physiques'] = [];
                $validated['frequence_activite_physique'] = null;
            }

            if (($validated['activite_physique'] ?? false) && empty($validated['activites_physiques'])) {
                $validated['frequence_activite_physique'] = null;
            }

            if (($validated['activite_physique'] ?? false)
                && ! empty($validated['activites_physiques'])
                && empty($validated['frequence_activite_physique'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'frequence_activite_physique' => ['Veuillez sélectionner la fréquence d\'activité physique.'],
                ]);
            }

            $validated['user_id'] = Auth::id();
            
            // Gérer l'email du médecin s'il est fourni
            if (isset($validated['medecin_email'])) {
                $validated['medecin_email'] = $validated['medecin_email'] !== null
                    ? strtolower(trim($validated['medecin_email']))
                    : null;
            }

            $existingProfil = ProfilSante::query()
                ->where('user_id', Auth::id())
                ->first();

            $previousDoctorEmail = $existingProfil?->medecin_email !== null
                ? strtolower(trim((string) $existingProfil->medecin_email))
                : null;

            $profil = ProfilSante::updateOrCreate(
                ['user_id' => Auth::id()],
                $validated
            );

            $this->synchroniserInvitationMedecin($profil, $previousDoctorEmail);

            return response()->json([
                'message' => 'Profil sante enregistre avec succes.',
                'data' => $profil,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Veuillez corriger les erreurs du formulaire.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Profil sante registration error: '.$e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'enregistrement du profil.'], 500);
        }
    }

    public function afficher()
    {
        $user = Auth::user();
        $profil = $user->profilSante;

        return response()->json([
            'data' => $profil,
            'user' => $user,
        ]);
    }

    private function synchroniserInvitationMedecin(ProfilSante $profil, ?string $previousDoctorEmail = null): void
    {
        $doctorInvitationModel = 'App\\Models\\DoctorInvitation';

        if (! class_exists($doctorInvitationModel) || ! Schema::hasTable('doctor_invitations')) {
            return;
        }

        $patient = Auth::user();
        if (! $patient) {
            return;
        }

        // Vérifier si le patient veut consulter un médecin (optionnel)
        $shouldInvite = (bool) ($profil->consulte_medecin ?? false)
            && (bool) ($profil->medecin_peut_consulter ?? false)
            && ! empty($profil->medecin_email);

        if (! $shouldInvite) {
            $doctorInvitationModel::query()
                ->where('patient_user_id', $patient->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
            return;
        }

        $doctorEmail = strtolower(trim((string) $profil->medecin_email));
        $doctorEmailChanged = $doctorEmail !== ($previousDoctorEmail !== null ? strtolower(trim($previousDoctorEmail)) : null);

        if ($doctorEmailChanged) {
            $doctorInvitationModel::query()
                ->where('patient_user_id', $patient->id)
                ->where('status', 'pending')
                ->where('doctor_email', '!=', $doctorEmail)
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
        }

        $doctorAccount = User::query()
            ->whereRaw('LOWER(email) = ?', [$doctorEmail])
            ->where('role', 'medecin')
            ->latest('id')
            ->first();

        $existingAccount = $doctorAccount ?: User::query()
            ->whereRaw('LOWER(email) = ?', [$doctorEmail])
            ->latest('id')
            ->first();

        if ($existingAccount && $existingAccount->id === $patient->id) {
            return;
        }

        $doctor = $doctorAccount;

        if ($existingAccount && ! $doctorAccount && $existingAccount->role !== 'medecin') {
            try {
                $existingAccount->update([
                    'role' => 'medecin',
                ]);
                $doctor = $existingAccount->fresh();
            } catch (QueryException $exception) {
                Log::warning('Doctor invitation role sync skipped: '.$exception->getMessage(), [
                    'doctor_email' => $doctorEmail,
                    'existing_account_id' => $existingAccount->id,
                ]);

                $doctor = User::query()
                    ->whereRaw('LOWER(email) = ?', [$doctorEmail])
                    ->where('role', 'medecin')
                    ->latest('id')
                    ->first();
            }
        }

        $existing = $doctorInvitationModel::query()
            ->where('patient_user_id', $patient->id)
            ->where('doctor_email', $doctorEmail)
            ->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                ]);
            } elseif ($doctorEmailChanged) {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                    'status' => 'pending',
                    'token' => Str::random(64),
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'revoked_at' => null,
                ]);
            } else {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                ]);
            }
        } else {
            $doctorInvitationModel::query()->create([
                'patient_user_id' => $patient->id,
                'doctor_user_id' => $doctor?->id,
                'doctor_email' => $doctorEmail,
                'status' => 'pending',
                'token' => Str::random(64),
            ]);
        }

        if (! $doctorEmailChanged || $doctor) {
            return;
        }

        $doctorInvitationMailClass = 'App\\Mail\\DoctorInvitationMail';
        if (! class_exists($doctorInvitationMailClass)) {
            return;
        }

        try {
            Mail::to($doctorEmail)->send(new $doctorInvitationMailClass(
                $patient,
                $doctorEmail,
                $existingAccount ? '/login' : '/doctor-login',
                $existingAccount ? 'medecin' : null,
            ));
        } catch (\Throwable $e) {
            Log::warning('Doctor invitation email failed: '.$e->getMessage());
        }
    }
}
