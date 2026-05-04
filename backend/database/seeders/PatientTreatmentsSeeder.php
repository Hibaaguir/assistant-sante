<?php

namespace Database\Seeders;

use App\Models\HealthData;
use App\Models\Treatment;
use App\Models\TreatmentCatalog;
use App\Models\User;
use Carbon\Carbon;

/** Crée les traitements de chaque patient (liés à leur première HealthData). */
class PatientTreatmentsSeeder extends MedicalSeeder
{
    public function run(): void
    {
        $this->faker = fake('fr_FR');
        $startDate   = Carbon::parse(self::SEED_START)->startOfDay();

        foreach ($this->patients() as $patientData) {
            $user = $this->findPatientByEmail($patientData['email']);
            if (! $user) {
                continue;
            }

            $seedHealthData = HealthData::firstOrCreate(
                ['user_id' => $user->id, 'date' => $startDate->toDateString()],
                ['doctor_observation' => null]
            );

            foreach ($patientData['treatments'] as $row) {
                $catalog = TreatmentCatalog::firstOrCreate([
                    'treatment_type' => trim((string) $row['type']),
                    'treatment_name' => trim((string) $row['name']),
                ]);

                $treatStart = Carbon::parse($row['start_date']);
                $treatEnd   = Carbon::parse($row['end_date']);

                if ($treatEnd->lt($treatStart)) {
                    [$treatStart, $treatEnd] = [$treatEnd, $treatStart];
                }

                Treatment::create([
                    'health_data_id'      => $seedHealthData->id,
                    'treatment_catalog_id'=> $catalog->id,
                    'dose'                => $row['dose'],
                    'frequency'           => $row['frequency'] ?? 'day',
                    'daily_doses'         => max(1, min((int) ($row['daily_doses'] ?? 1), 4)),
                    'start_date'          => $treatStart->toDateString(),
                    'end_date'            => $treatEnd->toDateString(),
                ]);
            }
        }
    }

    private function findPatientByEmail(string $email): ?User
    {
        return User::query()
            ->whereHas('account', fn ($q) => $q->where('email', strtolower($email)))
            ->first();
    }
}
