<?php

namespace App\Notifications;

use App\Models\ConsultaCobro;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CitaAfectadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ConsultaCobro $consultaCobro,
        private int $affectedCount
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => __('notifications.types.appointment_reschedule'),
            'consulta_cobro_id' => $this->consultaCobro->id,
            'cita_id' => $this->consultaCobro->cita_id,
            'affected_count' => $this->affectedCount,
            'mensaje' => __('cobros.notifications.affected_appointments', ['count' => $this->affectedCount]),
            'action_url' => route('citas.index'),
            'icon' => 'fa-calendar-alt',
        ];
    }
}
