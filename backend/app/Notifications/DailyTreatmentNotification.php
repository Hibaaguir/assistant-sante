<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DailyTreatmentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $notificationType,
        private readonly CarbonInterface $targetDate,
        private readonly array $items,
        private readonly int $expectedTotal,
        private readonly int $takenTotal,
        private readonly int $missingTotal,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isMissed = $this->notificationType === 'missed';

        return [
            'notification_kind' => $this->notificationType,
            'target_date' => $this->targetDate->toDateString(),
            'title' => $isMissed ? 'Forgotten treatments today' : 'Treatment reminders for today',
            'message' => $isMissed 
                ? "You missed {$this->missingTotal} dose(s) out of {$this->expectedTotal} expected."
                : "You have {$this->expectedTotal} dose(s) expected today.",
            'expected_total' => $this->expectedTotal,
            'taken_total' => $this->takenTotal,
            'missing_total' => $this->missingTotal,
            'items' => $this->items,
        ];
    }
}
