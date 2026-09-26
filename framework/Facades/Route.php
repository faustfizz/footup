<?php

declare(strict_types=1);

namespace Footup\Facades;

use Footup\Engine\Application;

/*
|--------------------------------------------------------------------------
| Facades
|--------------------------------------------------------------------------
| Static, Laravel-style sugar over the booted Router. 
|
| Route::get('/users', [UserController::class, 'index']);
| Route::group(['prefix' => 'api'], function () {
|     Route::get('/users/{id}', [UserController::class, 'show']);
| });
*/

/**
 * @method static \Footup\Routing\Route get(string $uri, \Closure|array $action)
 * @method static \Footup\Routing\Route post(string $uri, \Closure|array $action)
 * @method static \Footup\Routing\Route put(string $uri, \Closure|array $action)
 * @method static \Footup\Routing\Route patch(string $uri, \Closure|array $action)
 * @method static \Footup\Routing\Route delete(string $uri, \Closure|array $action)
 * @method static void group(array $attributes, \Closure $callback)
 * @method static \Footup\Routing\Route[] all()
 */
class Route
{
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return Application::current()->router()->{$method}(...$arguments);
    }
}
