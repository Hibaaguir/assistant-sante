<?php

namespace App\Console\Commands;

use App\Models\HealthTreatmentCheck;
use App\Models\Utilisateur;
use App\Notifications\TraitementJournalierNotification;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NotifierTraitementsDuJour extends Command
{
    protected $signature = 'treatments:notify
                            {--mode=all : reminder|missed|all}
                            {--date= : Date cible au format YYYY-MM-DD}';

    protected $description = 'Envoie des notifications de rappel et d\'oubli des traitements du jour';

    public function __construct(private readonly HealthDataService $serviceDonneesSante)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $mode = strtolower((string) $this->option('mode'));

        if (! in_array($mode, ['all', 'reminder', 'missed'], true)) {
            $this->error('Option --mode invalide. Valeurs autorisees: all, reminder, missed.');
            return self::FAILURE;
        }

        $date = $this->resolveDate();
        $counts = ['reminder' => 0, 'missed' => 0];

        Utilisateur::whereHas('profilSante')->get()->each(function (Utilisateur $user) use ($mode, $date, &$counts) {
            $medicaments = collect($this->serviceDonneesSante->resoudreMedicamentsTraitement($user->id));

            if ($medicaments->isEmpty()) return;

            $stats = $this->buildStats($user->id, $date, $medicaments);

            $this->maybeNotify($user, 'reminder', $mode, $date, $stats, $counts);
            $this->maybeNotify($user, 'missed',   $mode, $date, $stats, $counts);
        });

        $this->info("Notifications rappel envoyees: {$counts['reminder']}");
        $this->info("Notifications oubli envoyees: {$counts['missed']}");

        return self::SUCCESS;
    }

    private function maybeNotify(Utilisateur $user, string $type, string $mode, Carbon $date, array $stats, array &$counts): void
    {
        $guard = $type === 'reminder' ? $stats['expected_total'] > 0 : $stats['missing_total'] > 0;

        if (! in_array($mode, ['all', $type], true) || ! $guard || $this->alreadyNotified($user, $type, $date)) {
            return;
        }

        $user->notify(new TraitementJournalierNotification(
            typeNotification: $type,
            dateCible:        $date,
            elements:         $stats['items'],
            totalPrevu:       $stats['expected_total'],
            totalPris:        $stats['taken_total'],
            totalManquant:    $stats['missing_total'],
        ));

        $counts[$type]++;
    }

    private function resolveDate(): Carbon
    {
        $raw = $this->option('date');
        return is_string($raw) && trim($raw) !== '' ? Carbon::parse($raw)->startOfDay() : Carbon::today();
    }

    private function buildStats(int $userId, Carbon $date, Collection $medicaments): array
    {
        $checks = HealthTreatmentCheck::query()
            ->where('id_utilisateur', $userId)
            ->whereDate('check_date', $date->toDateString())
            ->get();

        $items = $medicaments->map(function (array $med) use ($checks) {
            $id       = (string) ($med['id'] ?? '');
            $expected = max(1, (int) ($med['doses_per_day'] ?? 1));
            $taken    = min($expected, $checks->filter(
                fn (HealthTreatmentCheck $c) => str_starts_with((string) $c->medication_key, $id.'__dose_') && (bool) $c->taken
            )->count());

            return [
                'medication_id'   => $id,
                'medication_name' => (string) ($med['name'] ?? 'Traitement'),
                'expected'        => $expected,
                'taken'           => $taken,
                'missing'         => $expected - $taken,
            ];
        })->values()->all();

        $expectedTotal = array_sum(array_column($items, 'expected'));
        $takenTotal    = array_sum(array_column($items, 'taken'));

        return [
            'items'          => $items,
            'expected_total' => $expectedTotal,
            'taken_total'    => $takenTotal,
            'missing_total'  => $expectedTotal - $takenTotal,
        ];
    }

    private function alreadyNotified(Utilisateur $user, string $type, Carbon $date): bool
    {
        return $user->notifications()
            ->where('type', TraitementJournalierNotification::class)
            ->where('data->notification_kind', $type)
            ->where('data->target_date', $date->toDateString())
            ->exists();
    }
}