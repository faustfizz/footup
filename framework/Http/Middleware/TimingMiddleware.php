<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\Request;
use Footup\Http\Response;

class TimingMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        \Footup\Benchmark::reset();
        $start = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $totalMs = round((microtime(true) - $start) * 1000, 3);

        $entries = [];
        foreach (\Footup\Benchmark::summary() as $label => $stat) {
            $token = preg_replace('/[^a-zA-Z0-9_-]/', '_', $label);
            $entries[] = "{$token};dur={$stat['total_ms']}";
        }
        $entries[] = "total;dur={$totalMs}";

        // Server-Timing: shows up right in the browser DevTools Network
        // tab / `curl -I` — no extra tooling needed to see the breakdown.
        return $response
          ->withBody(
            str_replace('_ELAPSED_TIME_', "{$totalMs}ms", $response->body())
          )
          ->withHeader('Server-Timing', implode(', ', $entries))
          ->withHeader('X-Response-Time', "{$totalMs}ms");
    }
}
