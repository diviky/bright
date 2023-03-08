<?php

declare(strict_types=1);

return [
    'name' => env('BRIGHT_THEME_NAME', 'tabler'),
    'device' => env('BRIGHT_THEME_DEVICE', 'computer'),

    'paths' => [
        'tabler' => resource_path('themes/tabler'),
    ],

    /*
    |----------------------------------------------------------------------------
    | Default theme config all devices
    |----------------------------------------------------------------------------
    |
    | Default config will be applied for all devices. Devices are identified by
    | their headers
    |
    | prefix:auth.*  (all methods starting with prefix)
     */
    'default' => [
        'default' => env('BRIGHT_THEME_NAME', 'tabler') . '|layouts.index',
        'auth.login' => env('BRIGHT_THEME_NAME', 'tabler') . '|layouts.index',
        'auth.*' => env('BRIGHT_THEME_NAME', 'tabler') . '|layouts.index',
    ],
    /*
    |----------------------------------------------------------------------------
    | Theme configuration for computer devices
    |----------------------------------------------------------------------------
    |
    | You can overwrite the theme config provided in default for computer (big screen)
    | devices
     */
    'computer' => [
        /*
        |----------------------------------------------------------------------------
        | Default key
        |----------------------------------------------------------------------------
        |
        | Default config will be used if route not matching with any of theme config
        | Example route configurations are:
        |
        |  members.login (members component and login method)
        |  members.* (members component and all methods)
         */
        'auth.changepass' => env('BRIGHT_THEME_NAME', 'tabler') . '|layouts.index',
    ],

    /*
    |----------------------------------------------------------------------------
    | Theme configuration for mobile devices
    |----------------------------------------------------------------------------
     */
    'phone' => [],

    /*
    |----------------------------------------------------------------------------
    | Theme configuration for tablet devices
    |----------------------------------------------------------------------------
     */
    'tablet' => [],
];
