<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Monitoring\Maintenance;
use Footup\Http\Response;
use Footup\Http\Request;

/*
|--------------------------------------------------------------------------
| Maintenance Middleware — Block requests during maintenance
|--------------------------------------------------------------------------
*/

class MaintenanceMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        // Check if maintenance mode is enabled
        if (!Maintenance::isEnabled()) {
            return $next($request);
        }

        // Get client IP
        $ip = $request->ip();

        // Check if request is allowed (whitelisted IP or valid secret)
        $secret = $request->header('X-Maintenance-Secret');
        if (Maintenance::isAllowed($ip, $secret)) {
            return $next($request);
        }

        // Skip health check endpoint
        if ($request->path() === '/health') {
            return $next($request);
        }

        // Return maintenance response
        return $this->maintenanceResponse();
    }

    /**
     * Build maintenance response
     */
    protected function maintenanceResponse(): Response
    {
        $retryAfter = Maintenance::retryAfter();
        $message = Maintenance::message();

        $response = new Response(
            json_encode([
                'status' => 'maintenance',
                'message' => $message,
                'retry_after' => $retryAfter,
            ]),
            503,
            [
                'Content-Type' => 'application/json',
                'Retry-After' => $retryAfter ?? 3600,
            ]
        );

        return $response;
    }
}
