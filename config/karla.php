<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tables configuration
    |--------------------------------------------------------------------------
    |
    | Use this configuration to add user_id to below tables in query process
     */
    'tables'      => [
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
    'databases'   => [
        //'table' => 'database',
    ],

    'connections' => [
        //'table' => 'connection',
    ],
];
