<?php

declare(strict_types=1);
use Diviky\Bright\Database\Listeners\QueryQueuedListener;
use Diviky\Bright\Http\Middleware\Accept;
use Diviky\Bright\Http\Middleware\Ajax;
use Diviky\Bright\Http\Middleware\Api;
use Diviky\Bright\Http\Middleware\ApiKey;
use Diviky\Bright\Http\Middleware\AuthorizeMiddleware;
use Diviky\Bright\Http\Middleware\AuthProxy;
use Diviky\Bright\Http\Middleware\IsUserActivated;
use Diviky\Bright\Http\Middleware\PermissionMiddleware;
use Diviky\Bright\Http\Middleware\Pond;
use Diviky\Bright\Http\Middleware\PreflightResponse;
use Diviky\Bright\Http\Middleware\RoleMiddleware;
use Diviky\Bright\Http\Middleware\RoleOrPermissionMiddleware;
use Diviky\Bright\Http\Middleware\XSSProtection;
use Diviky\Bright\Listeners\EmailLogger;
use Diviky\Bright\Models\EmailLogs;
use Diviky\Bright\Models\Meta;
use Diviky\Bright\Models\MetaValues;
use Diviky\Bright\Models\Options;
use Diviky\Bright\Models\User;
use Diviky\Bright\Models\UserUsers;

return [
    // Sharding service config name
    'sharding' => env('BRIGHT_SHARDING', null),

    'timestamps' => env('BRIGHT_TIMESTAMPS', false),

    'db_events' => env('DB_EVENTS', true),

    'db_cache' => env('DB_CACHE', false),

    /*
    |--------------------------------------------------------------------------
    | Bulk load via LOAD DATA LOCAL INFILE
    |--------------------------------------------------------------------------
    |
    | Disable when the app connects through ProxySQL or another proxy that
    | does not support LOAD DATA LOCAL INFILE. Batch inserts will fall back
    | to chunked INSERT statements instead.
    |
    */
    'bulk_load' => env('DB_BULK_LOAD', false),

    'async' => [
        'enable' => env('DB_ASYNC_QUERY', false),
        'all' => env('DB_ASYNC_QUERY_ALL', false),
        'connection' => env('DB_ASYNC_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
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
            'table' => ['user_id'], // [$column => $value]
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
            // 'table' => 'database',
        ],
        'patterns' => [
            // 'table_*' => 'database',
        ],
    ],

    'connections' => [
        'names' => [
            // 'table' => 'connection',
        ],
        'patterns' => [
            // 'table_*' => 'database',
        ],
    ],

    'notifications' => [
        'mail',
    ],

    'events' => [
        'Illuminate\Mail\Events\MessageSending' => [
            EmailLogger::class,
        ],

        'Diviky\Bright\Database\Events\QueryQueued' => [
            QueryQueuedListener::class,
        ],
    ],

    'middlewares' => [
        'permission' => PermissionMiddleware::class,
        'role' => RoleMiddleware::class,
        'roleorpermission' => RoleOrPermissionMiddleware::class,
        'authorize' => AuthorizeMiddleware::class,
        'auth.activated' => IsUserActivated::class,
        'accept' => Accept::class,
        'api.response' => Api::class,
        'ajax' => Ajax::class,
        'preflight' => PreflightResponse::class,
        'xss' => XSSProtection::class,
        'auth.proxy' => AuthProxy::class,
        'apikey' => ApiKey::class,
        'filepond' => Pond::class,
    ],

    'priority_middleware' => [
        ApiKey::class,
        Accept::class,
    ],

    'models' => [
        'user' => User::class,
        'options' => Options::class,
        'meta' => Meta::class,
        'meta_values' => MetaValues::class,
        'email_logs' => EmailLogs::class,
        'user_users' => UserUsers::class,
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
        'branding' => 'auth_socialite_users',
    ],

    'geoip' => [
        'database_path' => env('GEOIP_DB_PATH', storage_path('geoip')),
        'update_url' => sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', env('MAXMIND_LICENSE_KEY', 'J8y0pS9JmwliTB1f')),
    ],

    'money' => [
        'decimals' => env('BRIGHT_MONEY_DECIMALS', 2),
        'currency' => env('BRIGHT_MONEY_CURRENCY', 'USD'),
    ],

    /**
     * Load the migration file automatically
     */
    'migrations' => false,
];
