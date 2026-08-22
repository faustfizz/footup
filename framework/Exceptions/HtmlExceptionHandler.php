<?php

namespace Footup\Exceptions;

use Footup\Http\Request;
use Footup\Http\Response;

class HtmlExceptionHandler extends ExceptionHandler
{
    public function render(\Throwable $e, Request $request): Response
    {
        $status = $e instanceof \Footup\Exceptions\HttpException ? $e->getStatusCode() : 500;

        return Response::make(
            "<h1>Error {$status}</h1><p>" . htmlspecialchars($e->getMessage()) . '</p>',
            $status,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}