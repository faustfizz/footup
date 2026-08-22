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
     * @param \Closure(Request):Response $next
     */
    public function handle(Request $request, \Closure $next): Response;
}
