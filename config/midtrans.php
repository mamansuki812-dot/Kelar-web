<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Kredensial diambil dari .env.
    | Server Key BERSIFAT RAHASIA — tidak boleh pernah dikirim ke frontend.
    | Client Key aman untuk frontend (dipakai Snap.js).
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    /*
    | Base URL API Snap (untuk create transaction token).
    */
    'snap_base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    /*
    | URL Snap.js untuk frontend.
    */
    'snap_js_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];
