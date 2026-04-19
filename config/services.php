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

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'mmcloud' => [
        'url' => env('MMCC_API_URL'),
        'secret' => env('MMCC_API_SECRET'),
        'skip_verify' => env('MMCC_SKIP_VERIFY', false),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 30),
    ],

    'vee' => [
        'internal_token' => env('VEE_MCP_INTERNAL_TOKEN'),
    ],

    'n8n' => [
        'url' => env('N8N_URL'),
        'api_key' => env('N8N_API_KEY'),
        'webhook_test_runner' => env('N8N_WEBHOOK_TEST_RUNNER'),
    ],

    'vee_mcp' => [
        'url' => env('VEE_MCP_URL'),
        'token' => env('VEE_MCP_TOKEN'),
    ],

];
