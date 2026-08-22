<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
| Small, optional sugar over the container/config/view/error primitives
| in core/*.php (one class per file — see Application.php, Container.php,
| etc). Nothing in the core depends on these — use them
| in your controllers and routes because they're convenient, not because
| anything requires it. Loaded via composer.json's "files" autoload (or
| a plain require in public/index.php if Composer isn't installed).
*/

if (! function_exists('app')) {
    /**
     * Resolve the Application (no args) or anything bound in its
     * container (with $abstract) — e.g. app(Footup\View::class).
     */
    function app(?string $abstract = null): mixed
    {
        $app = \Footup\Engine\Application::current();

        return $abstract === null ? $app : $app->make($abstract);
    }
}

if (! function_exists('env')) {
    /**
     * Read a value from .env / the real environment, e.g. env('APP_ENV', 'production').
     */
    function env(string $key, mixed $default = null): mixed
    {
        return \Footup\Env::get($key, $default);
    }
}

if (! function_exists('config')) {
    /**
     * Read from the merged config/*.php files with dot notation, e.g.
     * config('app.name'), or config() for the whole array.
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = app('config');

        if ($key === null) {
            return $config;
        }

        $value = $config;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (! function_exists('response')) {
    function response(mixed $content = '', int $status = 200, array $headers = []): \Footup\Http\Response
    {
        return \Footup\Http\Response::make($content, $status, $headers);
    }
}

if (! function_exists('json')) {
    function json(mixed $data, int $status = 200): \Footup\Http\Response
    {
        return \Footup\Http\Response::json($data, $status);
    }
}

if (! function_exists('view')) {
    function view(string $template, array $data = [], int $status = 200): \Footup\Http\Response
    {
        $html = app(\Footup\View::class)->render($template, $data);

        return \Footup\Http\Response::make($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}

if (! function_exists('abort')) {
    /**
     * Stop the request with an HTTP error, e.g. abort(404, 'Not found').
     * Caught by Application::handle() and rendered by the bound
     * ExceptionHandlerInterface just like any other HttpException.
     */
    function abort(int $status, string $message = ''): never
    {
        throw new \Footup\Exceptions\HttpException($status, $message ?: 'Error');
    }
}

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app()->basePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
      return app()->storagePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('ui_path')) {
    function ui_path(string $path = ''): string
    {
      return app()->uiPath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('benchmark')) {
    /**
     * Time a block of code and get its own return value back — the
     * elapsed time is recorded under $label and shows up in the
     * Server-Timing header (see TimingMiddleware) and in
     * Footup\Benchmark::summary(). E.g.:
     *   $report = benchmark('report.generate', fn () => $this->buildReport());
     */
    function benchmark(string $label, \Closure $callback): mixed
    {
        return \Footup\Benchmark::measure($label, $callback);
    }
}

if (! function_exists('logger')) {
    /**
     * Quick one-liner logging to storage/logs/app.log, e.g.
     * logger('User logged in', ['id' => 42]).
     */
    function logger(string $message, array $context = []): void
    {
        \Footup\Log::info($message, $context);
    }
}

if (! function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \Footup\Http\Csrf::token();
    }
}

if (! function_exists('validate')) {
    function validate(array $data, array $rules): \Footup\Http\Validator
    {
        return new \Footup\Http\Validator($data, $rules);
    }
}

if (! function_exists('cache')) {
    function cache(string $key, ?callable $callback = null, int $seconds = 3600): mixed
    {
        if ($callback === null) {
            return \Footup\Cache::get($key);
        }

        return \Footup\Cache::remember($key, $seconds, $callback);
    }
}

if (! function_exists('uploaded_file')) {
    function uploaded_file(string $name): ?\Footup\Http\UploadedFile
    {
        if (! isset($_FILES[$name])) {
            return null;
        }

        return new \Footup\Http\UploadedFile($_FILES[$name]);
    }
}

if (! function_exists('trans')) {
    function trans(string $key, array $replace = []): string
    {
        return \Footup\i18n::trans($key, $replace);
    }
}

if (! function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return trans($key, $replace);
    }
}

if (! function_exists('auth')) {
    function auth()
    {
        return \Footup\Auth\Auth::class;
    }
}

if (! function_exists('auth_user')) {
    function auth_user()
    {
        return \Footup\Auth\Auth::user();
    }
}

if (! function_exists('queue')) {
    function queue(string $jobClass, array $data = []): void
    {
        \Footup\Queue::push($jobClass, $data);
    }
}
