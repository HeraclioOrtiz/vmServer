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
	   'socios' => [
        'base'     => env('SOCIOS_API_BASE', 'https://clubvillamitre.com/api_back_socios'),
        'login'    => env('SOCIOS_API_LOGIN', 'surtek'),
        'token'    => env('SOCIOS_API_TOKEN', ''),
        'img_base' => env('SOCIOS_IMG_BASE', 'https://clubvillamitre.com/images/socios'),
        'timeout'  => 15,
        'verify'   => env('SOCIOS_API_VERIFY', true), // podés setear false temporalmente
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
