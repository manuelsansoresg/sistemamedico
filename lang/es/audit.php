<?php

return [
    'title' => 'Registro de Auditoría',
    'columns' => [
        'user' => 'USUARIO',
        'action' => 'ACCIÓN',
        'module' => 'MÓDULO',
        'description' => 'DESCRIPCIÓN',
        'ip' => 'DIRECCIÓN IP',
        'date' => 'FECHA',
        'actions' => 'ACCIONES',
    ],
    'actions_list' => [
        'created' => 'Creación',
        'updated' => 'Actualización',
        'deleted' => 'Eliminación',
        'viewed' => 'Visualización',
        'login' => 'Inicio de Sesión',
        'logout' => 'Cierre de Sesión',
    ],
];
