<?php

return [
    'title' => 'Gestión de Horarios',
    'breadcrumbs' => [
        'index' => 'Gestión de Horarios',
        'manage' => 'Gestionar',
    ],
    'titles' => [
        'my_schedules' => 'Mis Horarios',
        'manage_by_doctor' => 'Gestión de Horarios por Médico',
        'schedule_of' => 'Horario de :doctor',
    ],
    'descriptions' => [
        'doctor' => 'Selecciona un consultorio para gestionar tu disponibilidad.',
        'admin' => 'Selecciona un médico y consultorio para gestionar su disponibilidad.',
    ],
    'search' => [
        'placeholder_doctor' => 'Buscar médico...',
    ],
    'sections' => [
        'assigned_offices' => 'Consultorios asignados',
        'quick_shortcut' => 'Atajo rápido',
        'copy_from_other_office' => 'Copiar horarios desde otro consultorio:',
        'select_origin_office' => 'Selecciona consultorio origen',
        'overwrite_warning' => 'Se sobrescribirá el horario actual de este consultorio con el elegido.',
    ],
    'empty' => [
        'no_specialty' => 'Sin especialidad',
        'no_assigned_offices' => 'No tiene consultorios asignados.',
        'no_users_with_offices' => 'No hay usuarios con consultorios asignados.',
    ],
    'links' => [
        'go_to_user_management' => 'Ir a gestión de usuarios',
    ],
    'columns' => [
        'doctor' => 'MÉDICO',
        'day' => 'DÍA',
        'start' => 'INICIO',
        'end' => 'FIN',
        'actions' => 'ACCIONES',
        'status' => 'ESTADO',
    ],
    'manage' => 'Administrar Horarios',
    'days' => [
        'monday' => 'Lunes',
        'tuesday' => 'Martes',
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ],
    'fields' => [
        'doctor' => 'Médico',
        'day' => 'Día',
        'start_time' => 'Hora Inicio',
        'end_time' => 'Hora Fin',
        'slot_duration' => 'Duración del Turno',
        'break_start' => 'Inicio Descanso',
        'break_end' => 'Fin Descanso',
        'office' => 'Consultorio',
        'consultation_time' => 'Tiempo de consulta',
        'start' => 'Inicio',
        'end' => 'Fin',
    ],
    'periods' => [
        'morning' => 'Mañana',
        'afternoon' => 'Tarde',
        'night' => 'Noche',
        'morning_article' => 'la Mañana',
        'afternoon_article' => 'la Tarde',
        'night_article' => 'la Noche',
        'day_article' => 'el día',
    ],
    'buttons' => [
        'manage' => 'GESTIONAR',
        'add_slot' => 'Agregar turno',
        'remove' => 'Eliminar',
        'save_schedules' => 'Guardar horarios',
    ],
    'units' => [
        'min' => 'min',
        'hr' => 'hr',
    ],
    'validation' => [
        'start_adjusted_min' => 'Horario de inicio ajustado al mínimo de :period (:time)',
        'start_exceeds_max' => 'La hora de inicio no puede exceder el límite de :period (:time)',
        'end_adjusted_max' => 'Horario de fin ajustado al máximo de :period (:time)',
        'end_before_min' => 'La hora de fin no puede ser menor al inicio de :period (:time)',
        'start_before_end' => 'La hora de inicio debe ser anterior a la hora de fin',
    ],
    'messages' => [
        'updated_success' => 'Horarios actualizados exitosamente.',
    ],
    'errors' => [
        'user_not_assigned_to_office' => 'El usuario no está asignado a este consultorio.',
        'no_permission_manage_other_doctor' => 'No tiene permiso para gestionar los horarios de otro médico.',
        'no_permission_manage_in_office' => 'No tiene permiso para gestionar horarios en este consultorio.',
    ],
];
