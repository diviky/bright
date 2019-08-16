<?php
return [
    'device'   => 'computer',
    /*
    |----------------------------------------------------------------------------
    | Default theme config all devices
    |----------------------------------------------------------------------------
    |
    | Default config will be applied for all devices. Devices are identified by
    | their headers
     */
    'default'  => [
        'default'    => 'tabler',
        'auth.login' => 'tabler',
        'auth.*'     => 'tabler',
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
        'auth.changepass' => 'tabler',
    ],

    /*
    |----------------------------------------------------------------------------
    | Theme configuration for mobile devices
    |----------------------------------------------------------------------------
     */
    'phone'    => [],

    /*
    |----------------------------------------------------------------------------
    | Theme configuration for tablet devices
    |----------------------------------------------------------------------------
     */
    'tablet'   => [],
];
