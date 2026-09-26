<?php

declare(strict_types=1);

namespace Footup\Http\Middleware;

use Footup\Http\Request;
use Footup\Http\Response;

/*
|--------------------------------------------------------------------------
| Contract every middleware implements:
|   handle(Request $request, Closure $next): Response
|--------------------------------------------------------------------------
*/

interface MiddlewareInterface
{
    /**
     * @param \Footup\Http\Request $request
     * @param \Closure(\Footup\Http\Request):\Footup\Http\Response $next
     * 
     * @return \Footup\Http\Response
     */
    public function handle(Request $request, \Closure $next);
}
