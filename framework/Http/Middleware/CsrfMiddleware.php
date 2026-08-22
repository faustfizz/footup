<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\Csrf;
use Footup\Http\Request;
use Footup\Http\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        if (in_array($request->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->input('_token') ?? $request->header('X-CSRF-Token');

            if (! $token || ! Csrf::verify($token)) {
                return Response::json(['error' => 'CSRF token invalid'], 419);
            }
        }

        return $next($request);
    }
}
