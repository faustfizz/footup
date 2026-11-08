<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\Middleware\MiddlewareInterface;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Auth\Auth;

/*
|--------------------------------------------------------------------------
| AuthMiddleware — Protect routes with authentication requirement
|
| Usage in routes:
|   Route::get('/dashboard', 'DashboardController@index')->middleware('auth');
|--------------------------------------------------------------------------
*/

class AuthMiddleware implements MiddlewareInterface
{

    public function handle(Request $request, \Closure $next): Response
    {
        // Before logic
        if (!Auth::check()) {
            redirect(route('login'));
            exit;
        }

        // Pass to next middleware/controller
        return $next($request);
    }
}
