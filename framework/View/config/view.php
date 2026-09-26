<?php

/**
 * View/Template Configuration
 * 
 * Configuration for Footup Template Engine
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Template Engine
    |--------------------------------------------------------------------------
    |
    | Which template engine to use by default.
    | Must be a key defined in the 'engines' array below.
    |
    */
    'engine' => 'footup',

    /*
    |--------------------------------------------------------------------------
    | Template Engines
    |--------------------------------------------------------------------------
    |
    | Define all available template engines and their configuration.
    | Each engine can have its own paths, extensions, and settings.
    |
    | For built-in Footup Engine:
    |   - Omit 'class' (uses Footup\Template\Engine by default)
    |
    | For custom engines:
    |   - Add 'class' => 'Fully\Qualified\ClassName'
    |   - Class must implement TemplateEngineInterface
    |   - Constructor receives the config array
    |
    */
    'engines' => [
        'footup' => [
            // 'class' omitted = built-in Footup Engine
            'extensions' => ['.ftup', '.kick'],
            'paths' => [
                'resources/views',
                'app/Modules/*/resources/views',
            ],
            'compiled' => 'storage/compiled/views',
            'production' => env('APP_ENV') === 'production',
            'attribute_prefixes' => ['ui:', ':'],
        ],

        // Example custom engine (commented out)
        // 'blade' => [
        //     'class' => 'App\Engines\BladeTemplateEngine',
        //     'extensions' => ['.blade.php'],
        //     'paths' => ['resources/views'],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Settings
    |--------------------------------------------------------------------------
    |
    | Settings applied to all engines (can be overridden per-engine)
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Auto-escaping
    |--------------------------------------------------------------------------
    |
    | Automatically escape variable output for security (Footup Engine only).
    | Use {! $var !} or |raw filter to bypass escaping.
    |
    */
    'auto_escape' => true,

    /*
    |--------------------------------------------------------------------------
    | Compile on Demand
    |--------------------------------------------------------------------------
    |
    | Compile templates on-demand during rendering.
    | Set to false if using pre-compiled templates.
    |
    */
    'compile_on_demand' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Expiration
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) to keep cached templates.
    | Set to 0 to disable cache expiration.
    |
    */
    'cache_expiration' => 0,

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | How to handle template errors.
    | Options: 'throw' (throw exception), 'log' (log and render empty),
    |          'display' (display error message in output)
    |
    */
    'error_handling' => env('APP_DEBUG') ? 'throw' : 'log',
];
