<?php

declare(strict_types=1);

namespace Footup\Monitoring;

use Footup\Files\File;

/*
|--------------------------------------------------------------------------
| Maintenance Mode — Put app in/out of maintenance
|
| Usage:
|   Maintenance::enable();           // Enable maintenance mode
|   Maintenance::disable();          // Disable maintenance mode
|   Maintenance::isEnabled();        // Check status
|   Maintenance::isAllowed();        // Check if request allowed
|
|   php bin/console down                  # Enable
|   php bin/console up                    # Disable
|   php bin/console down --allow=127.0.0.1 --message="Upgrading"
|--------------------------------------------------------------------------
*/

class Maintenance
{
    protected static string $flagFile = 'storage/maintenance.flag';
    protected static ?array $data = null;

    /**
     * Enable maintenance mode
     */
    public static function enable(array $options = []): void
    {
        $data = [
            'enabled' => true,
            'started_at' => now(),
            'message' => $options['message'] ?? 'Application is under maintenance.',
            'retry_after' => $options['retry_after'] ?? null,
            'allowed_ips' => $options['allowed'] ?? [],
            'secret' => $options['secret'] ?? null,
        ];

        File::put(static::flagFile(), json_encode($data));
        static::$data = $data;
    }

    /**
     * Disable maintenance mode
     */
    public static function disable(): void
    {
        if (File::exists(static::flagFile())) {
            File::delete(static::flagFile());
        }
        static::$data = null;
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isEnabled(): bool
    {
        if (static::$data !== null) {
            return static::$data['enabled'] ?? false;
        }

        if (!File::exists(static::flagFile())) {
            return false;
        }

        static::$data = json_decode(File::get(static::flagFile()), true);
        return static::$data['enabled'] ?? false;
    }

    /**
     * Check if current request is allowed during maintenance
     */
    public static function isAllowed(string $ip = '', string $secret = ''): bool
    {
        if (!static::isEnabled()) {
            return true;
        }

        $data = static::getData();

        // Check IP whitelist
        if (!empty($ip) && !empty($data['allowed_ips'])) {
            if (in_array($ip, $data['allowed_ips'])) {
                return true;
            }
        }

        // Check secret
        if (!empty($secret) && !empty($data['secret'])) {
            if ($secret === $data['secret']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get maintenance data
     */
    public static function getData(): array
    {
        if (static::$data !== null) {
            return static::$data;
        }

        if (!File::exists(static::flagFile())) {
            return [];
        }

        static::$data = json_decode(File::get(static::flagFile()), true);
        return static::$data;
    }

    /**
     * Get maintenance message
     */
    public static function message(): string
    {
        $data = static::getData();
        return $data['message'] ?? 'Application is under maintenance.';
    }

    /**
     * Get retry-after value
     */
    public static function retryAfter(): ?int
    {
        $data = static::getData();
        return $data['retry_after'] ?? null;
    }

    /**
     * Get started timestamp
     */
    public static function startedAt(): ?string
    {
        $data = static::getData();
        return $data['started_at'] ?? null;
    }

    /**
     * Get maintenance flag file path
     */
    protected static function flagFile(): string
    {
        return base_path(static::$flagFile);
    }

    /**
     * Set flag file location
     */
    public static function setFlagFile(string $path): void
    {
        static::$flagFile = $path;
    }
}

/**
 * Helper for current timestamp
 */
if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
