<?php

return [
    'title' => 'Configuraciones del Sistema',
    'columns' => [
        'key' => 'CLAVE',
        'value' => 'VALOR',
        'module' => 'MÓDULO',
        'actions' => 'ACCIONES',
    ],
    'create' => 'Agregar Configuración',
    'edit' => 'Editar Configuración',
    'fields' => [
        'key' => 'Clave',
        'value' => 'Valor',
        'module' => 'Módulo',
        'description' => 'Descripción',
    ],
    'sections' => [
        'general' => 'General',
        'billing' => 'Facturación',
        'notifications' => 'Notificaciones',
        'appearance' => 'Apariencia',
    ],
    'messages' => [
        'created_success' => 'Configuración creada exitosamente.',
        'updated_success' => 'Configuración actualizada exitosamente.',
        'deleted_success' => 'Configuración eliminada exitosamente.',
    ],
];
