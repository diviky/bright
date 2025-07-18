<?php

declare(strict_types=1);

return [
    'device' => env('BRIGHT_THEME_DEVICE', 'computer'),

    /*
    |--------------------------------------------------------------------------
    | Parent Theme
    |--------------------------------------------------------------------------
    |
    | This is a parent theme for the theme specified in the active config
    | option. It works like the WordPress style theme hierarchy, if the blade
    | file is not found in the currently active theme, then it will look for it
    | in the parent theme.
    */
    'parent' => env('BRIGHT_THEME_PARENT'),

    /*
    |--------------------------------------------------------------------------
    | Default Active Theme
    |--------------------------------------------------------------------------
    |
    | It will assign the default active theme to be used if one is not set during
    | runtime.
    */
    'active' => env('BRIGHT_THEME_NAME', 'bootstrap'),

    /*
    |--------------------------------------------------------------------------
    | Base Path
    |--------------------------------------------------------------------------
    |
    | The base path where all the themes are located.
    */
    'base_path' => resource_path('themes'),

    'paths' => [
    ],

    'layout' => env('BRIGHT_THEME_LAYOUT', 'default'),

    'layouts' => [
        'default' => [
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
                'default' => env('BRIGHT_THEME_NAME', 'bootstrap') . '::layouts.index',
                'auth.login' => env('BRIGHT_THEME_NAME', 'bootstrap') . '::layouts.index',
                'auth.*' => env('BRIGHT_THEME_NAME', 'bootstrap') . '::layouts.index',
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
                'auth.changepass' => env('BRIGHT_THEME_NAME', 'bootstrap') . '::layouts.index',
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
        ],
    ],
];
