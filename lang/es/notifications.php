<?php

return [
    'title' => 'Notificaciones',
    'subtitle' => 'Centro de avisos importantes del sistema, correos y tareas pendientes.',
    'item_title' => 'Aviso del sistema',
    'fallback_message' => 'Tienes una nueva notificación.',
    'empty' => 'No tienes notificaciones por ahora.',
    'unread' => 'Sin leer',
    'unread_count' => '{0} Sin notificaciones nuevas|{1} 1 sin leer|[2,*] :count sin leer',
    'confirm_delete' => '¿Quieres borrar esta notificación?',
    'actions' => [
        'open' => 'Abrir',
        'view_all' => 'Ver todas',
        'mark_read' => 'Marcar como leída',
        'mark_all_read' => 'Marcar todas como leídas',
        'delete' => 'Borrar',
    ],
    'messages' => [
        'marked_read' => 'Notificación marcada como leída.',
        'all_marked_read' => 'Todas las notificaciones fueron marcadas como leídas.',
        'deleted' => 'Notificación borrada.',
    ],
    'types' => [
        'subscription_expiring' => 'Suscripción por vencer',
        'appointment_reschedule' => 'Citas para reagendar',
        'card_welcome' => 'Compra confirmada',
        'transfer_instructions' => 'Instrucciones de transferencia',
        'email_verification' => 'Verificación de correo',
    ],
    'mail' => [
        'card_welcome' => 'Te enviamos por correo la confirmación de tu compra con tarjeta.',
        'transfer_instructions' => 'Te enviamos por correo las instrucciones para completar tu pago por transferencia.',
        'email_verification' => 'Te enviamos por correo el enlace para verificar tu dirección de email.',
    ],
];
