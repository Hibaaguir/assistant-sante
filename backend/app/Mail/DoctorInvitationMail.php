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
        public string $applicationPath = '/doctor-login',
        public ?string $targetSpace = null,
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
        $query = [
            'email' => $this->doctorEmail,
        ];

        if ($this->targetSpace) {
            $query['space'] = $this->targetSpace;
        }

        return new Content(
            view: 'emails.doctor-invitation',
            with: [
                'patientName' => $this->patient->name,
                'applicationUrl' => rtrim((string) env('FRONTEND_URL', 'http://localhost:5174'), '/').$this->applicationPath.'?'.http_build_query($query),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
