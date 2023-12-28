<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
     */

    'guards' => [
        'access_token' => [
            'driver' => 'access_token',
            'provider' => 'access_token',
        ],

        'auth_token' => [
            'driver' => 'auth_token',
            'provider' => 'auth_token',
        ],

        'credentials' => [
            'driver' => 'credentials',
            'provider' => 'credentials',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Throttle Key
    |--------------------------------------------------------------------------
    |
    | You may choose to block ip address from failed attempts
    | of a combination of IP and Username
    |
    | Supported: "ip", "username|ip"
    |
     */
    'throttle_key' => 'ip',

    // Set columns for username and other
    'columns' => [
        'username' => 'email',
        'address' => 'email',
    ],

    'user' => [
        'role' => 'customer',
        // Default user status if 0 user should verify email address
        'status' => 0,
    ],
];
