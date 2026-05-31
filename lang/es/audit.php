<?php

return [
    'title' => 'Registro de Auditoría',
    'category' => 'CATEGORÍA',
    'view_details' => 'Ver detalles',
    'no_records' => 'No hay registros de auditoría para los filtros seleccionados.',
    'columns' => [
        'user' => 'USUARIO',
        'action' => 'ACCIÓN',
        'module' => 'MÓDULO',
        'description' => 'DESCRIPCIÓN',
        'ip' => 'DIRECCIÓN IP',
        'date' => 'FECHA',
        'actions' => 'ACCIONES',
    ],
    'categories' => [
        'seguridad' => 'Seguridad',
        'suscripciones' => 'Suscripciones',
        'usuarios' => 'Usuarios',
        'clinico' => 'Clínico',
        'expedientes' => 'Expedientes',
        'administracion' => 'Administración',
        'inteligencia_artificial' => 'Inteligencia Artificial',
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
