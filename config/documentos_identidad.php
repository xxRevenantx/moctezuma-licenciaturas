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
