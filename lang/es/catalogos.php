<?php

return [
    'title' => 'Listado de Catálogos',
    'columns' => [
        'name' => 'NOMBRE',
        'type' => 'TIPO',
        'price' => 'PRECIO',
        'status' => 'ESTADO',
        'actions' => 'ACCIONES',
        'description' => 'DESCRIPCIÓN',
    ],
    'create' => 'Crear Catálogo',
    'edit' => 'Editar Catálogo',
    'fields' => [
        'name' => 'Nombre',
        'type' => 'Tipo',
        'price' => 'Precio',
        'status' => 'Estado',
        'description' => 'Descripción',
        'profit_percentage' => 'Porcentaje Ganancias',
    ],
    'form' => [
        'required_note' => 'Los campos marcados con * son requeridos',
    ],
    'messages' => [
        'created_success' => 'Catálogo creado exitosamente.',
        'updated_success' => 'Catálogo actualizado exitosamente.',
        'deleted_success' => 'Catálogo eliminado exitosamente.',
    ],
];
