<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WiFi Portal Configuration
    |--------------------------------------------------------------------------
    */

    'default_redirect_url' => env('WIFI_DEFAULT_REDIRECT_URL', 'https://www.google.com'),
    
    /*
    |--------------------------------------------------------------------------
    | MikroTik Configuration
    |--------------------------------------------------------------------------
    */
    
    'mikrotik' => [
        'host' => env('MIKROTIK_HOST', '192.168.88.1'),
        'username' => env('MIKROTIK_USERNAME', 'admin'),
        'password' => env('MIKROTIK_PASSWORD', ''),
        'port' => env('MIKROTIK_PORT', 8728),
        'timeout' => env('MIKROTIK_TIMEOUT', 10),
        'enabled' => env('MIKROTIK_ENABLED', true),
        'sync_token' => env('MIKROTIK_SYNC_TOKEN', 'mikrotik-sync-2024'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Configuration
    |--------------------------------------------------------------------------
    */
    
    'pricing' => [
        'default_price' => env('WIFI_DEFAULT_PRICE', 5.99),
        'session_duration_hours' => env('WIFI_SESSION_DURATION_HOURS', 24),
        'trial_duration_minutes' => env('WIFI_TRIAL_DURATION_MINUTES', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */
    
    'payment' => [
        'default_gateway' => env('PIX_GATEWAY', 'woovi'),
        'enabled_gateways' => [
            'woovi' => env('PIX_ENABLED', true),
            'santander' => env('SANTANDER_PIX_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Instagram Free Access Configuration
    |--------------------------------------------------------------------------
    */
    
    'instagram' => [
        'enabled' => env('INSTAGRAM_FREE_ENABLED', true),
        'required_time_seconds' => env('INSTAGRAM_REQUIRED_TIME', 120),
        'free_duration_minutes' => env('INSTAGRAM_FREE_DURATION', 30),
        'questions' => [
            'Qual a cor do ônibus?' => 'azul',
            'Qual o nome da empresa?' => 'tocantins',
            'Quantas rodas tem o ônibus?' => '6',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */
    
    'ui' => [
        'brand_name' => env('WIFI_BRAND_NAME', 'WiFi Tocantins'),
        'brand_color' => env('WIFI_BRAND_COLOR', '#007bff'),
        'logo_url' => env('WIFI_LOGO_URL', '/images/logo.png'),
        'welcome_message' => env('WIFI_WELCOME_MESSAGE', 'Bem-vindo ao WiFi Tocantins Express!'),
    ]
];


