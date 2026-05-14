<?php

return [
    'title' => 'Billing instructions',
    'help' => [
        'doctor_instructions' => 'Select services, adjust prices when needed, and leave front-desk billing instructions.',
    ],
    'sections' => [
        'breakdown' => 'Billing breakdown',
        'add_article' => 'Add medication or extra',
        'affected_appointments' => 'Affected appointments',
    ],
    'articles' => [
        'title' => 'Medications and extras',
        'create' => 'Create medication or extra',
        'edit' => 'Edit medication or extra',
    ],
    'fields' => [
        'instruction_status' => 'Doctor decision',
        'instructions' => 'Billing instructions',
        'catalog_price' => 'Catalog price',
        'charged_price' => 'Charged price',
        'quantity' => 'Quantity',
        'subtotal' => 'Subtotal',
        'total' => 'Total',
        'services_subtotal' => 'Services',
        'articles_subtotal' => 'Medications and extras',
        'article' => 'Item',
        'description' => 'Description',
        'unit' => 'Unit',
        'notes' => 'Notes',
    ],
    'columns' => [
        'select' => 'Select',
        'type' => 'Type',
        'description' => 'Description',
        'patient' => 'Patient',
        'contact' => 'Contact',
        'original_time' => 'Original time',
        'status' => 'Status',
    ],
    'actions' => [
        'send_instructions' => 'Save instructions',
        'view_breakdown' => 'View breakdown',
        'confirm_affectations' => 'Confirm and mark appointments',
        'add_article' => 'Add to charge',
        'manage_articles' => 'Manage catalog',
    ],
    'statuses' => [
        'pendiente' => 'Pending doctor decision',
        'sin_instrucciones' => 'No instructions',
        'con_instrucciones' => 'With instructions',
        'without_instructions' => 'Finish without instructions',
        'with_instructions' => 'Send instructions',
    ],
    'item_types' => [
        'servicio' => 'Service',
        'articulo' => 'Item',
    ],
    'affectation_statuses' => [
        'pendiente_aviso' => 'Pending notice',
        'avisado' => 'Notified',
        'reagendada' => 'Rescheduled',
        'no_localizado' => 'Not reached',
    ],
    'affectations' => [
        'warning_title' => 'This charge affects later appointments',
        'warning_summary' => 'The consultation will extend until :time and affect :count appointment(s).',
    ],
    'placeholders' => [
        'instructions' => 'Example: add medication, authorized discount, extra service performed...',
        'select_article' => 'Select an item',
    ],
    'messages' => [
        'instructions_saved' => 'Billing instructions saved successfully.',
        'item_added' => 'Item added to charge.',
        'item_updated' => 'Item updated successfully.',
        'item_deleted' => 'Item removed from charge.',
        'affectation_updated' => 'Affected appointment status updated.',
        'article_created' => 'Item created successfully.',
        'article_updated' => 'Item updated successfully.',
        'article_deleted' => 'Item deleted successfully.',
        'no_services' => 'No services configured.',
        'no_charge_yet' => 'The doctor has not saved billing instructions for this appointment yet.',
        'without_instructions' => 'The doctor finished without additional instructions.',
        'no_affected_appointments' => 'No appointments are affected by the extra duration.',
    ],
    'validation' => [
        'instructions_or_services_required' => 'Add instructions or select at least one service.',
    ],
    'notifications' => [
        'affected_appointments' => ':count appointment(s) are affected by an extended consultation.',
    ],
    'earnings' => [
        'consultation_charge' => 'Consultation charge :patient',
    ],
    'ui' => [
        'no_phone' => 'No phone',
        'no_email' => 'No email',
        'dash' => '—',
        'modified_price' => 'Modified price',
        'updated_at' => 'Updated:',
    ],
];
