<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\Request;
use Footup\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        $token = $request->header('Authorization');

        if ($token !== 'Bearer secret-token') {
            return Response::json(['error' => 'Unauthenticated'], 401);
        }

        return $next($request);
    }
}
