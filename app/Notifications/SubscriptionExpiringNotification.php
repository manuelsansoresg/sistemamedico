<?php

namespace App\Notifications;

use App\Mail\SubscriptionExpiryNotification as SubscriptionExpiryMailable;
use App\Models\Suscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    public $suscripcion;

    /**
     * Create a new notification instance.
     */
    public function __construct(Suscripcion $suscripcion)
    {
        $this->suscripcion = $suscripcion;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new SubscriptionExpiryMailable($this->suscripcion))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $nombre = $this->suscripcion->tipo == 'paquete'
            ? ($this->suscripcion->paquete->nombre ?? __('emails.subscription_expiry.package_fallback'))
            : ($this->suscripcion->catalogo->nombre ?? __('emails.subscription_expiry.extra_fallback'));

        return [
            'suscripcion_id' => $this->suscripcion->id,
            'tipo' => $this->suscripcion->tipo,
            'nombre' => $nombre,
            'fecha_fin' => $this->suscripcion->fecha_fin,
            'mensaje' => __('subscriptions.notifications.expiring_message', [
                'name' => $nombre,
                'date' => \Carbon\Carbon::parse($this->suscripcion->fecha_fin)->format('d/m/Y'),
            ]),
        ];
    }
}
