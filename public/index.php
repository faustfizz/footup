<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Front Controller
|--------------------------------------------------------------------------
| Every HTTP request is routed here (configure your web server to funnel
| all requests through this file — see public/.htaccess for Apache, or
| run `php bin/console.php serve` / `php -S localhost:8000 -t public`).
|
| Steps:
|   1. Load Composer's autoloader if present, else a small built-in
|      fallback (see core/autoload.php) — either way every Core\ class
|      and the global helpers become available.
|   2. Load .env into the environment.
|   3. Boot the Application (container, config, middleware, routes).
|   4. Capture the Request, hand it to the app, send back the Response.
*/

require dirname(__DIR__) . '/framework/autoload.php';

use Footup\Env;
use Footup\Http\Request;

Env::load('./../.env');

/** @var \Footup\Engine\Application $app */
$app = require '../bootstrap/app.php';

$request = Request::capture();

$response = $app->handle($request);

$response->send();
