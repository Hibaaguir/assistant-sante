<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DoctorInvitationMail;
use App\Models\DoctorInvitation;
use App\Models\ProfilSante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfilSanteController extends Controller
{
    public function store(Request $request)
    {
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
            'consulte_medecin' => ['required', 'boolean'],
            'medecin_peut_consulter' => ['required_if:consulte_medecin,1', 'boolean'],
            'medecin_email' => [
                'nullable',
                'email',
                'required_if:medecin_peut_consulter,1',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $currentEmail = strtolower((string) Auth::user()?->email);
                    if (strtolower((string) $value) === $currentEmail) {
                        $fail("L'email du medecin doit etre different de votre email.");
                    }
                },
            ],
            'fumeur' => ['required', 'boolean'],
            'alcool' => ['required', 'boolean'],
        ]);

        $validated['user_id'] = Auth::id();
        $validated['medecin_email'] = $validated['medecin_email'] !== null
            ? strtolower(trim($validated['medecin_email']))
            : null;

        $profil = ProfilSante::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        $this->syncDoctorInvitation($profil);

        return response()->json([
            'message' => 'Profil sante enregistre avec succes.',
            'data' => $profil,
        ]);
    }

    public function show()
    {
        $user = Auth::user();
        $profil = $user->profilSante;

        return response()->json([
            'data' => $profil,
            'user' => $user,
        ]);
    }

    private function syncDoctorInvitation(ProfilSante $profil): void
    {
        $patient = Auth::user();
        if (! $patient) {
            return;
        }

        $shouldInvite = (bool) $profil->consulte_medecin
            && (bool) $profil->medecin_peut_consulter
            && ! empty($profil->medecin_email);

        if (! $shouldInvite) {
            DoctorInvitation::query()
                ->where('patient_user_id', $patient->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);
            return;
        }

        $doctorEmail = strtolower(trim((string) $profil->medecin_email));
        $doctor = User::query()
            ->whereRaw('LOWER(email) = ?', [$doctorEmail])
            ->first();

        if ($doctor && $doctor->id === $patient->id) {
            return;
        }

        $existing = DoctorInvitation::query()
            ->where('patient_user_id', $patient->id)
            ->where('doctor_email', $doctorEmail)
            ->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                ]);
            } else {
                $existing->update([
                    'doctor_user_id' => $doctor?->id,
                    'doctor_email' => $doctorEmail,
                    'status' => 'pending',
                    'token' => Str::random(64),
                    'accepted_at' => null,
                    'rejected_at' => null,
                    'revoked_at' => null,
                ]);
            }
        } else {
            DoctorInvitation::query()->create([
                'patient_user_id' => $patient->id,
                'doctor_user_id' => $doctor?->id,
                'doctor_email' => $doctorEmail,
                'status' => 'pending',
                'token' => Str::random(64),
            ]);
        }

        try {
            Mail::to($doctorEmail)->send(new DoctorInvitationMail($patient, $doctorEmail));
        } catch (\Throwable $e) {
            Log::warning('Doctor invitation email failed: '.$e->getMessage());
        }
    }
}
