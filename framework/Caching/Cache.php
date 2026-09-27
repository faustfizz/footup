<?php

declare(strict_types=1);

namespace Footup\Caching;

/*
|--------------------------------------------------------------------------
| Cache — simple file-based caching with driver support
|
| Drivers: file, array, database
| Configuration: CACHE_DRIVER env variable
| Usage:
|   Cache::remember('users', 3600, fn () => User::all());
|   Cache::put('key', $value, 3600);
|   Cache::get('key');
|   Cache::forget('key');
|--------------------------------------------------------------------------
*/

class Cache
{
    private static ?string $driver = null;
    private static array $arrayStore = [];

    /**
     * Get configured cache driver (from CACHE_DRIVER env)
     */
    private static function driver(): string
    {
        if (self::$driver === null) {
            self::$driver = env('CACHE_DRIVER', 'file');
        }
        return self::$driver;
    }

    public static function remember(string $key, int $seconds, callable $callback): mixed
    {
        if ($value = self::get($key)) {
            return $value;
        }

        $value = $callback();
        self::put($key, $value, $seconds);

        return $value;
    }

    public static function put(string $key, mixed $value, int $seconds = 3600): void
    {
        $driver = self::driver();

        match ($driver) {
            'file' => self::putFile($key, $value, $seconds),
            'array' => self::putArray($key, $value, $seconds),
            'database' => self::putDatabase($key, $value, $seconds),
            default => throw new \RuntimeException("Cache driver '{$driver}' not supported"),
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $driver = self::driver();

        return match ($driver) {
            'file' => self::getFile($key, $default),
            'array' => self::getArray($key, $default),
            'database' => self::getDatabase($key, $default),
            default => $default,
        };
    }

    public static function forget(string $key): void
    {
        $driver = self::driver();

        match ($driver) {
            'file' => self::forgetFile($key),
            'array' => self::forgetArray($key),
            'database' => self::forgetDatabase($key),
            default => null,
        };
    }

    public static function flush(): void
    {
        $driver = self::driver();

        match ($driver) {
            'file' => self::flushFile(),
            'array' => self::flushArray(),
            'database' => self::flushDatabase(),
            default => null,
        };
    }

    // ========== FILE DRIVER ==========

    private static function putFile(string $key, mixed $value, int $seconds): void
    {
        $dir = storage_path('cache');
        @mkdir($dir, 0775, true);

        $file = $dir . '/' . hash('sha256', $key) . '.cache';
        $data = [
            'expires_at' => time() + $seconds,
            'value' => $value,
        ];

        file_put_contents($file, serialize($data));
    }

    private static function getFile(string $key, mixed $default = null): mixed
    {
        $dir = storage_path('cache');
        $file = $dir . '/' . hash('sha256', $key) . '.cache';

        if (!is_file($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires_at'] < time()) {
            unlink($file);
            return $default;
        }

        return $data['value'];
    }

    private static function forgetFile(string $key): void
    {
        $dir = storage_path('cache');
        $file = $dir . '/' . hash('sha256', $key) . '.cache';

        if (is_file($file)) {
            unlink($file);
        }
    }

    private static function flushFile(): void
    {
        $dir = storage_path('cache');

        if (is_dir($dir)) {
            array_map('unlink', glob($dir . '/*.cache'));
        }
    }

    // ========== ARRAY DRIVER (in-memory only) ==========

    private static function putArray(string $key, mixed $value, int $seconds): void
    {
        self::$arrayStore[$key] = [
            'expires_at' => time() + $seconds,
            'value' => $value,
        ];
    }

    private static function getArray(string $key, mixed $default = null): mixed
    {
        if (!isset(self::$arrayStore[$key])) {
            return $default;
        }

        $data = self::$arrayStore[$key];

        if ($data['expires_at'] < time()) {
            unset(self::$arrayStore[$key]);
            return $default;
        }

        return $data['value'];
    }

    private static function forgetArray(string $key): void
    {
        unset(self::$arrayStore[$key]);
    }

    private static function flushArray(): void
    {
        self::$arrayStore = [];
    }

    // ========== DATABASE DRIVER ==========

    private static function putDatabase(string $key, mixed $value, int $seconds): void
    {
        // Placeholder: Database driver implementation
        // Would store in database table like 'cache'
        // For now, fallback to file driver
        self::putFile($key, $value, $seconds);
    }

    private static function getDatabase(string $key, mixed $default = null): mixed
    {
        // Placeholder: Database driver implementation
        // For now, fallback to file driver
        return self::getFile($key, $default);
    }

    private static function forgetDatabase(string $key): void
    {
        // Placeholder: Database driver implementation
        // For now, fallback to file driver
        self::forgetFile($key);
    }

    private static function flushDatabase(): void
    {
        // Placeholder: Database driver implementation
        // For now, fallback to file driver
        self::flushFile();
    }
}
