<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Localization Driver
    |--------------------------------------------------------------------------
    |
    | This option controls how the locale state is stored and isolated.
    |
    | Built-in Drivers: 
    |  - "native": Standard Laravel behavior (Global state).
    |  - "context": Laravel 11+ Context (Concurrent safe).
    |  - "swoole": Swoole Coroutine Context (For Laravel Octane).
    |  - "openswoole": OpenSwoole Coroutine Context.
    |
    | Custom Drivers:
    | You may provide the fully qualified class name of a custom driver.
    | The class must implement:
    | \Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract
    |
    */
    'driver' => env('LOCALIZATION_DRIVER', 'native'),
    'route_key' => 'locale',
];