<?php

namespace App\Console\Commands;

use App\Models\HealthTreatmentCheck;
use App\Models\User;
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
        $modeNotification = strtolower((string) $this->option('mode'));
        if (! in_array($modeNotification, ['all', 'reminder', 'missed'], true)) {
            $this->error('Option --mode invalide. Valeurs autorisees: all, reminder, missed.');
            return self::FAILURE;
        }

        $dateCible = $this->resoudreDateCible();
        $utilisateurs = User::query()->whereHas('profilSante')->get();

        $rappelsEnvoyes = 0;
        $oublisEnvoyes = 0;

        foreach ($utilisateurs as $utilisateur) {
            $medicaments = collect($this->serviceDonneesSante->resoudreMedicamentsTraitement($utilisateur->id));
            if ($medicaments->isEmpty()) {
                continue;
            }

            $statistiques = $this->construireStatistiquesPourDate($utilisateur->id, $dateCible, $medicaments);

            if (in_array($modeNotification, ['all', 'reminder'], true)
                && $statistiques['expected_total'] > 0
                && ! $this->notificationDejaEnvoyee($utilisateur, 'reminder', $dateCible)) {
                $utilisateur->notify(new TraitementJournalierNotification(
                    typeNotification: 'reminder',
                    dateCible: $dateCible,
                    elements: $statistiques['items'],
                    totalPrevu: $statistiques['expected_total'],
                    totalPris: $statistiques['taken_total'],
                    totalManquant: $statistiques['missing_total'],
                ));
                $rappelsEnvoyes++;
            }

            if (in_array($modeNotification, ['all', 'missed'], true)
                && $statistiques['missing_total'] > 0
                && ! $this->notificationDejaEnvoyee($utilisateur, 'missed', $dateCible)) {
                $utilisateur->notify(new TraitementJournalierNotification(
                    typeNotification: 'missed',
                    dateCible: $dateCible,
                    elements: $statistiques['items'],
                    totalPrevu: $statistiques['expected_total'],
                    totalPris: $statistiques['taken_total'],
                    totalManquant: $statistiques['missing_total'],
                ));
                $oublisEnvoyes++;
            }
        }

        $this->info("Notifications rappel envoyees: {$rappelsEnvoyes}");
        $this->info("Notifications oubli envoyees: {$oublisEnvoyes}");

        return self::SUCCESS;
    }

    private function resoudreDateCible(): Carbon
    {
        $dateBrute = $this->option('date');
        if (is_string($dateBrute) && trim($dateBrute) !== '') {
            return Carbon::parse($dateBrute)->startOfDay();
        }

        return Carbon::today();
    }

    private function construireStatistiquesPourDate(int $idUtilisateur, Carbon $dateCible, Collection $medicaments): array
    {
        $cleDate = $dateCible->toDateString();
        $controles = HealthTreatmentCheck::query()
            ->where('user_id', $idUtilisateur)
            ->whereDate('check_date', $cleDate)
            ->get();

        $elements = [];
        $totalPrevu = 0;
        $totalPris = 0;

        foreach ($medicaments as $medicament) {
            $idMedicament = (string) ($medicament['id'] ?? '');
            $nomMedicament = (string) ($medicament['name'] ?? 'Traitement');
            $prisesPrevues = max(1, (int) ($medicament['doses_per_day'] ?? 1));

            $prisesFaitesPourMedicament = $controles
                ->filter(function (HealthTreatmentCheck $controle) use ($idMedicament) {
                    return str_starts_with((string) $controle->medication_key, $idMedicament.'__dose_') && (bool) $controle->taken;
                })
                ->count();

            $prisesFaitesPourMedicament = min($prisesFaitesPourMedicament, $prisesPrevues);
            $prisesManquantesPourMedicament = max(0, $prisesPrevues - $prisesFaitesPourMedicament);

            $elements[] = [
                'medication_id' => $idMedicament,
                'medication_name' => $nomMedicament,
                'expected' => $prisesPrevues,
                'taken' => $prisesFaitesPourMedicament,
                'missing' => $prisesManquantesPourMedicament,
            ];

            $totalPrevu += $prisesPrevues;
            $totalPris += $prisesFaitesPourMedicament;
        }

        return [
            'items' => $elements,
            'expected_total' => $totalPrevu,
            'taken_total' => $totalPris,
            'missing_total' => max(0, $totalPrevu - $totalPris),
        ];
    }

    private function notificationDejaEnvoyee(User $utilisateur, string $typeNotification, Carbon $dateCible): bool
    {
        return $utilisateur->notifications()
            ->where('type', TraitementJournalierNotification::class)
            ->where('data->notification_kind', $typeNotification)
            ->where('data->target_date', $dateCible->toDateString())
            ->exists();
    }
}
