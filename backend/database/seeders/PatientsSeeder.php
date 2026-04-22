<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AnalysisResult;
use App\Models\DoctorInvitation;
use App\Models\HealthData;
use App\Models\HealthProfile;
use App\Models\JournalEntry;
use App\Models\Notification;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Models\VitalSigns;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Crée les 8 comptes patients, leurs profils de santé et leurs invitations médecin.
 * Efface également toutes les données médicales existantes pour un seed déterministe.
 */
class PatientsSeeder extends MedicalSeeder
{
    public function run(): void
    {
        $this->faker = fake('fr_FR');
        $startDate   = Carbon::today()->subDays(29)->startOfDay();

        $doctors = User::query()
            ->where('role', 'doctor')
            ->with('account')
            ->get()
            ->keyBy(fn (User $u) => strtolower((string) $u->account?->email));

        foreach ($this->patients() as $patientData) {
            $account = Account::updateOrCreate(
                ['email' => strtolower((string) $patientData['email'])],
                [
                    'password'       => Hash::make(self::DEFAULT_PASSWORD),
                    'account_status' => $patientData['account_status'] ?? 'active',
                ]
            );

            $dob  = Carbon::parse($patientData['date_of_birth']);
            $user = User::updateOrCreate(
                ['account_id' => $account->id],
                [
                    'name'          => $patientData['name'],
                    'date_of_birth' => $dob->toDateString(),
                    'profile_photo' => null,
                    'age'           => $dob->age,
                    'role'          => 'user',
                    'specialty'     => null,
                ]
            );

            $this->clearPatientMedicalData($user);

            $doctorEmail = isset($patientData['doctor_email']) && $patientData['doctor_email']
                ? strtolower(trim((string) $patientData['doctor_email']))
                : null;

            HealthProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gender'          => $patientData['gender'],
                    'height'          => $patientData['height'],
                    'initial_weight'  => $patientData['initial_weight'],
                    'current_weight'  => $patientData['initial_weight'],
                    'blood_type'      => $patientData['blood_type'],
                    'goals'           => $patientData['goals'],
                    'allergies'       => $patientData['allergies'],
                    'chronic_diseases'=> $patientData['chronic_diseases'],
                    'smoker'          => $patientData['smoker'],
                    'alcoholic'       => $patientData['alcoholic'],
                    'doctor_invited'  => $doctorEmail !== null,
                    'doctor_email'    => $doctorEmail,
                ]
            );

            if ($doctorEmail) {
                $this->createOrUpdateDoctorInvitation($user, $patientData, $doctors, $startDate);
            }
        }
    }

    // ─── Nettoyage ─────────────────────────────────────────────────────────────

    private function clearPatientMedicalData(User $user): void
    {
        $treatmentIds = Treatment::query()
            ->whereHas('healthData', fn ($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        if ($treatmentIds->isNotEmpty()) {
            Notification::query()->whereIn('treatment_id', $treatmentIds)->delete();
            TreatmentCheck::query()->whereIn('treatment_id', $treatmentIds)->delete();
            Treatment::query()->whereIn('id', $treatmentIds)->delete();
        }

        $healthDataIds = HealthData::query()->where('user_id', $user->id)->pluck('id');
        if ($healthDataIds->isNotEmpty()) {
            VitalSigns::query()->whereIn('health_data_id', $healthDataIds)->delete();
            AnalysisResult::query()->whereIn('health_data_id', $healthDataIds)->delete();
            HealthData::query()->whereIn('id', $healthDataIds)->delete();
        }

        JournalEntry::query()->where('user_id', $user->id)->delete();
        DoctorInvitation::query()->where('patient_user_id', $user->id)->delete();
    }

    // ─── Invitation médecin ────────────────────────────────────────────────────

    private function createOrUpdateDoctorInvitation(User $patient, array $patientData, Collection $doctors, Carbon $startDate): void
    {
        $doctorEmail = strtolower(trim((string) $patientData['doctor_email']));
        $status      = strtolower((string) ($patientData['invitation_status'] ?? 'pending'));

        if (! in_array($status, ['pending', 'accepted', 'rejected'], true)) {
            $status = 'pending';
        }

        $doctorUser = $doctors->get($doctorEmail);

        DoctorInvitation::updateOrCreate(
            ['patient_user_id' => $patient->id, 'doctor_email' => $doctorEmail],
            [
                'doctor_user_id' => in_array($status, ['accepted', 'rejected']) ? $doctorUser?->id : null,
                'status'         => $status,
                'token'          => Str::random(64),
                'accepted_at'    => $status === 'accepted' ? $startDate->copy()->addDays($this->faker->numberBetween(2, 12)) : null,
                'rejected_at'    => $status === 'rejected' ? $startDate->copy()->addDays($this->faker->numberBetween(1, 8))  : null,
                'revoked_at'     => null,
            ]
        );
    }
}
