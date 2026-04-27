<?php

return [
    'title' => 'Listado de Consultorios',
    'breadcrumbs' => [
        'index' => 'Consultorios',
        'create' => 'Crear',
        'edit' => 'Editar',
    ],
    'columns' => [
        'name' => 'NOMBRE',
        'phone' => 'TELÉFONO',
        'status' => 'ESTADO',
        'actions' => 'ACCIONES',
    ],
    'create' => 'Crear Consultorio',
    'edit' => 'Editar Consultorio',
    'fields' => [
        'name' => 'Nombre del Consultorio',
        'phone' => 'Teléfono',
        'address' => 'Dirección',
        'address_optional' => 'Dirección (Opcional)',
        'status' => 'Estado',
        'clinic' => 'Clínica',
        'description' => 'Descripción',
    ],
    'placeholders' => [
        'address_search' => 'Escribe la dirección para buscar...',
    ],
    'messages' => [
        'created_success' => 'Consultorio creado exitosamente.',
        'updated_success' => 'Consultorio actualizado exitosamente.',
        'deleted_success' => 'Consultorio eliminado exitosamente.',
    ],
    'errors' => [
        'subscription_limit_reached' => 'Ha alcanzado el límite de consultorios permitidos por su suscripción.',
    ],
];
