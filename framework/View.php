<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| Minimal plain-PHP template renderer. Swap this class out (or
| extend it) for something Blade-like once you need layouts, includes,
| and directives — controllers only ever see render(). Reports render
| time into Core\Benchmark automatically.
|--------------------------------------------------------------------------
*/

class View
{
    public function __construct(protected string $viewsPath)
    {
    }

    public function render(string $template, array $data = []): string
    {
        $path = rtrim($this->viewsPath, '/') . '/' . str_replace('.', '/', $template) . '.php';

        if (! is_file($path)) {
            throw new \RuntimeException("View [{$template}] not found at [{$path}].");
        }

        return Benchmark::measure("view:{$template}", function () use ($path, $data) {
            $renderer = function (string $__path, array $__data) {
                extract($__data, EXTR_SKIP);
                ob_start();
                include $__path;

                return ob_get_clean();
            };

            return $renderer($path, $data);
        });
    }
}
