<?php

return [
    'title' => 'Audit Log',
    'columns' => [
        'user' => 'USER',
        'action' => 'ACTION',
        'module' => 'MODULE',
        'description' => 'DESCRIPTION',
        'ip' => 'IP ADDRESS',
        'date' => 'DATE',
        'actions' => 'ACTIONS',
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
