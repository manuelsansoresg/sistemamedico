<?php

return [
    'title' => 'Listado de Expedientes',
    'patient' => [
        'title' => 'Mis Expedientes',
        'breadcrumbs' => [
            'my_records' => 'Mis Expedientes',
            'patient_history' => 'Historial del Paciente',
        ],
        'description' => 'Historial completo de consultas y estudios del paciente.',
        'filters' => [
            'clinic' => 'Clínica',
            'office' => 'Consultorio',
            'from' => 'Desde',
            'to' => 'Hasta',
            'all_feminine' => 'Todas',
            'all_masculine' => 'Todos',
        ],
        'table' => [
            'headers' => [
                'date' => 'FECHA',
                'clinic' => 'CLÍNICA',
                'office' => 'CONSULTORIO',
                'doctor' => 'DOCTOR',
                'detail' => 'DETALLE',
                'actions' => 'ACCIONES',
            ],
            'detail' => [
                'reason_label' => 'Motivo:',
                'template_label' => 'Plantilla:',
                'studies_label' => 'Estudios:',
                'no_reason' => 'Sin motivo registrado',
                'no_template' => 'Sin plantilla',
                'view_consultation' => 'Ver Consulta',
            ],
        ],
        'messages' => [
            'no_records_for_filters' => 'No hay expedientes con los filtros seleccionados.',
            'no_consultations_for_filters' => 'No hay consultas registradas para este paciente con los filtros seleccionados.',
            'no_records_yet' => 'Aún no tienes expedientes registrados.',
            'select_to_download_zip' => 'Selecciona una o varias consultas para descargar tu expediente en ZIP.',
        ],
    ],
    'columns' => [
        'patient' => 'PACIENTE',
        'creation_date' => 'FECHA CREACIÓN',
        'type' => 'TIPO',
        'last_update' => 'ÚLTIMA ACTUALIZACIÓN',
        'actions' => 'ACCIONES',
        'doctor' => 'MÉDICO',
        'diagnosis' => 'DIAGNÓSTICO',
    ],
    'view' => 'Ver Expediente',
    'download' => 'Descargar Expediente',
    'download_all' => 'Descargar Todo',
    'download_bulk' => 'Descarga Masiva',
    'sections' => [
        'personal_info' => 'Información Personal',
        'medical_history' => 'Historial Médico',
        'consultations' => 'Consultas',
        'studies' => 'Estudios',
        'prescriptions' => 'Recetas',
    ],
    'empty' => 'No hay consultas registradas para este paciente.',
    'errors' => [
        'patient_subscription_expired' => 'Tu suscripción de paciente ha vencido. Solicita a tu médico la renovación para descargar tu expediente.',
        'no_records_for_filters' => 'No hay expedientes para descargar con los filtros seleccionados.',
        'no_records_to_download' => 'No hay expedientes para descargar.',
        'no_download_permission' => 'No tienes permisos para descargar expedientes.',
        'zip_create_failed' => 'No se pudo crear el archivo ZIP.',
    ],
];
