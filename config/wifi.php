<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WiFi Tocantins Configuration
    |--------------------------------------------------------------------------
    */

    'company' => [
        'name' => env('COMPANY_NAME', 'WiFi Tocantins Express'),
        'contact' => env('COMPANY_CONTACT', '(63) 99999-9999'),
        'email' => env('COMPANY_EMAIL', 'suporte@wifitocantins.com.br'),
    ],

    'pricing' => [
        'default_price' => env('WIFI_DEFAULT_PRICE', 5.00),
        'session_duration_hours' => env('WIFI_SESSION_DURATION', 24),
        'currency' => 'BRL',
    ],

    'mikrotik' => [
        'host' => env('MIKROTIK_HOST', '192.168.1.1'),
        'username' => env('MIKROTIK_USERNAME', 'admin'),
        'password' => env('MIKROTIK_PASSWORD', ''),
        'port' => env('MIKROTIK_PORT', 8728),
        'api_enabled' => env('MIKROTIK_API_ENABLED', false),
        'hotspot_server' => env('MIKROTIK_HOTSPOT_SERVER', 'hotspot1'),
    ],

    'payment_gateways' => [
        'pix' => [
            'enabled' => env('PIX_ENABLED', true),
            'gateway' => env('PIX_GATEWAY', 'mercadopago'), // mercadopago, pagseguro, banco_brasil
            'key' => env('PIX_API_KEY', ''),
            'secret' => env('PIX_API_SECRET', ''),
        ],
        'card' => [
            'enabled' => env('CARD_ENABLED', true),
            'gateway' => env('CARD_GATEWAY', 'stripe'), // stripe, mercadopago, pagseguro
            'key' => env('CARD_API_KEY', ''),
            'secret' => env('CARD_API_SECRET', ''),
        ],
    ],

    'vouchers' => [
        'enabled' => env('VOUCHERS_ENABLED', true),
        'default_prefix' => env('VOUCHER_DEFAULT_PREFIX', 'WIFI'),
        'code_length' => env('VOUCHER_CODE_LENGTH', 8),
        'default_expiry_days' => env('VOUCHER_DEFAULT_EXPIRY_DAYS', 30),
    ],

    'system' => [
        'auto_disconnect_expired' => env('AUTO_DISCONNECT_EXPIRED', true),
        'session_monitoring_interval' => env('SESSION_MONITORING_INTERVAL', 30), // segundos
        'data_retention_days' => env('DATA_RETENTION_DAYS', 90),
        'admin_notifications' => env('ADMIN_NOTIFICATIONS', true),
    ],

    'ui' => [
        'theme' => [
            'primary_color' => '#FFD700', // Dourado Tocantins
            'secondary_color' => '#228B22', // Verde Tocantins
            'accent_colors' => [
                'light_cream' => '#FFF8DC',
                'dark_green' => '#006400',
                'light_yellow' => '#FFE55C',
                'gray_green' => '#2F4F2F',
            ],
        ],
        'portal' => [
            'show_logo' => env('PORTAL_SHOW_LOGO', true),
            'show_company_info' => env('PORTAL_SHOW_COMPANY_INFO', true),
            'show_speed_info' => env('PORTAL_SHOW_SPEED_INFO', true),
            'default_speed_display' => env('PORTAL_DEFAULT_SPEED', '100+ Mbps'),
        ],
    ],

    'monitoring' => [
        'enabled' => env('MONITORING_ENABLED', true),
        'log_connections' => env('LOG_CONNECTIONS', true),
        'log_payments' => env('LOG_PAYMENTS', true),
        'log_mikrotik_calls' => env('LOG_MIKROTIK_CALLS', true),
        'dashboard_refresh_interval' => env('DASHBOARD_REFRESH_INTERVAL', 30), // segundos
    ],

    'security' => [
        'rate_limiting' => [
            'payment_attempts' => env('RATE_LIMIT_PAYMENTS', '5,1'), // 5 tentativas por minuto
            'api_calls' => env('RATE_LIMIT_API', '60,1'), // 60 calls por minuto
        ],
        'mac_address_validation' => env('VALIDATE_MAC_ADDRESS', true),
        'require_user_agent' => env('REQUIRE_USER_AGENT', true),
    ],

    'features' => [
        'bandwidth_control' => env('FEATURE_BANDWIDTH_CONTROL', false),
        'time_based_pricing' => env('FEATURE_TIME_BASED_PRICING', false),
        'user_profiles' => env('FEATURE_USER_PROFILES', false),
        'referral_system' => env('FEATURE_REFERRAL_SYSTEM', false),
        'loyalty_program' => env('FEATURE_LOYALTY_PROGRAM', false),
    ],
];


