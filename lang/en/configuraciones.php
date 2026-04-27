<?php

return [
    'title' => 'System Configurations',
    'columns' => [
        'key' => 'KEY',
        'value' => 'VALUE',
        'module' => 'MODULE',
        'actions' => 'ACTIONS',
    ],
    'create' => 'Add Configuration',
    'edit' => 'Edit Configuration',
    'fields' => [
        'key' => 'Key',
        'value' => 'Value',
        'module' => 'Module',
        'description' => 'Description',
    ],
    'sections' => [
        'general' => 'General',
        'billing' => 'Billing',
        'notifications' => 'Notifications',
        'appearance' => 'Appearance',
    ],
    'messages' => [
        'created_success' => 'Configuration created successfully.',
        'updated_success' => 'Configuration updated successfully.',
        'deleted_success' => 'Configuration deleted successfully.',
    ],
];
