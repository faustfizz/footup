<?php

declare(strict_types=1);

namespace Footup\Exceptions;

use Footup\Http\Request;
use Footup\Http\Response;

/*
|--------------------------------------------------------------------------
| Contract for turning an uncaught Throwable into a Response.
| Bind your own implementation to change how errors render, e.g.:
|   $app->singleton(ExceptionHandlerInterface::class, MyHandler::class);
|--------------------------------------------------------------------------
*/

interface ExceptionHandlerInterface
{
    public function render(\Throwable $e, Request $request): Response;
}
