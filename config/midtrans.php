<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Used to authenticate server-to-server requests (creating Snap
    | transactions and verifying webhook signatures).
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', env('MIDTRANS_API_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Loaded on the cashier screen (Snap.js) to open the Snap payment popup.
    |
    */

    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Production Mode
    |--------------------------------------------------------------------------
    |
    | When false the sandbox environment is used.
    |
    */

    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Notification URL
    |--------------------------------------------------------------------------
    |
    | Appended to every Snap transaction as X-Append-Notification so Midtrans
    | can reach this application's webhook even without dashboard config.
    |
    */

    'notification_url' => env('MIDTRANS_NOTIFICATION_URL', rtrim(env('APP_URL', 'http://localhost'), '/').'/midtrans/webhook'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Status API Base URL
    |--------------------------------------------------------------------------
    |
    | Optional override for the Midtrans transaction status endpoint used to
    | reconcile pending payments by pulling the status instead of waiting
    | for a webhook. Empty uses the official sandbox/production host.
    |
    */

    'api_base_url' => env('MIDTRANS_API_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Reconciliation
    |--------------------------------------------------------------------------
    |
    | Minimum age (minutes) before the scheduler reconciles a pending Midtrans
    | payment, and the per-payment throttle (seconds) applied when the POS
    | polling endpoint triggers a status pull.
    |
    */

    'reconcile_after_minutes' => env('MIDTRANS_RECONCILE_AFTER_MINUTES', 2),

    'reconcile_throttle_seconds' => env('MIDTRANS_RECONCILE_THROTTLE_SECONDS', 5),

];
