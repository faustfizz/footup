<?php

declare(strict_types=1);

namespace Footup\Monitoring;

/*
|--------------------------------------------------------------------------
| Health Check — Monitor application health
|
| Usage:
|   GET /health → {status: "ok", services: {...}}
|   
|   HealthCheck::register('database', fn() => DB::connection()->ping());
|   HealthCheck::register('queue', fn() => Queue::isPushable());
|--------------------------------------------------------------------------
*/

class HealthCheck
{
    protected static array $checks = [];
    protected static array $results = [];

    /**
     * Register a health check
     */
    public static function register(string $name, callable $check, string $label = ''): void
    {
        static::$checks[$name] = [
            'check' => $check,
            'label' => $label ?: ucfirst($name),
        ];
    }

    /**
     * Run all health checks
     */
    public static function run(): array
    {
        static::$results = [];

        foreach (static::$checks as $name => $check) {
            static::$results[$name] = static::runCheck($name, $check['check']);
        }

        return static::$results;
    }

    /**
     * Run a single check
     */
    protected static function runCheck(string $name, callable $check): array
    {
        try {
            $start = microtime(true);
            $result = $check();
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $result === true || $result === null ? 'ok' : 'warning',
                'message' => is_string($result) ? $result : null,
                'duration_ms' => $duration,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'duration_ms' => null,
            ];
        }
    }

    /**
     * Get overall status
     */
    public static function status(): string
    {
        if (empty(static::$results)) {
            static::run();
        }

        foreach (static::$results as $result) {
            if ($result['status'] === 'error') {
                return 'error';
            }
        }

        foreach (static::$results as $result) {
            if ($result['status'] === 'warning') {
                return 'warning';
            }
        }

        return 'ok';
    }

    /**
     * Get all results
     */
    public static function results(): array
    {
        if (empty(static::$results)) {
            static::run();
        }

        return static::$results;
    }

    /**
     * Get single check result
     */
    public static function get(string $name): ?array
    {
        if (empty(static::$results)) {
            static::run();
        }

        return static::$results[$name] ?? null;
    }

    /**
     * Check if all checks passed
     */
    public static function isHealthy(): bool
    {
        return static::status() === 'ok';
    }

    /**
     * Get response for /health endpoint
     */
    public static function response(): array
    {
        return [
            'status' => static::status(),
            'timestamp' => date('Y-m-d H:i:s'),
            'app_name' => frameworkName() ?? 'Footup',
            'version' => frameworkVersion() ?? '1.0.0',
            'services' => static::results(),
        ];
    }
}
