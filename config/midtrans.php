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

];
