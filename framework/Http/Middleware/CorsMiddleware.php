<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\Request;
use Footup\Http\Response;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }
}
