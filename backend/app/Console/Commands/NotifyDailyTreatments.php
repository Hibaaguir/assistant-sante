<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Notifications\DailyTreatmentNotification;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NotifyDailyTreatments extends Command
{
    protected $signature = 'treatments:notify
                            {--mode=all : reminder|missed|all}
                            {--date= : Target date in YYYY-MM-DD format}';

    protected $description = 'Send reminder and missed-dose notifications per treatment for today';

    public function __construct(private readonly HealthDataService $healthDataService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $mode = strtolower((string) $this->option('mode'));

        if (! in_array($mode, ['all', 'reminder', 'missed'], true)) {
            $this->error('Invalid --mode. Allowed: all, reminder, missed.');
            return self::FAILURE;
        }

        $date    = $this->resolveDate();
        $dateKey = $date->toDateString();
        $counts  = ['reminder' => 0, 'missed' => 0];

        User::whereHas('healthProfile')->get()->each(function (User $user) use ($mode, $date, $dateKey, &$counts) {
            $medicines = collect($this->healthDataService->resolveTreatmentMedicines($user->id));
            if ($medicines->isEmpty()) {
                return;
            }

            // Load all checks for this user and date at once
            $checks = TreatmentCheck::where('user_id', $user->id)
                ->whereDate('check_date', $dateKey)
                ->get();

            foreach ($medicines as $medicine) {
                $treatment = Treatment::find($medicine['id'] ?? null);
                if (! $treatment) {
                    continue;
                }

                $stats = $this->buildStats($medicine, $checks);

                $this->maybeNotify($treatment->id, 'reminder', $mode, $date, $stats, $counts);
                $this->maybeNotify($treatment->id, 'missed',   $mode, $date, $stats, $counts);
            }
        });

        $this->info("Reminder notifications sent: {$counts['reminder']}");
        $this->info("Missed-dose notifications sent: {$counts['missed']}");

        return self::SUCCESS;
    }

    private function maybeNotify(int $traitementId, string $type, string $mode, Carbon $date, array $stats, array &$counts): void
    {
        $guard = $type === 'reminder' ? $stats['expected_total'] > 0 : $stats['missing_total'] > 0;

        if (! in_array($mode, ['all', $type], true) || ! $guard || $this->alreadyNotified($traitementId, $type, $date)) {
            return;
        }

        $isMissed = $type === 'missed';

        Notification::create([
            'id'            => Str::uuid()->toString(),
            'type'          => DailyTreatmentNotification::class,
            'treatment_id' => $traitementId,
            'data'          => [
                'notification_kind' => $type,
                'target_date'       => $date->toDateString(),
                'title'             => $isMissed ? 'Forgotten treatments today' : 'Treatment reminders for today',
                'message'           => $isMissed
                    ? "You missed {$stats['missing_total']} dose(s) out of {$stats['expected_total']} expected."
                    : "You have {$stats['expected_total']} dose(s) expected today.",
                'expected_total'    => $stats['expected_total'],
                'taken_total'       => $stats['taken_total'],
                'missing_total'     => $stats['missing_total'],
                'items'             => $stats['items'],
            ],
        ]);

        $counts[$type]++;
    }

    // Build stats for a single medicine using pre-loaded checks
    private function buildStats(array $medicine, $checks): array
    {
        $medicineId   = (string) ($medicine['id'] ?? '');
        $medicineName = (string) ($medicine['name'] ?? 'Treatment');
        $expected     = max(1, (int) ($medicine['doses_per_day'] ?? 1));

        $taken = min($expected, $checks->filter(
            fn (TreatmentCheck $c) => str_starts_with((string) $c->medication_key, $medicineId . '__dose_') && (bool) $c->taken
        )->count());

        $missing = max(0, $expected - $taken);

        return [
            'items'          => [[
                'medication_id'   => $medicineId,
                'medication_name' => $medicineName,
                'expected'        => $expected,
                'taken'           => $taken,
                'missing'         => $missing,
            ]],
            'expected_total' => $expected,
            'taken_total'    => $taken,
            'missing_total'  => $missing,
        ];
    }

    private function resolveDate(): Carbon
    {
        $raw = $this->option('date');
        return is_string($raw) && trim($raw) !== '' ? Carbon::parse($raw)->startOfDay() : Carbon::today();
    }

    private function alreadyNotified(int $traitementId, string $type, Carbon $date): bool
    {
        return Notification::query()
            ->where('treatment_id', $traitementId)
            ->where('type', DailyTreatmentNotification::class)
            ->where('data->notification_kind', $type)
            ->where('data->target_date', $date->toDateString())
            ->exists();
    }
}
