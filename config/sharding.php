<?php

return [
    'map' => [
        /*
        |--------------------------------------------------------------------------
        | Service Name
        |--------------------------------------------------------------------------
        */
        'default' => [
            /*
            |--------------------------------------------------------------------------
            | Database Connections List
            |--------------------------------------------------------------------------
            | 1. For "ModuleDivision" and "CentralTable" ShardChoosers
            |--------------------------------------------------------------------------
            | If you're using "ModuleDivision" or "CentralTable" as a ShardChooser
            | you should set "connections" array like this:
            | 'connections' => [
            |     'auth-1',
            |     'auth-2',
            |     'auth-3'
            | ],
            | Where each array value is a connection name (from your config/database.php)
            |--------------------------------------------------------------------------
            | 2. For "ServersRange" ShardChooser
            |--------------------------------------------------------------------------
            | If you're using "ServersRange" as a ShardChooser
            | you should set "connections" array like this:
            | 'connections' => [
                  'auth-1' => [1, 10],
                  'auth-2' => [11, 20],
                  'auth-3' => [21, 30]
            | ],
            | Where each array key is a connection name (from your config/database.php),
            | and each value is array of id-ranges.
            |
            */
            'connections' => [
                'auth-1' => [1, 10],
                'auth-2' => [11, 20],
                'auth-3' => [21, 30],
            ],

            /*
            |--------------------------------------------------------------------------
            | Shard Chooser class
            |--------------------------------------------------------------------------
            |
            */
            'shard_chooser' => 'Diviky\Bright\Database\Sharding\ShardChoosers\DatabaseCentralTable',

            /*
            |--------------------------------------------------------------------------
            | Unique id generator class
            |--------------------------------------------------------------------------
            | Generators you can use to generate ids or emulate sequence:
            |--------------------------------------------------------------------------
            | 1. Diviky\Bright\Database\Sharding\IdGenerators\RedisSequence - to store in Redis
            |--------------------------------------------------------------------------
            |
            | You can use your own Id generator (must be an Instance of Diviky\Bright\Database\Sharding\IdGenerators\IdGeneratorInterface)
            |
            */
            'id_generator' => 'Diviky\Bright\Database\Sharding\IdGenerators\RedisSequence',

            /*
            |--------------------------------------------------------------------------
            | Name of sequence table for sequence Id generators (like RedisSequence, MysqlSequence)
            |--------------------------------------------------------------------------
            |
            */
            'sequence_key' => 'sharding:last-id',
            'relation_key' => 'sharding:user',
        ],
    ],
];
