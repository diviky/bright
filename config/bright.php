<?php

declare(strict_types=1);

return [
    // | Sharding service config name
    'sharding' => env('BRIGHT_SHARDING', null),

    'timestamps' => env('BRIGHT_TIMESTAMPS', false),

    'db_cache' => env('DB_CACHE', false),

    'async' => [
        'enable' => env('DB_ASYNC_QUERY', false),
        'connection' => env('DB_ASYNC_CONNECTION', 'sync'),
        'queue' => env('DB_ASYNC_QUEUE', 'sql'),
    ],
    /*
    |--------------------------------------------------------------------------
    | Tables configuration
    |--------------------------------------------------------------------------
    |
    | Use this configuration to add user_id to below tables in query process
     */
    'tables' => [
        'default' => [
            'table' => ['user_id'],
        ],
        'select' => [],
        'insert' => [],
        'delete' => [],
        'update' => [],
        'ignore' => [
            'migrations',
            'jobs',
            'failed_jobs',
        ],
    ],
    // append database based on table name
    'databases' => [
        'names' => [
            //'table' => 'database',
        ],
        'patterns' => [
            //'table_*' => 'database',
        ],
    ],

    'connections' => [
        'names' => [
            //'table' => 'connection',
        ],
        'patterns' => [
            //'table_*' => 'database',
        ],
    ],

    'notifications' => [
        'mail',
    ],

    'events' => [
        'Illuminate\Mail\Events\MessageSending' => [
            \Diviky\Bright\Listeners\EmailLogger::class,
        ],
        'Illuminate\Auth\Events\PasswordReset' => [
            \Diviky\Bright\Listeners\PasswordReset::class,
        ],
        'Illuminate\Auth\Events\Login' => [
            \Diviky\Bright\Listeners\SuccessLogin::class,
        ],
        'Diviky\Bright\Database\Events\QueryQueued' => [
            Diviky\Bright\Database\Listeners\QueryQueuedListener::class,
        ],
    ],

    'middlewares' => [
        'permission' => \Diviky\Bright\Http\Middleware\PermissionMiddleware::class,
        'role' => \Diviky\Bright\Http\Middleware\RoleMiddleware::class,
        'roleorpermission' => \Diviky\Bright\Http\Middleware\RoleOrPermissionMiddleware::class,
        'authorize' => \Diviky\Bright\Http\Middleware\AuthorizeMiddleware::class,
        'auth.verified' => \Diviky\Bright\Http\Middleware\IsUserActivated::class,
        'accept' => \Diviky\Bright\Http\Middleware\Accept::class,
        'api.response' => \Diviky\Bright\Http\Middleware\Api::class,
        'ajax' => \Diviky\Bright\Http\Middleware\Ajax::class,
        'theme' => \Diviky\Bright\Http\Middleware\ThemeMiddleware::class,
        'branding' => \Diviky\Bright\Http\Middleware\Branding::class,
        'preflight' => \Diviky\Bright\Http\Middleware\PreflightResponse::class,
        'xss' => \Diviky\Bright\Http\Middleware\XSSProtection::class,
    ],

    'models' => [
        'user' => \App\Models\User::class,
    ],

    'table' => [
        'users' => 'users',
        'email_logs' => 'email_logs',
        'password_history' => 'auth_password_history',
        'activations' => 'auth_activations',
        'permissions' => 'auth_permissions',
        'role_permissions' => 'auth_role_permissions',
        'roles' => 'auth_roles',
        'user_roles' => 'auth_user_roles',
        'user_users' => 'auth_user_users',
        'user_permissions' => 'auth_user_permissions',
        'tokens' => 'auth_tokens',
        'user_domains' => 'auth_user_domains',
        'socialite_users' => 'auth_socialite_users',
    ],

    'geoip' => [
        'database_path' => env('GEOIP_DB_PATH', storage_path('app/geoip')),
        'update_url' => sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', env('MAXMIND_LICENSE_KEY', 'J8y0pS9JmwliTB1f')),
    ],
];
