<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'curp_api' => [
        // CURP_API_TOKEN es la variable usada por la integración actual.
        // CURP_API_KEY se conserva para instalaciones anteriores.
        'key' => env('CURP_API_TOKEN') ?: env('CURP_API_KEY'),
        'url' => env('CURP_API_URL', 'https://api.valida-curp.com.mx/curp/obtener_datos/'),
        'timeout' => (int) env('CURP_API_TIMEOUT', 15),
        'connect_timeout' => (int) env('CURP_API_CONNECT_TIMEOUT', 5),
    ],

];
