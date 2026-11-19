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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    | LLM used to generate the training module (ProcessTraining) and by the tutor.
    | TRAINING_LLM_PROVIDER: "openai" (default, the paper's o3-mini setup) | "anthropic".
    | LLM_TIMEOUT is the per-call HTTP timeout in seconds; the job timeout is derived from it
    | (5 calls + margin), and queue.connections.database.retry_after must stay above that.
    */
    'llm' => [
        'training_provider' => env('TRAINING_LLM_PROVIDER', 'openai'),
        'timeout' => (int) env('LLM_TIMEOUT', 180),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        // Fixed on purpose: Training::setNonCustomizedVersion() was generated with o3-mini.
        'model' => 'o3-mini',
        'reasoning_effort' => 'medium',
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5-5'),
        'effort' => env('ANTHROPIC_EFFORT', 'medium'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 16000),
        'max_retries' => (int) env('ANTHROPIC_MAX_RETRIES', 2),
    ],

];
