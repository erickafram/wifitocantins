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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ntfy.sh - Push Notifications Gratuitas
    |--------------------------------------------------------------------------
    | Serviço gratuito de push notifications para celular.
    | Instale o app "ntfy" no celular e inscreva-se no tópico configurado.
    | https://ntfy.sh
    */
    'ntfy' => [
        'enabled' => env('NTFY_ENABLED', false),
        'server_url' => env('NTFY_SERVER_URL', 'https://ntfy.sh'),
        'topic' => env('NTFY_TOPIC', ''),
    ],

];
