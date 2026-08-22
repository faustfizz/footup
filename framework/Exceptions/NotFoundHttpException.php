<?php

declare(strict_types=1);

namespace Footup\Exceptions;

/*
|--------------------------------------------------------------------------
| 404 — no registered route matched the request path.
|--------------------------------------------------------------------------
*/

class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Not Found')
    {
        parent::__construct(404, $message);
    }
}
