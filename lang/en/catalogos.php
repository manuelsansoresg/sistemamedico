<?php

return [
    'title' => 'Catalogs List',
    'columns' => [
        'name' => 'NAME',
        'type' => 'TYPE',
        'price' => 'PRICE',
        'status' => 'STATUS',
        'actions' => 'ACTIONS',
        'description' => 'DESCRIPTION',
    ],
    'create' => 'Create Catalog',
    'edit' => 'Edit Catalog',
    'fields' => [
        'name' => 'Name',
        'type' => 'Type',
        'price' => 'Price',
        'status' => 'Status',
        'description' => 'Description',
        'profit_percentage' => 'Profit Percentage',
    ],
    'form' => [
        'required_note' => 'Fields marked with * are required',
    ],
    'messages' => [
        'created_success' => 'Catalog created successfully.',
        'updated_success' => 'Catalog updated successfully.',
        'deleted_success' => 'Catalog deleted successfully.',
    ],
    'confirm' => [
        'delete' => 'Are you sure you want to delete this catalog?',
    ],
];
