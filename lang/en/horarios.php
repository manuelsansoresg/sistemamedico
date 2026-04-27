<?php

return [
    'title' => 'Schedule Management',
    'breadcrumbs' => [
        'index' => 'Schedule Management',
        'manage' => 'Manage',
    ],
    'titles' => [
        'my_schedules' => 'My Schedules',
        'manage_by_doctor' => 'Schedule Management by Doctor',
        'schedule_of' => 'Schedule for :doctor',
    ],
    'descriptions' => [
        'doctor' => 'Select an office to manage your availability.',
        'admin' => 'Select a doctor and office to manage their availability.',
    ],
    'search' => [
        'placeholder_doctor' => 'Search doctor...',
    ],
    'sections' => [
        'assigned_offices' => 'Assigned offices',
        'quick_shortcut' => 'Quick shortcut',
        'copy_from_other_office' => 'Copy schedules from another office:',
        'select_origin_office' => 'Select origin office',
        'overwrite_warning' => 'The current schedule for this office will be overwritten with the selected one.',
    ],
    'empty' => [
        'no_specialty' => 'No specialty',
        'no_assigned_offices' => 'No assigned offices.',
        'no_users_with_offices' => 'There are no users with assigned offices.',
    ],
    'links' => [
        'go_to_user_management' => 'Go to user management',
    ],
    'columns' => [
        'doctor' => 'DOCTOR',
        'day' => 'DAY',
        'start' => 'START',
        'end' => 'END',
        'actions' => 'ACTIONS',
        'status' => 'STATUS',
    ],
    'manage' => 'Manage Schedules',
    'days' => [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ],
    'fields' => [
        'doctor' => 'Doctor',
        'day' => 'Day',
        'start_time' => 'Start Time',
        'end_time' => 'End Time',
        'slot_duration' => 'Slot Duration',
        'break_start' => 'Break Start',
        'break_end' => 'Break End',
        'office' => 'Office',
        'consultation_time' => 'Consultation time',
        'start' => 'Start',
        'end' => 'End',
    ],
    'periods' => [
        'morning' => 'Morning',
        'afternoon' => 'Afternoon',
        'night' => 'Night',
        'morning_article' => 'the morning',
        'afternoon_article' => 'the afternoon',
        'night_article' => 'the night',
        'day_article' => 'the day',
    ],
    'buttons' => [
        'manage' => 'MANAGE',
        'add_slot' => 'Add slot',
        'remove' => 'Remove',
        'save_schedules' => 'Save schedules',
    ],
    'units' => [
        'min' => 'min',
        'hr' => 'hr',
    ],
    'validation' => [
        'start_adjusted_min' => 'Start time adjusted to the minimum allowed for :period (:time)',
        'start_exceeds_max' => 'Start time cannot exceed the limit for :period (:time)',
        'end_adjusted_max' => 'End time adjusted to the maximum allowed for :period (:time)',
        'end_before_min' => 'End time cannot be earlier than the start of :period (:time)',
        'start_before_end' => 'Start time must be earlier than end time',
    ],
    'messages' => [
        'updated_success' => 'Schedules updated successfully.',
    ],
    'errors' => [
        'user_not_assigned_to_office' => 'The user is not assigned to this office.',
        'no_permission_manage_other_doctor' => 'You do not have permission to manage another doctor\'s schedules.',
        'no_permission_manage_in_office' => 'You do not have permission to manage schedules in this office.',
    ],
];
