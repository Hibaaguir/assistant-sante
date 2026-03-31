<?php

namespace App\Mail;

use App\Models\Utilisateur;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Utilisateur $patient,
        public string $doctorEmail,
        public string $applicationPath = '/doctor-register',
        public ?string $role = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation medecin - {$this->patient->nom}",
        );
    }

    public function content(): Content
    {
        $url = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/') . $this->applicationPath;
        
        $url .= '?email=' . urlencode($this->doctorEmail);

        return new Content(
            view: 'emails.doctor-invitation',
            with: [
                'patientName' => $this->patient->nom,
                'applicationUrl' => $url,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
