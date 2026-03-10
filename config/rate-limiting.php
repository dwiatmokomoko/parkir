<?php

/**
 * Rate Limiting Configuration
 * 
 * This file defines rate limiting rules for API endpoints to prevent abuse
 * and ensure fair usage of system resources.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Rules
    |--------------------------------------------------------------------------
    |
    | Define rate limiting rules for different endpoint categories.
    | Format: 'requests,minutes' or 'requests,seconds'
    |
    */

    'rules' => [
        // Authentication endpoints: 5 login attempts per 15 minutes per IP
        'auth' => '5,15',

        // QR code generation: 10 per minute per attendant
        'qr_generation' => '10,1',

        // Public endpoints: 60 requests per minute per IP
        'public' => '60,1',

        // API endpoints: 100 requests per minute per IP
        'api' => '100,1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Keys
    |--------------------------------------------------------------------------
    |
    | Define how rate limits are keyed (by IP, user ID, etc.)
    |
    */

    'keys' => [
        // Authentication: limit by IP address
        'auth' => 'ip',

        // QR generation: limit by attendant ID
        'qr_generation' => 'attendant_id',

        // Public endpoints: limit by IP address
        'public' => 'ip',

        // API endpoints: limit by IP address
        'api' => 'ip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Response
    |--------------------------------------------------------------------------
    |
    | Configure the response when rate limit is exceeded
    |
    */

    'response' => [
        'status' => 429,
        'message' => 'Terlalu banyak permintaan. Silakan coba beberapa saat lagi.',
        'retry_after' => 60, // seconds
    ],
];
