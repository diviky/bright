<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tables configuration
    |--------------------------------------------------------------------------
    |
    | Use this configuration to add user_id to below tables in query process
     */
    'tables' => [
        'default' => [
            'smart_links' => ['user_id'],
        ],
        'select' => [],
        'save' => [],
        'delete' => [],
        'update' => [],
    ],

];
