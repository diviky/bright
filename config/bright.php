<?php

return [
    'timestamps'       => false,
    'db_cache'         => false,

    /*
    |--------------------------------------------------------------------------
    | Tables configuration
    |--------------------------------------------------------------------------
    |
    | Use this configuration to add user_id to below tables in query process
     */
    'tables'        => [
        'default' => [
            'table' => ['user_id'],
        ],
        'select'  => [],
        'insert'  => [],
        'delete'  => [],
        'update'  => [],
        'ignore'  => [
            'migrations',
            'jobs',
            'failed_jobs',
        ],
    ],
    // append database based on table name
    'databases'     => [
        //'table' => 'database',
    ],

    'connections'   => [
        //'table' => 'connection',
    ],

    'notifications' => [
        'mail',
    ],

    'events'        => [
        'Illuminate\Mail\Events\MessageSending' => [
            \Diviky\Bright\Listeners\EmailLogger::class,
        ],
        'Illuminate\Auth\Events\PasswordReset'  => [
            \Diviky\Bright\Listeners\PasswordReset::class,
        ],
        'Illuminate\Auth\Events\Login'          => [
            \Diviky\Bright\Listeners\SuccessLogin::class,
        ],
    ],

    'middlewares'   => [
        'permission'       => \Diviky\Bright\Http\Controllers\Auth\Middleware\PermissionMiddleware::class,
        'role'             => \Diviky\Bright\Http\Controllers\Auth\Middleware\RoleMiddleware::class,
        'roleorpermission' => \Diviky\Bright\Http\Controllers\Auth\Middleware\RoleOrPermissionMiddleware::class,
        'authorize'        => \Diviky\Bright\Http\Controllers\Auth\Middleware\AuthorizeMiddleware::class,
        'auth.verified'    => \Diviky\Bright\Http\Controllers\Auth\Middleware\IsUserActivated::class,
        'accept'           => \Diviky\Bright\Http\Middleware\Accept::class,
        'api.response'     => \Diviky\Bright\Http\Middleware\Api::class,
        'ajax'             => \Diviky\Bright\Http\Middleware\Ajax::class,
        'theme'            => \Diviky\Bright\Http\Middleware\ThemeMiddleware::class,
        'branding'         => \Diviky\Bright\Http\Middleware\Branding::class,
    ],

    'models'        => [
        'user' => \App\Models\User::class,
    ],

    'table'         => [
        'users'            => 'users',
        'email_logs'       => 'email_logs',
        'password_history' => 'auth_password_history',
        'activations'      => 'auth_activations',
        'permissions'      => 'auth_permissions',
        'role_permissions' => 'auth_role_permissions',
        'roles'            => 'auth_roles',
        'user_roles'       => 'auth_user_roles',
        'user_users'       => 'auth_user_users',
        'user_permissions' => 'auth_user_permissions',
        'tokens'           => 'auth_tokens',
        'user_domains'     => 'auth_user_domains',
    ],
];
