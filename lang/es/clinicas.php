<?php

return [
    'title' => 'Listado de Clínicas',
    'breadcrumbs' => [
        'index' => 'Clínicas',
        'create' => 'Crear',
        'edit' => 'Editar',
    ],
    'columns' => [
        'name' => 'NOMBRE',
        'address' => 'DIRECCIÓN',
        'phone' => 'TELÉFONO',
        'status' => 'ESTADO',
        'actions' => 'ACCIONES',
        'consultorios' => 'CONSULTORIOS',
        'doctors' => 'MÉDICOS',
    ],
    'create' => 'Crear Clínica',
    'edit' => 'Editar Clínica',
    'fields' => [
        'name' => 'Nombre de la Clínica',
        'address' => 'Dirección',
        'phone' => 'Teléfono',
        'status' => 'Estado',
        'description' => 'Descripción',
        'location_details_optional' => 'Detalles de Ubicación (Opcional)',
        'logo' => 'Logo',
        'city' => 'Ciudad',
        'state' => 'Estado/Provincia',
        'postal_code' => 'Código Postal',
        'country' => 'País',
    ],
    'placeholders' => [
        'address_search' => 'Escribe la dirección para buscar...',
    ],
];
