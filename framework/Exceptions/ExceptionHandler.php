<?php

declare(strict_types=1);

namespace Footup\Exceptions;

use Footup\Http\Request;
use Footup\Http\Response;
use Footup\View;

/*
|--------------------------------------------------------------------------
| Default exception handler — intelligently renders JSON for APIs, HTML for web.
| Checks the route context (_route_context on request) to decide rendering.
|
| If you want to override this for all routes, extend this class:
|   class MyHandler extends ExceptionHandler {
|       protected function isApiRoute(Request $r): bool { return true; }
|   }
| Then bind it: $app->singleton(ExceptionHandlerInterface::class, MyHandler::class);
|--------------------------------------------------------------------------
*/

class ExceptionHandler implements ExceptionHandlerInterface
{
    public function render(\Throwable $e, Request $request): Response
    {
        $isApi = $this->isApiRoute($request);

        if ($isApi) {
            return $this->renderJson($e);
        }

        return $this->renderHtml($e);
    }

    /**
     * Determine if this is an API request based on route context or Accept header.
     */
    protected function isApiRoute(Request $request): bool
    {
        // Check if the route has an explicit context
        $context = $request->attribute('_route_context');

        if ($context === 'api') {
            return true;
        }

        if ($context === 'web') {
            return false;
        }

        // Fall back to Accept header (no explicit route context)
        $accept = $request->header('Accept', '');

        return str_contains($accept, 'application/json');
    }

    protected function renderJson(\Throwable $e): Response
    {
        $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

        return Response::json(
            [
                'error' => $e instanceof HttpException ? $e->getMessage() : 'Internal Server Error',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ],
            $status
        );
    }

    protected function renderHtml(\Throwable $e): Response
    {
        $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $view = new View(ui_path('/views/errors'));

        try {
            $html = $view->render((string)$status, [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return Response::make($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
        } catch (\Throwable) {
            // View doesn't exist, fall back to JSON
            return $this->renderJson($e);
        }
    }
}
