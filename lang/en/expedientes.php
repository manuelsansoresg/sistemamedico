<?php

return [
    'title' => 'Records List',
    'patient' => [
        'title' => 'My Records',
        'breadcrumbs' => [
            'my_records' => 'My Records',
            'patient_history' => 'Patient History',
        ],
        'description' => 'Full history of the patient’s consultations and studies.',
        'filters' => [
            'clinic' => 'Clinic',
            'office' => 'Office',
            'from' => 'From',
            'to' => 'To',
            'all_feminine' => 'All',
            'all_masculine' => 'All',
        ],
        'table' => [
            'headers' => [
                'date' => 'DATE',
                'clinic' => 'CLINIC',
                'office' => 'OFFICE',
                'doctor' => 'DOCTOR',
                'detail' => 'DETAIL',
                'actions' => 'ACTIONS',
            ],
            'detail' => [
                'reason_label' => 'Reason:',
                'template_label' => 'Template:',
                'studies_label' => 'Studies:',
                'no_reason' => 'No reason recorded',
                'no_template' => 'No template',
                'view_consultation' => 'View Consultation',
            ],
        ],
        'messages' => [
            'no_records_for_filters' => 'There are no records with the selected filters.',
            'no_consultations_for_filters' => 'There are no consultations for this patient with the selected filters.',
            'no_records_yet' => 'You do not have any records yet.',
            'select_to_download_zip' => 'Select one or more consultations to download your record as a ZIP.',
        ],
    ],
    'columns' => [
        'patient' => 'PATIENT',
        'creation_date' => 'CREATION DATE',
        'type' => 'TYPE',
        'last_update' => 'LAST UPDATE',
        'actions' => 'ACTIONS',
        'doctor' => 'DOCTOR',
        'diagnosis' => 'DIAGNOSIS',
    ],
    'view' => 'View Record',
    'download' => 'Download Record',
    'download_all' => 'Download All',
    'download_bulk' => 'Bulk Download',
    'sections' => [
        'personal_info' => 'Personal Information',
        'medical_history' => 'Medical History',
        'consultations' => 'Consultations',
        'studies' => 'Studies',
        'prescriptions' => 'Prescriptions',
    ],
    'empty' => 'No consultations recorded for this patient.',
    'errors' => [
        'patient_subscription_expired' => 'Your patient subscription has expired. Ask your doctor to renew it to download your record.',
        'no_records_for_filters' => 'There are no records to download with the selected filters.',
        'no_records_to_download' => 'There are no records to download.',
        'no_download_permission' => 'You do not have permission to download records.',
        'zip_create_failed' => 'Could not create the ZIP file.',
    ],
];
