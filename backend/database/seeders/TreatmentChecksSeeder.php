<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Génère 30 jours de suivi d'observance par patient :
 * TreatmentCheck (prise/manquée) et Notifications (rappel + dose manquée).
 */
class TreatmentChecksSeeder extends MedicalSeeder
{
    public function run(): void
    {
        $this->faker = fake('fr_FR');
        $startDate   = Carbon::today()->subDays(29)->startOfDay();

        foreach ($this->patients() as $patientIndex => $patientData) {
            $user = User::query()
                ->whereHas('account', fn ($q) => $q->where('email', strtolower($patientData['email'])))
                ->first();

            if (! $user) {
                continue;
            }

            $treatments = Treatment::query()
                ->whereHas('healthData', fn ($q) => $q->where('user_id', $user->id))
                ->with('treatmentCatalog')
                ->get();

            for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
                $date    = $startDate->copy()->addDays($dayOffset);
                $metrics = $this->buildDailyMetrics($patientData, $patientIndex, $dayOffset, $date);

                $this->seedChecksAndNotifications(
                    $user,
                    $treatments,
                    $metrics,
                    (float) $patientData['adherence'],
                    $patientIndex,
                    $dayOffset,
                    $date
                );
            }
        }
    }

    // ─── Checks & notifications ───────────────────────────────────────────────

    private function seedChecksAndNotifications(
        User $user,
        Collection $treatments,
        array $metrics,
        float $adherence,
        int $patientIndex,
        int $dayOffset,
        Carbon $date
    ): void {
        $baseHours = [8, 14, 20, 22];

        foreach ($treatments as $treatment) {
            if (! $this->isTreatmentDueOnDate($treatment, $date)) {
                continue;
            }

            $medicineName = $treatment->treatmentCatalog?->treatment_name ?? 'votre traitement';

            Notification::create([
                'treatment_id' => $treatment->id,
                'kind'         => 'reminder',
                'target_date'  => $date->toDateString(),
                'message'      => 'N\'oubliez pas de prendre ' . $medicineName . ' aujourd\'hui.',
                'read_at'      => $date->lt(Carbon::today()->subDays(2)) && $this->faker->boolean(60)
                    ? $date->copy()->setTime(21, $this->faker->numberBetween(0, 45))
                    : null,
            ]);

            $doses        = strtolower((string) $treatment->frequency) === 'day'
                ? max(1, min((int) ($treatment->daily_doses ?? 1), 4))
                : 1;
            $hasMissedDose = false;

            for ($doseIndex = 1; $doseIndex <= $doses; $doseIndex++) {
                $penalty  = ($date->isWeekend() ? 8 : 0) + ($metrics['stress'] >= 8 ? 10 : 0);
                $target   = max(45, (int) round(($adherence * 100) - $penalty));
                $score    = (($dayOffset + $doseIndex + ($patientIndex * 5)) * 13) % 100;
                $taken    = $score < $target;

                if (! $taken) {
                    $hasMissedDose = true;
                }

                $hour      = $baseHours[min($doseIndex - 1, count($baseHours) - 1)];
                $checkedAt = $taken
                    ? $date->copy()->setTime($hour, (($dayOffset + $doseIndex + $user->id) * 7) % 60)
                    : null;

                TreatmentCheck::create([
                    'treatment_id'  => $treatment->id,
                    'user_id'       => $user->id,
                    'check_date'    => $date->toDateString(),
                    'medication_key'=> $treatment->id . '__dose_' . $doseIndex,
                    'taken'         => $taken,
                    'checked_at'    => $checkedAt,
                ]);
            }

            if ($hasMissedDose) {
                Notification::create([
                    'treatment_id' => $treatment->id,
                    'kind'         => 'missed',
                    'target_date'  => $date->toDateString(),
                    'message'      => 'Vous avez manqué au moins une prise de ' . $medicineName . ' aujourd\'hui.',
                    'read_at'      => null,
                ]);
            }
        }
    }

    private function isTreatmentDueOnDate(Treatment $treatment, Carbon $date): bool
    {
        if ($treatment->start_date && $date->lt($treatment->start_date)) {
            return false;
        }
        if ($treatment->end_date && $date->gt($treatment->end_date)) {
            return false;
        }

        $frequency = strtolower((string) ($treatment->frequency ?? 'day'));

        if ($frequency === 'week') {
            return $treatment->start_date
                ? $date->dayOfWeek === $treatment->start_date->dayOfWeek
                : $date->dayOfWeek === Carbon::MONDAY;
        }

        if ($frequency === 'month') {
            return $treatment->start_date
                ? $date->day === $treatment->start_date->day
                : $date->day === 1;
        }

        return true;
    }
}
