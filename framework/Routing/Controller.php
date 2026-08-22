<?php

declare(strict_types=1);

namespace Footup\Routing;

use Footup\Http\Response;
use Footup\View;

/*
|--------------------------------------------------------------------------
| Optional base controller. Extend it to get json()/
| response()/view() convenience methods, and an auto-injected Core\View
| (resolved from the container, if bound) for free.
|--------------------------------------------------------------------------
*/

abstract class Controller
{
    public function __construct(protected ?View $view = null)
    {
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function response(mixed $content = '', int $status = 200): Response
    {
        return Response::make($content, $status);
    }

    protected function view(string $template, array $data = [], int $status = 200): Response
    {
        if (! $this->view) {
            throw new \RuntimeException(
                'No View engine was injected into ' . static::class . '. Bind Core\View in the container.'
            );
        }

        return Response::make($this->view->render($template, $data), $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
