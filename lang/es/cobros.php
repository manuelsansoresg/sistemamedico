<?php

return [
    'title' => 'Instrucciones de cobro',
    'help' => [
        'doctor_instructions' => 'Seleccioná servicios, ajustá precios si aplica y dejá instrucciones para mostrador.',
    ],
    'sections' => [
        'breakdown' => 'Desglose de cobro',
        'add_article' => 'Agregar medicamento o extra',
        'affected_appointments' => 'Citas afectadas',
    ],
    'articles' => [
        'title' => 'Medicamentos y extras',
        'create' => 'Crear medicamento o extra',
        'edit' => 'Editar medicamento o extra',
    ],
    'fields' => [
        'instruction_status' => 'Decisión del doctor',
        'instructions' => 'Instrucciones de cobro',
        'catalog_price' => 'Precio catálogo',
        'charged_price' => 'Precio cobrado',
        'quantity' => 'Cantidad',
        'subtotal' => 'Subtotal',
        'total' => 'Total',
        'services_subtotal' => 'Servicios',
        'articles_subtotal' => 'Medicamentos y extras',
        'article' => 'Artículo',
        'description' => 'Descripción',
        'unit' => 'Unidad',
        'notes' => 'Notas',
    ],
    'columns' => [
        'select' => 'Seleccionar',
        'type' => 'Tipo',
        'description' => 'Descripción',
        'patient' => 'Paciente',
        'contact' => 'Contacto',
        'original_time' => 'Horario original',
        'status' => 'Estado',
    ],
    'actions' => [
        'send_instructions' => 'Guardar instrucciones',
        'view_breakdown' => 'Ver desglose',
        'confirm_affectations' => 'Confirmar y marcar citas',
        'add_article' => 'Agregar al cobro',
        'manage_articles' => 'Administrar catálogo',
    ],
    'statuses' => [
        'pendiente' => 'Pendiente del doctor',
        'sin_instrucciones' => 'Sin instrucciones',
        'con_instrucciones' => 'Con instrucciones',
        'without_instructions' => 'Finalizar sin instrucciones',
        'with_instructions' => 'Enviar instrucciones',
    ],
    'item_types' => [
        'servicio' => 'Servicio',
        'articulo' => 'Artículo',
    ],
    'affectation_statuses' => [
        'pendiente_aviso' => 'Pendiente de aviso',
        'avisado' => 'Avisado',
        'reagendada' => 'Reagendada',
        'no_localizado' => 'No localizado',
    ],
    'affectations' => [
        'warning_title' => 'Este cobro afecta citas posteriores',
        'warning_summary' => 'La consulta se extenderá hasta :time y afectará :count cita(s).',
    ],
    'placeholders' => [
        'instructions' => 'Ej. agregar medicamento, descuento autorizado, servicio extra realizado...',
        'select_article' => 'Seleccione un artículo',
    ],
    'messages' => [
        'instructions_saved' => 'Instrucciones de cobro guardadas correctamente.',
        'item_added' => 'Artículo agregado al cobro.',
        'item_updated' => 'Item actualizado correctamente.',
        'item_deleted' => 'Item eliminado del cobro.',
        'affectation_updated' => 'Estado de la cita afectada actualizado.',
        'article_created' => 'Artículo creado correctamente.',
        'article_updated' => 'Artículo actualizado correctamente.',
        'article_deleted' => 'Artículo eliminado correctamente.',
        'no_services' => 'No hay servicios configurados.',
        'no_charge_yet' => 'El doctor todavía no guardó instrucciones de cobro para esta cita.',
        'without_instructions' => 'El doctor finalizó sin instrucciones adicionales.',
        'no_affected_appointments' => 'No hay citas afectadas por la duración extra.',
    ],
    'validation' => [
        'instructions_or_services_required' => 'Agregá instrucciones o seleccioná al menos un servicio.',
    ],
    'notifications' => [
        'affected_appointments' => 'Hay :count cita(s) afectadas por una consulta extendida.',
    ],
    'earnings' => [
        'consultation_charge' => 'Cobro de consulta :patient',
    ],
    'ui' => [
        'no_phone' => 'Sin teléfono',
        'no_email' => 'Sin email',
        'dash' => '—',
        'modified_price' => 'Precio modificado',
        'updated_at' => 'Actualizado:',
    ],
];
