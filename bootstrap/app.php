<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application Bootstrap
|--------------------------------------------------------------------------
| This is the file you grow as your framework grows: register bindings,
| define middleware, define routes. Everything below is example code
| showing the intended usage pattern — swap it for your real app.
*/

use Footup\Engine\Application as FootupEngine;
use Footup\Http\Middleware\CorsMiddleware;
use Footup\Http\Middleware\CsrfMiddleware;
use Footup\Http\Middleware\RateLimitMiddleware;
use Footup\Http\Middleware\TimingMiddleware;
use Footup\Http\Middleware\TrimStringsMiddleware;
// -----------------------------------------------------------------
// Build the application
// -----------------------------------------------------------------

$app = new FootupEngine(basePath: dirname(__DIR__));

// Initialize i18n with locale from .env
\Footup\Utils\i18n::setLocale(env('APP_LOCALE', 'en'));


$app->providers([
    \App\Providers\AppServiceProvider::class,
    // \App\Providers\AuthServiceProvider::class,
    // \App\Providers\ModelServiceProvider::class,
])->middleware([
    TimingMiddleware::class,
    TrimStringsMiddleware::class,
    CorsMiddleware::class,
    new RateLimitMiddleware(
        (int) env('RATE_LIMIT_WINDOW', '60'),
        (int) env('RATE_LIMIT_REQUESTS', '100')
    ),
    CsrfMiddleware::class, 
])->routesFrom(
  web: __DIR__.'/../routes/web.php',
  api: __DIR__.'/../routes/api.php'
);

return $app;
