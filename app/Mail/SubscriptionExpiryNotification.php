<?php

namespace App\Mail;

use App\Models\Suscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $suscripcion;

    /**
     * Create a new message instance.
     */
    public function __construct(Suscripcion $suscripcion)
    {
        $this->suscripcion = $suscripcion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tipo = $this->suscripcion->tipo == 'paquete'
            ? __('emails.subscription_expiry.package_fallback')
            : __('emails.subscription_expiry.extra_fallback');

        return new Envelope(
            subject: __('emails.subscription_expiry.subject_with_type', ['type' => $tipo]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_expiry',
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
