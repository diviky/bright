<?php

return [

    'use_cdn'    => env('USE_CDN', false),

    'cdn_url'    => '',

    'filesystem' => [
        'disk'    => 'cdn',

        'options' => [
            //
        ],
    ],

    'files'      => [
        'ignoreDotFiles' => true,

        'ignoreVCS'      => true,

        'include'        => [
            'paths'      => [
                'assets',
                'vendor',
            ],
            'files'      => [
                //
            ],
            'extensions' => [
                //
            ],
            'patterns'   => [
                //
            ],
        ],

        'exclude'        => [
            'paths'      => [
                //
            ],
            'files'      => [
                //
            ],
            'extensions' => [
                //
            ],
            'patterns'   => [
                //
            ],
        ],
    ],

];
