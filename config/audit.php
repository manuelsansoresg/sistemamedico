<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Category Configuration
    |--------------------------------------------------------------------------
    |
    | Each category maps audit log events to a dedicated table. This prevents
    | table saturation by distributing records across domain-specific tables.
    |
    | 'sections' => The normalized section strings that route to this category.
    | 'table'    => The database table name.
    | 'model'    => The Eloquent model class.
    | 'label'    => Locale key under 'audit.categories' for the display label.
    |
    */

    'categories' => [
        'security' => [
            'label' => 'seguridad',
            'table' => 'audit_security_logs',
            'model' => App\Models\AuditSecurityLog::class,
            'sections' => ['seguridad', 'auth', 'security'],
        ],
        'subscriptions' => [
            'label' => 'suscripciones',
            'table' => 'audit_subscription_logs',
            'model' => App\Models\AuditSubscriptionLog::class,
            'sections' => ['suscripciones'],
        ],
        'users' => [
            'label' => 'usuarios',
            'table' => 'audit_user_logs',
            'model' => App\Models\AuditUserLog::class,
            'sections' => ['usuarios', 'pacientes'],
        ],
        'clinical' => [
            'label' => 'clinico',
            'table' => 'audit_clinical_logs',
            'model' => App\Models\AuditClinicalLog::class,
            'sections' => [
                'citas', 'consultas', 'consultorios', 'clinicas',
                'horarios', 'dias_sin_cita', 'recursos', 'recursos_agenda',
                'pendientes',
            ],
        ],
        'medical' => [
            'label' => 'expedientes',
            'table' => 'audit_medical_logs',
            'model' => App\Models\AuditMedicalLog::class,
            'sections' => ['estudios', 'estudio_archivos'],
        ],
        'settings' => [
            'label' => 'administracion',
            'table' => 'audit_settings_logs',
            'model' => App\Models\AuditSettingsLog::class,
            'sections' => [
                'configuraciones', 'catalogos', 'especialidads', 'servicios',
                'paquetes', 'plantillas', 'plantilla_campos', 'ganancias',
                'consulta_valors',
            ],
        ],
    ],

];
