<?php

return [

    'timestamps'    => false,

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
            \Karla\Listeners\EmailLogger::class,
        ],
        'Illuminate\Auth\Events\PasswordReset'  => [
            \Karla\Listeners\PasswordReset::class,
        ],
        'Illuminate\Auth\Events\Login'          => [
            \Karla\Listeners\SuccessLogin::class,
        ],
    ],

    'middlewares'   => [
        'permission'       => \Karla\Http\Controllers\Auth\Middleware\PermissionMiddleware::class,
        'role'             => \Karla\Http\Controllers\Auth\Middleware\RoleMiddleware::class,
        'roleorpermission' => \Karla\Http\Controllers\Auth\Middleware\RoleOrPermissionMiddleware::class,
        'authorize'        => \Karla\Http\Controllers\Auth\Middleware\AuthorizeMiddleware::class,
        'theme'            => \Karla\Http\Middleware\ThemeMiddleware::class,
        'accept'           => \Karla\Http\Middleware\Accept::class,
        'api.response'     => \Karla\Http\Middleware\Api::class,
        'ajax'             => \Karla\Http\Middleware\Ajax::class,
        'branding'         => \Karla\Http\Middleware\Branding::class,
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
