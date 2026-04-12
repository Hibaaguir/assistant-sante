<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $patient,
        public string $doctorEmail,
        public string $applicationPath = '/doctor-register',
        public ?string $role = null,
    ) {
    }

    // Créer l'enveloppe de l'email
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation médecin - {$this->patient->name}",
        );
    }

    // Contenu de l'email
    public function content(): Content
    {
        $url = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/') . $this->applicationPath;
        
        $url .= '?email=' . urlencode($this->doctorEmail);

        return new Content(
            view: 'emails.doctor-invitation',
            with: [
                'patientName' => $this->patient->name,
                'applicationUrl' => $url,
            ],
        );
    }

    // Pièces jointes
    public function attachments(): array
    {
        return [];
    }
}
