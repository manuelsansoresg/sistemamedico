<?php

return [
    'title' => 'Days Without Appointments',
    'create' => 'Add Day Without Appointments',
    'list_title' => 'Days Without Appointments List',
    'fields' => [
        'date' => 'Date',
        'reason' => 'Reason',
        'all_day' => 'All day',
        'start_time' => 'Start Time',
        'end_time' => 'End Time',
        'offices' => 'Affected Offices',
    ],
    'active' => 'Day(s) Without Appointments Active',
    'table' => [
        'headers' => [
            'reason_id' => 'REASON / ID',
            'dates' => 'DATES',
            'schedule' => 'SCHEDULE',
            'offices' => 'OFFICES',
            'actions' => 'ACTIONS',
        ],
    ],
    'labels' => [
        'all_day' => 'All day',
    ],
    'confirm' => [
        'delete' => 'Are you sure you want to delete this record?',
    ],
    'empty' => 'No days without appointments found.',
    'buttons' => [
        'add_day' => 'ADD DAY',
    ],
    'form' => [
        'title' => 'Create Day Without Appointments',
        'reason_placeholder' => 'e.g., Holiday bridge, Vacation, Maintenance',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'all_day_help' => 'Check this if there will be no appointments during working hours.',
        'apply_to_offices' => 'Apply to Offices',
    ],
    'messages' => [
        'created_success' => 'Day without appointments created successfully.',
        'deleted_success' => 'Record deleted successfully.',
    ],
    'errors' => [
        'assign_offices_forbidden' => 'You do not have permission to assign these offices.',
        'delete_forbidden' => 'You do not have permission to delete this record.',
        'delete_db_failed' => 'Could not delete the record in the database (ID: :id, exists=:exists).',
        'delete_failed' => 'Error deleting the record.',
    ],
];
