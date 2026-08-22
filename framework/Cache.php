<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| Cache — simple file-based caching
|
| Usage:
|   Cache::remember('users', 3600, fn () => User::all());
|   Cache::put('key', $value, 3600);
|   Cache::get('key');
|   Cache::forget('key');
|--------------------------------------------------------------------------
*/

class Cache
{
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
        $dir = storage_path('cache');
        @mkdir($dir, 0775, true);

        $file = $dir . '/' . hash('sha256', $key) . '.cache';
        $data = [
            'expires_at' => time() + $seconds,
            'value' => $value,
        ];

        file_put_contents($file, serialize($data));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $dir = storage_path('cache');
        $file = $dir . '/' . hash('sha256', $key) . '.cache';

        if (! is_file($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires_at'] < time()) {
            unlink($file);

            return $default;
        }

        return $data['value'];
    }

    public static function forget(string $key): void
    {
        $dir = storage_path('cache');
        $file = $dir . '/' . hash('sha256', $key) . '.cache';

        if (is_file($file)) {
            unlink($file);
        }
    }

    public static function flush(): void
    {
        $dir = storage_path('cache');

        if (is_dir($dir)) {
            array_map('unlink', glob($dir . '/*.cache'));
        }
    }
}
