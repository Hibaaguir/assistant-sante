<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $token,
        public string $resetUrl
    ) {}

    // Créer l'enveloppe de l'email
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->email],
            subject: '[HealthFlow] Réinitialisation du mot de passe',
        );
    }

    // Contenu de l'email
    public function content(): Content
    {
        return new Content(
            view: 'mails.reset-password',
            with: [
                'email' => $this->email,
                'token' => $this->token,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }

    // Pièces jointes
    public function attachments(): array
    {
        return [];
    }
}
