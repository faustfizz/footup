<?php

namespace App\Modules;

use Footup\Engine\Module;

/**
 * API Module
 * 
 * Provides REST API functionality:
 * - API endpoints
 * - Rate limiting
 * - CORS handling
 * - API versioning
 * 
 * Structure:
 * ├── Controllers/ → API controllers
 * ├── Middleware/ → Rate limit, CORS, auth
 * ├── config/
 * │   ├── api.php → API configuration
 * │   └── routes.php → API routes (optional)
 * └── Views/ (optional) → API documentation
 * 
 * @example
 * ModuleManager::register(new ApiModule());
 * ModuleManager::boot();
 * 
 * // Now available:
 * // - API routes (v1/users, v1/posts, etc)
 * // - Middlewares (rate-limit, cors)
 * // - Config: config('modules.api.*')
 */
class ApiModule extends Module
{
    public function name(): string
    {
        return 'api';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function description(): string
    {
        return 'REST API module with versioning, rate limiting, and CORS';
    }

    public function boot(): void
    {
        // Load API config
        $config = $this->config('api', []);

        // Register API routes
        $this->registerRoutes();

        // Register middlewares
        $this->registerMiddlewares();

        // Listen to API events
        $this->registerEventListeners();

        $this->log('API module booted (v' . ($config['version'] ?? '1') . ')', 'info');
    }

    public function shutdown(): void
    {
        $this->log('API module shutdown', 'info');
    }

    /**
     * Register API routes
     * 
     * Routes are prefixed with /api/{version}/
     */
    private function registerRoutes(): void
    {
        // TODO: Register routes
        // Pattern: /api/v1/users, /api/v1/posts, etc
    }

    /**
     * Register API middlewares
     * 
     * - Rate limiting
     * - CORS
     * - Request validation
     * - Response formatting
     */
    private function registerMiddlewares(): void
    {
        // TODO: Register middlewares
    }

    /**
     * Register API event listeners
     */
    private function registerEventListeners(): void
    {
        // Listen to API-related events
        if (function_exists('Event')) {
            Event::listen('api:request', [$this, 'onApiRequest']);
            Event::listen('api:error', [$this, 'onApiError']);
        }
    }

    /**
     * Handle API request event
     */
    public function onApiRequest($request): void
    {
        // Log request, apply rate limiting, etc
        $this->log("API request: {$request->method} {$request->path}", 'debug');
    }

    /**
     * Handle API error event
     */
    public function onApiError($error): void
    {
        // Log error, send error response
        $this->log("API error: {$error->message}", 'error');
    }

    /**
     * Get API version
     */
    public function getVersion(): string
    {
        return $this->config('api.version', 'v1');
    }

    /**
     * Check if API is rate limited
     */
    public function isRateLimited(): bool
    {
        return $this->config('api.rate_limit.enabled', true);
    }

    /**
     * Get rate limit
     */
    public function getRateLimit(): int
    {
        return $this->config('api.rate_limit.requests_per_minute', 60);
    }
}
