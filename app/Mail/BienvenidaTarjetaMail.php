<?php

namespace App\Mail;

use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaTarjetaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $suscripcion;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Suscripcion $suscripcion, User $user)
    {
        $this->suscripcion = $suscripcion;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.card_welcome.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.suscripciones.bienvenida_tarjeta',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
