<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthTreatmentCheck;
use App\Models\User;
use App\Notifications\TraitementJournalierNotification;
use App\Services\HealthDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly HealthDataService $serviceDonneesSante)
    {
    }

    // Lister toutes les notifications de l'utilisateur
    public function index(Request $request): JsonResponse
    {
        // Récupérer l'utilisateur et déclencher les notifications de traitement
        $utilisateur = $request->user();
        // Vérifier que l'utilisateur est une instance de User
        if ($utilisateur instanceof User) {
            $this->declencherNotificationsTraitementsSelonHoraire($utilisateur);
        }

        $listeNotifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($notificationItem) {
                return [
                    'id' => $notificationItem->id,
                    'type' => $notificationItem->type,
                    'data' => $notificationItem->data,
                    'read_at' => optional($notificationItem->read_at)?->toISOString(),
                    'created_at' => optional($notificationItem->created_at)?->toISOString(),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Notifications recuperees avec succes.',
            'data' => $listeNotifications,
        ]);
    }

    // Marquer une notification comme lue
    public function markAsRead(Request $request, string $idNotification): JsonResponse
    {
        $notificationTrouvee = $request->user()->notifications()->whereKey($idNotification)->first();
        // Vérifier que la notification existe
        if (! $notificationTrouvee) {
            return response()->json(['message' => 'Notification introuvable.'], 404);
        }

        // Marquer comme lue seulement si pas déjà lue
        if ($notificationTrouvee->read_at === null) {
            $notificationTrouvee->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marquee comme lue.',
        ]);
    }

    // Marquer toutes les notifications comme lues
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
        ]);
    }

    // ─── Helpers privés ───────────────────────────────────────────────────────

    // Déclencher notifications de traitement selon horaire
    private function declencherNotificationsTraitementsSelonHoraire(User $utilisateur): void
    {
        // Obtenir la date et l'heure actuelles
        $maintenant = Carbon::now(config('app.timezone'));
        $dateCible = $maintenant->copy()->startOfDay();
        $heureCourante = (int) $maintenant->format('H');

        $medicaments = collect($this->serviceDonneesSante->resoudreMedicamentsTraitement($utilisateur->id));
        // Arrêter si l'utilisateur n'a pas de médicaments à traiter
        if ($medicaments->isEmpty()) {
            return;
        }

        $statistiques = $this->construireStatistiquesPourDate($utilisateur->id, $dateCible, $medicaments->all());

        $estFenetreRappelMatin = $heureCourante >= 5 && $heureCourante < 12;
        if ($estFenetreRappelMatin
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
        }

        $estFenetreOubliNuit = $heureCourante >= 20;
        if ($estFenetreOubliNuit
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
        }
    }

    // Construire statistiques des traitements pour une date
    private function construireStatistiquesPourDate(int $idUtilisateur, Carbon $dateCible, array $medicaments): array
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

    // Vérifier si notification déjà envoyée
    private function notificationDejaEnvoyee(User $utilisateur, string $typeNotification, Carbon $dateCible): bool
    {
        return $utilisateur->notifications()
            ->where('type', TraitementJournalierNotification::class)
            ->where('data->notification_kind', $typeNotification)
            ->where('data->target_date', $dateCible->toDateString())
            ->exists();
    }
}
