<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform Activity & Crash Logging
    |--------------------------------------------------------------------------
    |
    | Toggle platform-wide HTTP request logging on/off.
    | When disabled, the PlatformLogger middleware skips all DB writes.
    | Super Admin can also toggle this at runtime via Cache key
    | 'platform_logging_enabled' from the admin panel.
    |
    */
    'platform_logging' => env('PLATFORM_LOGGING_ENABLED', true),

];
