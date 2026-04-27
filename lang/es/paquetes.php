<?php

return [
    'title' => 'Listado de Paquetes',
    'columns' => [
        'name' => 'NOMBRE',
        'price' => 'PRECIO',
        'items' => 'SERVICIOS',
        'duration' => 'DURACIÓN',
        'status' => 'ESTADO',
        'actions' => 'ACCIONES',
    ],
    'create' => 'Crear Paquete',
    'edit' => 'Editar Paquete',
    'fields' => [
        'name' => 'Nombre del Paquete',
        'price' => 'Precio',
        'description' => 'Descripción',
        'duration' => 'Duración (días)',
        'items' => 'Servicios Incluidos',
        'status' => 'Estado',
    ],
    'messages' => [
        'created_success' => 'Paquete creado exitosamente.',
        'updated_success' => 'Paquete actualizado exitosamente.',
        'deleted_success' => 'Paquete eliminado exitosamente.',
    ],
];
