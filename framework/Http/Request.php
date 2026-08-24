<?php

declare(strict_types=1);

namespace Footup\Http;

use Footup\Files\File;
use Footup\Utils\Arrays\ArrDots;

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

    /**
     * @param string $field
     * @return File[]|false
     */
    public function files($field = null)
    {
        $files = [];
        if (is_null($field)) {
            foreach ($this->files as $name => $fs) {
                $keys = array_keys($fs);
                foreach ($keys as $k => $v) {
                    foreach ($fs[$v] as $i => $val) {
                        $files[$name][$i][$v] = $val;
                    }
                }
            }

            return !empty($files) ? array_map(function ($item) {
                foreach ($item as $a => $e) {
                    $item[$a] = new File($e);
                };
                return $item;
            }, $files) : false;
        }

        if (!is_null($field) && isset($this->files[$field])) {
            foreach ($this->files[$field] as $name => $fs) {
                foreach ($fs as $i => $val) {
                    $files[$i][$name] = $val;
                }
            }

            return array_map(function ($item) {
                return new File($item);
            }, $files);
        } else {
            return false;
        }
    }

    /**
     * @param string $field
     * @return File|false
     */
    public function file($field = null)
    {
        if (is_null($field)) {
            $firstFile = reset($this->files);
            return $firstFile ? new File($firstFile) : false;
        }

        if (!is_null($field) && isset($this->files[$field]['name'])) {
            return new File($this->files[$field]);
        } else {
            return false;
        }
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $all = array_merge($this->query, $this->body);

        if ($key === null) {
            return $all;
        }

        return $all[$key] ?? $default;
    }    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function env($key, $default = null)
    {
        if (empty($key)) {
            return $default;
        }

        $key = str_replace('-', '_', mb_strtolower($key));
        $kname = mb_strtoupper($key);

        if (isset($this->env[$kname])) {
            return $this->env[$kname];
        }
        
        if (isset($this->env[$key])) {
            return $this->env[$key];
        }

        if (str_contains($key, '.')) {
            $value = ArrDots::get($this->env, $key);
            if ($value !== null) {
                return $value;
            }
        }
        
        return $this->header($key, $default);
    }

    /**
     * @param string $index
     * @param mixed $value
     * @return self
     */ 
    public function setEnv($index, $value): self
    {
        $normalizedIndex = mb_strtoupper(str_replace('-', '_', mb_strtolower($index)));
        $this->env[$normalizedIndex] = $value;

        return $this;
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
 
    /**
     * Get the current request URL (scheme + host + path)
     * e.g. http://localhost:8000/api/users
     */
    public function url(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->header('HOST', 'localhost');
 
        return "{$scheme}://{$host}{$this->uri}";
    }
 
    /**
     * Get the full URL with query string
     * e.g. http://localhost:8000/api/users?page=1&limit=10
     */
    public function fullUrl(): string
    {
        $url = $this->url();
        $query = http_build_query($this->query);
 
        return $query ? "{$url}?{$query}" : $url;
    }
 
    /**
     * Get just the request path (no domain)
     * e.g. /api/users
     */
    public function path(): string
    {
        return $this->uri;
    }
 
    /**
     * Check if the request accepts JSON
     * (looks at Accept header)
     */
    public function acceptsJson(): bool
    {
        $accept = $this->header('Accept', '');
 
        return str_contains($accept, 'application/json') || $this->isJson();
    }
 
    /**
     * Alias for acceptsJson()
     */
    public function wantsJson(): bool
    {
        return $this->acceptsJson();
    }
 
    /**
     * Check if this is a secure (HTTPS) request
     */
    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? 'off') !== 'off' ||
               ($this->header('X-Forwarded-Proto') === 'https');
    }
 
    /**
     * Get the request's IP address
     */
    public function ip(): string
    {
        return $this->header('X-Forwarded-For') ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }
 
    /**
     * Check if request method matches
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }

    /**
     * @return bool
     */
    public function ajax(): bool
    {
        return mb_strtoupper($this->header('X-REQUESTED-WITH')) === 'XMLHTTPREQUEST';
    }
}
