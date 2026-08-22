<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| Minimal .env loader. No parsing frills (no variable
| interpolation, no export syntax) — just KEY=VALUE, one per line,
| '#' comments, and optional quotes. Swap for vlucas/phpdotenv via
| Composer if you outgrow this.
|--------------------------------------------------------------------------
*/

class Env
{
    /** @var array<string, string> */
    protected static array $variables = [];

    public static function load(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (strlen($value) >= 2 && in_array($value[0], ['"', "'"], true) && $value[-1] === $value[0]) {
                $value = substr($value, 1, -1);
            }

            self::$variables[$key] = $value;
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }

        $value = getenv($key);

        return $value === false ? $default : $value;
    }
}
