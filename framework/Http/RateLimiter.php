<?php

declare(strict_types=1);

namespace Footup\Http;

/*
|--------------------------------------------------------------------------
| RateLimiter — simple file-based rate limiting
|
| Usage:
|   new RateLimitMiddleware(60, 100) // 100 requests per 60 seconds
|--------------------------------------------------------------------------
*/

class RateLimiter
{
    public function __construct(protected int $windowSeconds = 60, protected int $limit = 100)
    {
    }

    public function allow(string $key): bool
    {
        $dir = storage_path('rate-limit');

        if (! is_dir($dir)) {
          @mkdir($dir, 0775, true);
        }

        $file = $dir . '/' . hash('sha256', $key) . '.txt';
        $now = time();
        $window = $now - $this->windowSeconds;

        $requests = [];

        if (is_file($file)) {
            $data = json_decode(file_get_contents($file), true) ?? [];
            $requests = array_filter($data, fn ($t) => $t > $window);
        }

        if (count($requests) >= $this->limit) {
            return false;
        }

        $requests[] = $now;
        file_put_contents($file, json_encode(array_values($requests)));

        return true;
    }
}
