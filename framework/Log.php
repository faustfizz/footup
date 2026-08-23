<?php

declare(strict_types=1);

namespace Footup;

use Footup\Engine\Application;

/*
|--------------------------------------------------------------------------
| Swap for a PSR-3 logger (e.g. `composer require monolog/monolog`)
| once you need multiple channels, rotation, or remote handlers.
|--------------------------------------------------------------------------
*/

class Log
{
    public static function write(string $level, string $message, array $context = []): void
    {
        try {
            $basePath = Application::current()->basePath;
        } catch (\Throwable) {
            $basePath = sys_get_temp_dir();
        }

        $path = rtrim($basePath, '/') . '/storage/logs/footup.log';
        $dir = dirname($path);

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $line = sprintf(
            '[%s] %s: %s%s%s',
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '',
            PHP_EOL
        );

        @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }
}
