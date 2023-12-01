<?php

/**
 * Middleware Exemple
 */

namespace App\Middle;

use Footup\Http\Request;
use Footup\Http\Response;

class Maintenance
{
    /**
     * Execute the middle --- Function called by the core framework
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next to queue middles
     * @return Response|void -- to continue the request return $next($request, $response)
     */
    public function execute(Request $request, Response $response, $next)
    {
        return $response->die(503, 'Site Web en Maintenance', 'Ce site est en maintenance !');
    }
}