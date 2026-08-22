<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\RateLimiter;
use Footup\Http\Request;
use Footup\Http\Response;

/*
|--------------------------------------------------------------------------
| RateLimiter — simple file-based rate limiting
|
| Usage:
|   new RateLimitMiddleware(60, 100) // 100 requests per 60 seconds
|--------------------------------------------------------------------------
*/

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(protected int $windowSeconds = 60, protected int $limit = 100)
    {
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $key = ($request->header('X-Forwarded-For') ?? $request->server['REMOTE_ADDR'] ?? 'unknown');
        $limiter = new RateLimiter($this->windowSeconds, $this->limit);

        if (! $limiter->allow($key)) {
            return Response::json(['error' => 'Rate limit exceeded'], 429);
        }

        return $next($request);
    }
}
