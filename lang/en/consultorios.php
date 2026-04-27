<?php

return [
    'title' => 'Offices List',
    'breadcrumbs' => [
        'index' => 'Offices',
        'create' => 'Create',
        'edit' => 'Edit',
    ],
    'columns' => [
        'name' => 'NAME',
        'phone' => 'PHONE',
        'status' => 'STATUS',
        'actions' => 'ACTIONS',
    ],
    'create' => 'Create Office',
    'edit' => 'Edit Office',
    'fields' => [
        'name' => 'Office Name',
        'phone' => 'Phone',
        'address' => 'Address',
        'address_optional' => 'Address (Optional)',
        'status' => 'Status',
        'clinic' => 'Clinic',
        'description' => 'Description',
    ],
    'placeholders' => [
        'address_search' => 'Type an address to search...',
    ],
    'messages' => [
        'created_success' => 'Office created successfully.',
        'updated_success' => 'Office updated successfully.',
        'deleted_success' => 'Office deleted successfully.',
    ],
    'errors' => [
        'subscription_limit_reached' => 'You have reached the limit of offices allowed by your subscription.',
    ],
];
