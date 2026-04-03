<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PixCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $amount,
        public string $pixCode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "PIX R$ {$this->amount} - WiFi Tocantins Transporte",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pix-code',
        );
    }
}
