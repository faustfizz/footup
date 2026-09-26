<?php

declare(strict_types=1);

namespace Footup\Monitoring;

/*
|--------------------------------------------------------------------------
| Tiny file logger, one line per entry in storage/logs/app.log.
| Swap for a PSR-3 logger (e.g. `composer require monolog/monolog`)
| once you need multiple channels, rotation, or remote handlers.
|--------------------------------------------------------------------------
*/

class Log
{
    public static function write(string $level, string $message, array $context = []): void
    {
        try {
            // Use configured storage path
            $path = storage_path('logs/footup.log');
        } catch (\Throwable) {
            // Fallback to temp directory if app not available
            $path = sys_get_temp_dir() . '/footup_app.log';
        }

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

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }
}
