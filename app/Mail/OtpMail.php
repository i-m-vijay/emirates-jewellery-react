<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $otp      Plain 6-digit code
     * @param  string  $purpose  registration | login | password_reset
     * @param  int     $expiryMinutes
     */
    public function __construct(
        public readonly string $otp,
        public readonly string $purpose,
        public readonly int    $expiryMinutes = 10,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'registration'   => 'Your Registration OTP',
            'login'          => 'Your Login OTP',
            'password_reset' => 'Your Password Reset OTP',
        ];

        return new Envelope(
            subject: $subjects[$this->purpose] ?? 'Your OTP Code',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }

    public function attachments(): array
    {
        return [];
    }
}
