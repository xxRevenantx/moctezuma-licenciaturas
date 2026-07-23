<?php

return [
    'disk' => env('DOCUMENTOS_IDENTIDAD_DISK', 'local'),
    'max_kb' => (int) env('DOCUMENTOS_IDENTIDAD_MAX_KB', 10240),
    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ],

    'organizer' => [
        'preview_directory' => env('DOCUMENTOS_IDENTIDAD_PREVIEW_DIRECTORY', 'documentos-identidad-temp/previews'),
        'draft_autosave' => true,
    ],

    'export' => [
        // Orden exacto dentro del PDF combinado.
        'types' => ['curp', 'acta_nacimiento', 'certificado_estudios'],
        // Hasta este número de alumnos se procesa en la misma petición.
        'sync_limit' => (int) env('DOCUMENTOS_IDENTIDAD_EXPORT_SYNC_LIMIT', 25),
        'disk' => env('DOCUMENTOS_IDENTIDAD_EXPORT_DISK', 'local'),
        'directory' => env('DOCUMENTOS_IDENTIDAD_EXPORT_DIRECTORY', 'expedientes-identidad-exportados'),
        'retention_hours' => (int) env('DOCUMENTOS_IDENTIDAD_EXPORT_RETENTION_HOURS', 48),
    ],

    'types' => [
        'curp' => [
            'label' => 'CURP',
            'column' => 'CURP_documento',
            'legacy_folder' => 'curp',
            'required' => true,
        ],
        'acta_nacimiento' => [
            'label' => 'Acta de nacimiento',
            'column' => 'acta_nacimiento',
            'legacy_folder' => 'actas',
            'required' => true,
        ],
        'certificado_estudios' => [
            'label' => 'Certificado de estudios',
            'column' => 'certificado_estudios',
            'legacy_folder' => 'certificado_estudios',
            'required' => true,
        ],
        'comprobante_domicilio' => [
            'label' => 'Comprobante de domicilio',
            'column' => 'comprobante_domicilio',
            'legacy_folder' => 'comprobante_domicilio',
            'required' => true,
        ],
        'certificado_medico' => [
            'label' => 'Certificado médico',
            'column' => 'certificado_medico',
            'legacy_folder' => 'certificado_medico',
            'required' => false,
        ],
        'ine' => [
            'label' => 'INE',
            'column' => 'ine',
            'legacy_folder' => 'ine',
            'required' => false,
        ],
    ],
];
