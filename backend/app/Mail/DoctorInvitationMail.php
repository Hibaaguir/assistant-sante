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
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation medecin - {$this->patient->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-invitation',
            with: [
                'patientName' => $this->patient->name,
                'applicationUrl' => rtrim((string) env('FRONTEND_URL', 'http://localhost:5174'), '/').'/doctor-login?email='.urlencode($this->doctorEmail),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
