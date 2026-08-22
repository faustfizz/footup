<?php

declare(strict_types=1);

namespace Footup\Exceptions;

/*
|--------------------------------------------------------------------------
| 405 — the path matched a route, but not for this HTTP method.
|--------------------------------------------------------------------------
*/

class MethodNotAllowedHttpException extends HttpException
{
    public function __construct(string $message = 'Method Not Allowed')
    {
        parent::__construct(405, $message);
    }
}
