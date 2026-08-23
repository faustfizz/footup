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
\Footup\i18n::setLocale(env('APP_LOCALE', 'en'));

// Global middleware stack (the "Kernel"), executed on every request,
// in the order listed — mirrors App\Http\Kernel::$middleware in Laravel.
$app->middleware([
    TimingMiddleware::class,
    TrimStringsMiddleware::class,
    CorsMiddleware::class,
    new RateLimitMiddleware(
        (int) env('RATE_LIMIT_WINDOW', '60'),
        (int) env('RATE_LIMIT_REQUESTS', '100')
    ),
    CsrfMiddleware::class, 
]);

// Routes — mirrors routes/web.php + routes/api.php in Laravel. The
// Route facade proxies to the booted Router, so no $router param needed.
$app->routesFrom(
  web: __DIR__.'/../routes/web.php',
  api: __DIR__.'/../routes/api.php',
  cli: __DIR__.'/../routes/console.php',
);

return $app;
