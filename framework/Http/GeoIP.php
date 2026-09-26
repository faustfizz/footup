<?php

declare(strict_types=1);

namespace Footup\Http;

/*
|--------------------------------------------------------------------------
| GeoIP — Identify user's region/country from IP address
|
| Usage in Request:
|   $geo = $request->geo();
|   $geo->ip();              // IP address
|   $geo->country();         // 'United States'
|   $geo->countryCode();     // 'US'
|   $geo->region();          // 'California'
|   $geo->city();            // 'San Francisco'
|   $geo->isLocal();         // true if localhost/private IP
|   $geo->toArray();         // All info as array
|
| Usage in code:
|   $geo = GeoIP::detect($ip);
|   $geo = GeoIP::detect($_SERVER['REMOTE_ADDR']);
|   if ($geo->isLocal()) { ... }
|   if ($geo->countryCode() === 'US') { ... }
|
| Data sources:
|   - Local cache for common IPs
|   - MaxMind GeoLite2 (optional, requires setup)
|   - IP2Location (optional)
|   - Fallback to safe defaults
|--------------------------------------------------------------------------
*/

class GeoIP
{
    private string $ip;
    private string $country = 'Unknown';
    private string $countryCode = 'XX';
    private string $region = 'Unknown';
    private string $city = 'Unknown';
    private string $timezone = 'UTC';
    private bool $local = false;
    private ?array $coordinates = null;

    private static array $localIPs = [
        '127.0.0.1',
        '::1',
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        'fc00::/7',
    ];

    public function __construct(string $ip)
    {
        $this->ip = $ip;
        $this->detectLocation();
    }

    /**
     * Static factory method
     */
    public static function detect(string $ip): self
    {
        return new self($ip);
    }

    /**
     * Detect IP info
     */
    private function detectLocation(): void
    {
        // Check if local/private IP
        if ($this->isPrivateIP($this->ip)) {
            $this->local = true;
            $this->country = 'Local';
            $this->countryCode = 'LOCAL';
            $this->region = 'Local Network';
            $this->city = 'Local';
            return;
        }

        // Try to load from cache/database
        $cached = $this->loadFromCache($this->ip);
        if ($cached) {
            $this->applyData($cached);
            return;
        }

        // Try external services (optional, requires API key)
        $result = $this->detectFromService($this->ip);
        if ($result) {
            $this->applyData($result);
            return;
        }

        // Fallback: at least have IP address
        $this->detectFallback();
    }

    /**
     * Check if IP is private/local
     */
    private function isPrivateIP(string $ip): bool
    {
        // Remove IPv6 prefix if present
        $ip = str_replace('::ffff:', '', $ip);

        // Check local IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Check private ranges
        $privateRanges = [
            '127.0.0.0/8',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            'fc00::/7',
        ];

        foreach ($privateRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, ':') !== false) {
            // IPv6
            return $this->ipv6InRange($ip, $range);
        }

        // IPv4
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) === $subnet;
    }

    /**
     * Check if IPv6 is in range (simplified)
     */
    private function ipv6InRange(string $ip, string $range): bool
    {
        // Simplified check
        return stripos($ip, 'fc') === 0 || stripos($ip, 'fe') === 0;
    }

    /**
     * Load from local cache (simple array cache)
     */
    private function loadFromCache(string $ip): ?array
    {
        // Cache common IPs or CDN providers
        $cache = [
            // Google
            '8.8.8.8' => ['country' => 'United States', 'countryCode' => 'US', 'region' => 'California', 'city' => 'Mountain View', 'timezone' => 'America/Los_Angeles'],
            '8.8.4.4' => ['country' => 'United States', 'countryCode' => 'US', 'region' => 'California', 'city' => 'Mountain View', 'timezone' => 'America/Los_Angeles'],
            
            // Cloudflare
            '1.1.1.1' => ['country' => 'United States', 'countryCode' => 'US', 'region' => 'California', 'city' => 'San Francisco', 'timezone' => 'America/Los_Angeles'],
            
            // OpenDNS
            '208.67.222.222' => ['country' => 'United States', 'countryCode' => 'US', 'region' => 'California', 'city' => 'San Francisco', 'timezone' => 'America/Los_Angeles'],
        ];

        return $cache[$ip] ?? null;
    }

    /**
     * Detect from external service (optional)
     */
    private function detectFromService(string $ip): ?array
    {
        // This would require an API key and external service
        // Examples: MaxMind, IP2Location, GeoIP2
        // For now, we return null (fallback will be used)
        
        // Example of how to implement:
        // try {
        //     $response = file_get_contents("https://geoip.api/ip/{$ip}");
        //     return json_decode($response, true);
        // } catch (Exception $e) {
        //     return null;
        // }

        return null;
    }

    /**
     * Fallback detection
     */
    private function detectFallback(): void
    {
        // When we can't determine location, set defaults
        $this->country = 'Unknown';
        $this->countryCode = 'XX';
        $this->region = 'Unknown';
        $this->city = 'Unknown';
    }

    /**
     * Apply detected data
     */
    private function applyData(array $data): void
    {
        if (isset($data['country'])) {
            $this->country = $data['country'];
        }
        if (isset($data['countryCode'])) {
            $this->countryCode = strtoupper($data['countryCode']);
        }
        if (isset($data['region'])) {
            $this->region = $data['region'];
        }
        if (isset($data['city'])) {
            $this->city = $data['city'];
        }
        if (isset($data['timezone'])) {
            $this->timezone = $data['timezone'];
        }
        if (isset($data['latitude']) && isset($data['longitude'])) {
            $this->coordinates = [
                'latitude' => (float)$data['latitude'],
                'longitude' => (float)$data['longitude'],
            ];
        }
    }

    // ==================== GETTERS ====================

    /**
     * Get IP address
     */
    public function ip(): string
    {
        return $this->ip;
    }

    /**
     * Get country name
     */
    public function country(): string
    {
        return $this->country;
    }

    /**
     * Get country code (2-letter ISO)
     */
    public function countryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Get region/state
     */
    public function region(): string
    {
        return $this->region;
    }

    /**
     * Get city
     */
    public function city(): string
    {
        return $this->city;
    }

    /**
     * Get timezone
     */
    public function timezone(): string
    {
        return $this->timezone;
    }

    /**
     * Get coordinates (latitude, longitude)
     */
    public function coordinates(): ?array
    {
        return $this->coordinates;
    }

    /**
     * Check if IP is local/private
     */
    public function isLocal(): bool
    {
        return $this->local;
    }

    /**
     * Get location as string
     */
    public function location(): string
    {
        if ($this->isLocal()) {
            return 'Local Network';
        }

        $parts = [];
        if ($this->city !== 'Unknown') {
            $parts[] = $this->city;
        }
        if ($this->region !== 'Unknown') {
            $parts[] = $this->region;
        }
        if ($this->country !== 'Unknown') {
            $parts[] = $this->country;
        }

        return implode(', ', $parts) ?: 'Unknown Location';
    }

    /**
     * Get all geo info as array
     */
    public function toArray(): array
    {
        return [
            'ip' => $this->ip,
            'country' => $this->country,
            'country_code' => $this->countryCode,
            'region' => $this->region,
            'city' => $this->city,
            'timezone' => $this->timezone,
            'coordinates' => $this->coordinates,
            'is_local' => $this->local,
            'location' => $this->location(),
        ];
    }

    /**
     * JSON representation
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
