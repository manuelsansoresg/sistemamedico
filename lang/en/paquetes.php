<?php

return [
    'title' => 'Packages List',
    'columns' => [
        'name' => 'NAME',
        'price' => 'PRICE',
        'items' => 'SERVICES',
        'duration' => 'DURATION',
        'status' => 'STATUS',
        'actions' => 'ACTIONS',
    ],
    'create' => 'Create Package',
    'edit' => 'Edit Package',
    'fields' => [
        'name' => 'Package Name',
        'price' => 'Price',
        'description' => 'Description',
        'duration' => 'Duration (days)',
        'items' => 'Included Services',
        'status' => 'Status',
    ],
    'messages' => [
        'created_success' => 'Package created successfully.',
        'updated_success' => 'Package updated successfully.',
        'deleted_success' => 'Package deleted successfully.',
    ],
];
