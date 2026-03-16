<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TraitementJournalierNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $typeNotification,
        private readonly CarbonInterface $dateCible,
        private readonly array $elements,
        private readonly int $totalPrevu,
        private readonly int $totalPris,
        private readonly int $totalManquant,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'notification_kind' => $this->typeNotification,
            'target_date' => $this->dateCible->toDateString(),
            'title' => $this->construireTitre(),
            'message' => $this->construireMessage(),
            'expected_total' => $this->totalPrevu,
            'taken_total' => $this->totalPris,
            'missing_total' => $this->totalManquant,
            'items' => $this->elements,
        ];
    }

    private function construireTitre(): string
    {
        if ($this->typeNotification === 'missed') {
            return 'Traitements oublies aujourd\'hui';
        }

        return 'Rappel traitements du jour';
    }

    private function construireMessage(): string
    {
        if ($this->typeNotification === 'missed') {
            return "Vous avez oublie {$this->totalManquant} prise(s) sur {$this->totalPrevu} prevue(s).";
        }

        return "Vous avez {$this->totalPrevu} prise(s) prevue(s) aujourd'hui.";
    }
}
