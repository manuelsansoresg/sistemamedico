<?php

return [
    'title' => 'Audit Log',
    'category' => 'CATEGORY',
    'view_details' => 'View details',
    'no_records' => 'No audit records found for the selected filters.',
    'columns' => [
        'user' => 'USER',
        'action' => 'ACTION',
        'module' => 'MODULE',
        'description' => 'DESCRIPTION',
        'ip' => 'IP ADDRESS',
        'date' => 'DATE',
        'actions' => 'ACTIONS',
    ],
    'categories' => [
        'seguridad' => 'Security',
        'suscripciones' => 'Subscriptions',
        'usuarios' => 'Users',
        'clinico' => 'Clinical',
        'expedientes' => 'Medical Records',
        'administracion' => 'Administration',
    ],
    'actions_list' => [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'viewed' => 'Viewed',
        'login' => 'Login',
        'logout' => 'Logout',
    ],
];
