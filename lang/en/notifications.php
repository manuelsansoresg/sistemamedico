<?php

return [
    'title' => 'Notifications',
    'subtitle' => 'System alerts, emails, and important pending work in one place.',
    'item_title' => 'System notice',
    'fallback_message' => 'You have a new notification.',
    'empty' => 'You have no notifications yet.',
    'unread' => 'Unread',
    'unread_count' => '{0} No new notifications|{1} 1 unread|[2,*] :count unread',
    'confirm_delete' => 'Delete this notification?',
    'actions' => [
        'open' => 'Open',
        'view_all' => 'View all',
        'mark_read' => 'Read',
        'mark_all_read' => 'Mark all read',
        'delete' => 'Delete',
    ],
    'messages' => [
        'marked_read' => 'Notification marked as read.',
        'all_marked_read' => 'All notifications were marked as read.',
        'deleted' => 'Notification deleted.',
    ],
    'types' => [
        'subscription_expiring' => 'Subscription expiring',
        'appointment_reschedule' => 'Appointments to reschedule',
        'card_welcome' => 'Purchase confirmed',
        'transfer_instructions' => 'Transfer instructions',
        'email_verification' => 'Email verification',
    ],
    'mail' => [
        'card_welcome' => 'We emailed you the confirmation for your card purchase.',
        'transfer_instructions' => 'We emailed you the instructions to complete your transfer payment.',
        'email_verification' => 'We emailed you the link to verify your email address.',
    ],
];
