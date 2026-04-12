<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

// Notification quotidienne pour rappeler ou signaler les traitements manques
class DailyTreatmentNotification extends Notification
{
    use Queueable;

    // Constructeur: initialiser les donnees de la notification
    public function __construct(
        private readonly string $notificationType,
        private readonly CarbonInterface $targetDate,
        private readonly array $items,
        private readonly int $expectedTotal,
        private readonly int $takenTotal,
        private readonly int $missingTotal,
    ) {}

    // Specifier les canaux de notification (database)
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Convertir la notification en donnees pour la base de donnees
    public function toArray(object $notifiable): array
    {
        $isMissed = $this->notificationType === 'missed';

        return [
            'notification_kind' => $this->notificationType,
            'target_date' => $this->targetDate->toDateString(),
            'title' => $isMissed ? 'Traitements oublies aujourd\'hui' : 'Rappels de traitement pour aujourd\'hui',
            'message' => $isMissed 
                ? "Vous avez manque {$this->missingTotal} dose(s) sur {$this->expectedTotal} attendues."
                : "Vous avez {$this->expectedTotal} dose(s) prevues aujourd\'hui.",
            'expected_total' => $this->expectedTotal,
            'taken_total' => $this->takenTotal,
            'missing_total' => $this->missingTotal,
            'items' => $this->items,
        ];
    }
}
