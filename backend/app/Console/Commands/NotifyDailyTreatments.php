<?php

// Déclaration de l'espace de noms pour que Laravel trouve cette commande
namespace App\Console\Commands;

// Importation des modèles et services nécessaires à la commande
use App\Models\Notification;
use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\User;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyDailyTreatments extends Command
{
    // Nom de la commande et options disponibles (--mode et --date)
    protected $signature = 'treatments:notify
                            {--mode=all : reminder|missed|all}
                            {--date= : Target date in YYYY-MM-DD format}';

    // Description affichée dans la liste des commandes artisan
    protected $description = 'Send reminder and missed-dose notifications per treatment for today';

    // Injection automatique du service qui récupère les médicaments actifs
    public function __construct(private readonly HealthDataService $healthDataService)
    {
        parent::__construct();
    }

    // Point d'entrée de la commande : s'exécute quand on tape php artisan treatments:notify
    public function handle(): int
    {
        // Récupération et validation de l'option --mode
        $mode = strtolower((string) $this->option('mode'));

        if (!in_array($mode, ['all', 'reminder', 'missed'], true)) {
            $this->error('Invalid --mode. Allowed: all, reminder, missed.');
            return self::FAILURE;
        }

        // Initialisation de la date cible et du compteur de notifications créées
        $date    = $this->resolveDate();
        $dateKey = $date->toDateString();
        $counts  = ['reminder' => 0, 'missed' => 0];

        // Récupération de tous les utilisateurs qui ont un profil de santé
        $users = User::whereHas('healthProfile')->get();

        // Boucle sur chaque utilisateur
        foreach ($users as $user) {

            // Récupération des médicaments actifs aujourd'hui pour cet utilisateur
            $medicines = collect($this->healthDataService->resolveTreatmentMedicines($user->id));

            if ($medicines->isEmpty()) {
                continue;
            }

            // Chargement en une seule requête de toutes les doses cochées par l'utilisateur aujourd'hui
            $checks = TreatmentCheck::where('user_id', $user->id)
                ->whereDate('check_date', $dateKey)
                ->get();

            // Pour chaque médicament, calcul des stats et tentative de création de notification
            foreach ($medicines as $medicine) {
                $treatment = Treatment::find($medicine['id'] ?? null);

                if (!$treatment) {
                    continue;
                }

                $stats = $this->buildStats($medicine, $checks);

                $this->maybeNotify($treatment->id, 'reminder', $mode, $date, $stats, $counts);
                $this->maybeNotify($treatment->id, 'missed', $mode, $date, $stats, $counts);
            }
        }

        // Affichage du résultat dans le terminal
        $this->info("Reminder notifications sent: {$counts['reminder']}");
        $this->info("Missed-dose notifications sent: {$counts['missed']}");

        return self::SUCCESS;
    }

    // Crée une notification en base seulement si les conditions sont remplies et qu'elle n'existe pas déjà
    private function maybeNotify(int $traitementId, string $type, string $mode, Carbon $date, array $stats, array &$counts): void
    {
        // Vérification que le mode correspond au type de notification
        if (!in_array($mode, ['all', $type], true)) {
            return;
        }

        // Vérification : au moins 1 dose prévue pour un rappel
        if ($type === 'reminder' && $stats['expected_total'] === 0) {
            return;
        }

        // Vérification : au moins 1 dose oubliée pour un manqué
        if ($type === 'missed' && $stats['missing_total'] === 0) {
            return;
        }

        // Vérification qu'une notification identique n'existe pas déjà aujourd'hui
        if ($this->alreadyNotified($traitementId, $type, $date)) {
            return;
        }

        // Construction du message selon le type de notification
        $medicineName = $stats['treatment_name'] ?? 'Traitement';

        if ($type === 'missed') {
            $message = "Vous avez oublié de prendre {$medicineName} aujourd'hui.";
        } else {
            $message = "N'oubliez pas de prendre {$medicineName} aujourd'hui.";
        }

        // Sauvegarde de la notification en base de données
        Notification::create([
            'treatment_id' => $traitementId,
            'kind'         => $type,
            'target_date'  => $date->toDateString(),
            'message'      => $message,
        ]);

        // Incrémentation du compteur pour l'affichage final
        $counts[$type]++;
    }

    // Calcule le nombre de doses prévues, prises et manquées pour un médicament
    private function buildStats(array $medicine, $checks): array
    {
        // Extraction des informations du médicament
        $medicineId   = (string) ($medicine['id'] ?? '');
        $medicineName = (string) ($medicine['name'] ?? 'Traitement');
        $expected     = (int) ($medicine['doses_per_day'] ?? 1);

        if ($expected < 1) {
            $expected = 1;
        }

        // Comptage des doses effectivement prises pour ce médicament
        $taken = 0;
        foreach ($checks as $check) {
            $belongsToThisMedicine = str_starts_with((string) $check->medication_key, $medicineId . '__dose_');
            if ($belongsToThisMedicine && $check->taken) {
                $taken++;
            }
        }

        // On ne peut pas prendre plus de doses que prévu
        if ($taken > $expected) {
            $taken = $expected;
        }

        // Calcul des doses manquées (ne peut pas être négatif)
        $missing = $expected - $taken;
        if ($missing < 0) {
            $missing = 0;
        }

        return [
            'treatment_name' => $medicineName,
            'expected_total' => $expected,
            'missing_total'  => $missing,
        ];
    }

    // Retourne la date cible : celle passée via --date ou aujourd'hui par défaut
    private function resolveDate(): Carbon
    {
        $raw = $this->option('date');

        if (is_string($raw) && trim($raw) !== '') {
            return Carbon::parse($raw)->startOfDay();
        }

        return Carbon::today();
    }

    // Vérifie qu'une notification du même type n'existe pas déjà pour ce traitement aujourd'hui
    private function alreadyNotified(int $traitementId, string $type, Carbon $date): bool
    {
        return Notification::query()
            ->where('treatment_id', $traitementId)
            ->where('kind', $type)
            ->where('target_date', $date->toDateString())
            ->exists();
    }
}
