<?php

return [
    'title' => 'Días Sin Citas',
    'create' => 'Agregar Día Sin Citas',
    'list_title' => 'Listado de Días Sin Citas',
    'fields' => [
        'date' => 'Fecha',
        'reason' => 'Motivo',
        'all_day' => 'Todo el día',
        'start_time' => 'Hora Inicio',
        'end_time' => 'Hora Fin',
        'offices' => 'Consultorios Afectados',
    ],
    'active' => 'Día(s) Sin Citas Activo(s)',
    'table' => [
        'headers' => [
            'reason_id' => 'MOTIVO / ID',
            'dates' => 'FECHAS',
            'schedule' => 'HORARIO',
            'offices' => 'CONSULTORIOS',
            'actions' => 'ACCIONES',
        ],
    ],
    'labels' => [
        'all_day' => 'Todo el día',
        'affects' => 'Afecta:',
    ],
    'confirm' => [
        'delete' => '¿Estás seguro de eliminar este registro?',
    ],
    'empty' => 'No hay días sin citas registrados.',
    'buttons' => [
        'add_day' => 'AGREGAR DÍA',
    ],
    'form' => [
        'title' => 'Registrar Día Sin Citas',
        'reason_placeholder' => 'Ej: Puente laboral, Vacaciones, Mantenimiento',
        'start_date' => 'Fecha Inicio',
        'end_date' => 'Fecha Fin',
        'all_day_help' => 'Marcar si no habrá citas en todo el horario laboral.',
        'apply_to_offices' => 'Aplicar a Consultorios',
    ],
    'messages' => [
        'created_success' => 'Día sin citas registrado exitosamente.',
        'deleted_success' => 'Registro eliminado exitosamente.',
    ],
    'errors' => [
        'assign_offices_forbidden' => 'No tiene permiso para asignar estos consultorios.',
        'delete_forbidden' => 'No tienes permiso para eliminar este registro.',
        'delete_db_failed' => 'No se pudo eliminar el registro en la base de datos (ID: :id, existe=:exists).',
        'delete_failed' => 'Error al eliminar el registro.',
    ],
];
