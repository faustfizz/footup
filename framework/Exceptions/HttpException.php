<?php

declare(strict_types=1);

namespace Footup\Exceptions;

/*
|--------------------------------------------------------------------------
| Base class for HTTP errors that carry a status code. Throw it
| directly, or via the abort() helper — caught by Application::handle()
| and rendered by whatever ExceptionHandlerInterface is bound.
|--------------------------------------------------------------------------
*/

class HttpException extends \RuntimeException
{
    public function __construct(protected int $statusCode, string $message = '')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
