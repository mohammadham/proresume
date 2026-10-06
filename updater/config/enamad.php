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

    /*
    |--------------------------------------------------------------------------
    | Digest
    |--------------------------------------------------------------------------
    | Recipient of the enamad:digest failure email. Falls back to the first
    | admins-table email when empty.
    */

    'digest_recipient' => env('ENAMAD_DIGEST_RECIPIENT', ''),

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
    | Footer Seal
    |--------------------------------------------------------------------------
    |
    | In-flow seal rendered inside the <footer> of a template, as opposed to
    | the floating corner widget. A fixed-position badge works on the front
    | site but covers page content on a tenant's single-page portfolio, so
    | the tenant themes, the tenant dashboard and the admin panel use this
    | one instead. Set enabled to false to hide the seal sitewide without
    | removing it from the templates.
    |
    */

    'footer' => [
        'enabled' => env('ENAMAD_FOOTER_ENABLED', true),
        'width' => 125,
        'height' => 36,
        'alignment' => 'center', // center, left, right
        'margin' => '18px',
        'show_title' => true,
        'title_size' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Seal Appearance
    |--------------------------------------------------------------------------
    |
    | Only the two properties that survived the removal of the floating
    | badge: the in-flow seal still rounds its corners and casts a shadow,
    | which is what keeps it from looking pasted onto the footer. The old
    | position/opacity/z-index/mobile-offset keys are gone - they only ever
    | configured the badge, and leaving them invites someone to "restore"
    | an overlay that covers page content.
    */
    'widget' => [
        'border_radius' => 8,
        'box_shadow' => '0 4px 12px rgba(0,0,0,0.15)',
    ],
];