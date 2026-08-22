<?php

declare(strict_types=1);

namespace Footup\Http;

/*
|--------------------------------------------------------------------------
| HTTP request wrapper — method, uri, query/body, headers, and
| per-request attributes (route params land here via setAttribute()).
|--------------------------------------------------------------------------
*/

class Request
{
    public array $attributes = [];

    public function __construct(
        public string $method,
        public string $uri,
        public array $query = [],
        public array $body = [],
        public array $headers = [],
        public array $server = [],
        public array $cookies = [],
        public array $files = [],
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }

        $rawBody = file_get_contents('php://input') ?: '';
        $body = $_POST;

        if (str_contains($headers['CONTENT-TYPE'] ?? '', 'application/json') && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return new self(
            method: $method,
            uri: $uri,
            query: $_GET,
            body: $body,
            headers: $headers,
            server: $_SERVER,
            cookies: $_COOKIE,
            files: $_FILES,
        );
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $all = array_merge($this->query, $this->body);

        if ($key === null) {
            return $all;
        }

        return $all[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtoupper($key)] ?? $this->headers[$key] ?? $default;
    }

    public function isJson(): bool
    {
        return str_contains($this->header('CONTENT-TYPE', ''), 'application/json');
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
