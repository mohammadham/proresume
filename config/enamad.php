<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enamad Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Enamad (نماد اعتماد الکترونیک) integration.
    | Requires registration at https://enamad.ir to obtain site_id and secret_key.
    |
    */

    'site_id' => env('ENAMAD_SITE_ID', ''),

    'secret_key' => env('ENAMAD_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    'api' => [
        'base_url' => env('ENAMAD_API_URL', 'https://api.enamad.ir/v1'),
        'verify_endpoint' => '/verify',
        'status_endpoint' => '/status',
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Trust Seal (نماد اعتماد) Configuration
    |--------------------------------------------------------------------------
    */
    'trust_seal' => [
        'base_url' => 'https://trustseal.enamad.ir',
        'logo_endpoint' => '/logo.aspx',
        'verify_endpoint' => '/verify',
        'js_widget_url' => 'https://trustseal.enamad.ir/Static/js/trustseal.js',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo Configuration
    |--------------------------------------------------------------------------
    |
    | Logo type: 'auto' (detect theme), 'light', 'dark'
    */
    'logo' => [
        'default_type' => 'auto', // auto, light, dark
        'width' => 100,
        'height' => 'auto',
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification Settings
    |--------------------------------------------------------------------------
    */
    'verification' => [
        'auto_verify_on_save' => true,
        'verify_interval_hours' => 24,
        'cache_ttl_minutes' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'prefix' => 'enamad_',
        'status_ttl' => 60, // minutes
        'verify_ttl' => 5, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Widget Settings
    |--------------------------------------------------------------------------
    */
    'widget' => [
        'position' => 'bottom-left', // bottom-left, bottom-right, top-left, top-right
        'opacity' => 0.8,
        'hover_opacity' => 1.0,
        'border_radius' => 8,
        'box_shadow' => '0 4px 12px rgba(0,0,0,0.15)',
        'z_index' => 1000,
        'mobile_bottom' => 80, // px from bottom on mobile
        'mobile_right' => 15, // px from right on mobile
    ],
];