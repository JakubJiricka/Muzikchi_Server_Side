<?php

return [
    'version' => env('APP_VERSION'),
    'demo'    => env('IS_DEMO_SITE'),
    'disable_update_auth' => env('DISABLE_UPDATE_AUTH'),
    'use_symlinks' => env('USE_SYMLINKS', false),
    'billing_enabled' => env('BILLING_ENABLED', false),
    'extra_bootstrap_data' => \App\Services\AppBootstrapData::class,

    'spotify' => [
        'id' => env('SPOTIFY_ID'),
        'secret' => env('SPOTIFY_SECRET')
    ],

    'lastfm' => [
        'key' => env('LASTFM_API_KEY'),
    ],

    'soundcloud' => [
        'key' => env('SOUNDCLOUD_API_KEY')
    ],

    'discogs' => [
        'id' => env('DISCOGS_ID'),
        'secret' => env('DISCOGS_SECRET')
    ]
];