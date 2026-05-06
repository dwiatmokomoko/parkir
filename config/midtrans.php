<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Server key from Midtrans dashboard
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Client key from Midtrans dashboard
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Environment
    |--------------------------------------------------------------------------
    |
    | Set to true for production environment, false for sandbox
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Sanitize Input
    |--------------------------------------------------------------------------
    |
    | Enable input sanitization for security
    |
    */
    'is_sanitized' => true,

    /*
    |--------------------------------------------------------------------------
    | 3D Secure
    |--------------------------------------------------------------------------
    |
    | Enable 3D Secure for card transactions
    |
    */
    'is_3ds' => true,

    /*
    |--------------------------------------------------------------------------
    | Payment Mode
    |--------------------------------------------------------------------------
    |
    | Use "qris" to generate dynamic QRIS codes that can be scanned directly
    | from GoPay, ShopeePay, and QRIS-compatible banking apps. Use "snap" only
    | if you want customers to open the Midtrans Snap checkout page first.
    |
    */
    'payment_mode' => env('MIDTRANS_PAYMENT_MODE', 'qris'),

    /*
    |--------------------------------------------------------------------------
    | Notification URL
    |--------------------------------------------------------------------------
    |
    | Public webhook URL that Midtrans calls when payment status changes.
    | Leave empty to use APP_URL . /api/payments/callback.
    |
    */
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | QRIS Expiry
    |--------------------------------------------------------------------------
    |
    | Dynamic QRIS should be paid before it expires. Sandbox testing is easier
    | with a slightly longer window, while still staying under common QRIS
    | provider limits.
    |
    */
    'qris_expiry_minutes' => (int) env('MIDTRANS_QRIS_EXPIRY_MINUTES', 60),
];
