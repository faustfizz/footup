<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\Request;
use Footup\Http\Response;

class TrimStringsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        $request->body = array_map(
            fn ($v) => is_string($v) ? trim($v) : $v,
            $request->body
        );

        return $next($request);
    }
}
