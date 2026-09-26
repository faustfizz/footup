<?php

declare(strict_types=1);

namespace Footup\Http;

/*
|--------------------------------------------------------------------------
| DeviceDetector — Identify user device from User-Agent
|
| Usage in Request:
|   $device = $request->device();
|   $device->type();          // 'desktop', 'mobile', 'tablet'
|   $device->browser();       // 'Chrome', 'Firefox', 'Safari', etc
|   $device->os();            // 'Windows', 'macOS', 'Linux', 'iOS', 'Android'
|   $device->toArray();       // All info as array
|
| In code:
|   $device = DeviceDetector::detect($userAgent);
|   if ($device->isMobile()) { ... }
|   if ($device->isTablet()) { ... }
|   if ($device->isDesktop()) { ... }
|--------------------------------------------------------------------------
*/

class DeviceDetector
{
    private string $userAgent;
    private string $type = 'unknown';
    private string $browser = 'unknown';
    private string $os = 'unknown';
    private string $version = 'unknown';

    public function __construct(string $userAgent)
    {
        $this->userAgent = $userAgent;
        $this->detect();
    }

    /**
     * Static factory method
     */
    public static function detect(string $userAgent): self
    {
        return new self($userAgent);
    }

    /**
     * Analyze User-Agent string
     */
    private function detect(): void
    {
        $ua = $this->userAgent;

        // Detect device type
        $this->detectDeviceType($ua);

        // Detect browser
        $this->detectBrowser($ua);

        // Detect operating system
        $this->detectOS($ua);
    }

    /**
     * Detect device type: mobile, tablet, or desktop
     */
    private function detectDeviceType(string $ua): void
    {
        // Mobile devices
        if (preg_match('/(Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone|Opera Mini)/i', $ua)) {
            // Further check if tablet
            if (preg_match('/(iPad|Android(?!.*Mobi)|Tablet|Kindle|Playbook|Nexus 7|Nexus 10)/i', $ua)) {
                $this->type = 'tablet';
            } else {
                $this->type = 'mobile';
            }
        } else {
            $this->type = 'desktop';
        }
    }

    /**
     * Detect browser
     */
    private function detectBrowser(string $ua): void
    {
        $browsers = [
            'Edge' => 'Edge',
            'Edg' => 'Edge',
            'OPR' => 'Opera',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'Firefox' => 'Firefox',
            'MSIE' => 'Internet Explorer',
            'Trident' => 'Internet Explorer',
        ];

        foreach ($browsers as $key => $name) {
            if (stripos($ua, $key) !== false) {
                $this->browser = $name;
                
                // Try to extract version
                if (preg_match("/{$key}[\/\s]*([\d.]+)/i", $ua, $match)) {
                    $this->version = $match[1];
                }
                break;
            }
        }
    }

    /**
     * Detect operating system
     */
    private function detectOS(string $ua): void
    {
        $oses = [
            'Windows NT 10.0' => 'Windows 10',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
            'Windows NT' => 'Windows',
            'Mac OS X 14' => 'macOS Sonoma',
            'Mac OS X 13' => 'macOS Ventura',
            'Mac OS X 12' => 'macOS Monterey',
            'Mac OS X 11' => 'macOS Big Sur',
            'Mac OS X 10' => 'macOS',
            'Macintosh' => 'macOS',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
            'iPod' => 'iOS',
            'Linux' => 'Linux',
            'X11' => 'Linux',
            'CrOS' => 'Chrome OS',
        ];

        foreach ($oses as $key => $name) {
            if (stripos($ua, $key) !== false) {
                $this->os = $name;
                break;
            }
        }
    }

    // ==================== GETTERS ====================

    /**
     * Get device type: mobile, tablet, desktop
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Get browser name
     */
    public function browser(): string
    {
        return $this->browser;
    }

    /**
     * Get operating system
     */
    public function os(): string
    {
        return $this->os;
    }

    /**
     * Get browser version
     */
    public function version(): string
    {
        return $this->version;
    }

    /**
     * Get User-Agent string
     */
    public function userAgent(): string
    {
        return $this->userAgent;
    }

    // ==================== HELPERS ====================

    public function isMobile(): bool
    {
        return $this->type === 'mobile';
    }

    public function isTablet(): bool
    {
        return $this->type === 'tablet';
    }

    public function isDesktop(): bool
    {
        return $this->type === 'desktop';
    }

    public function isChrome(): bool
    {
        return $this->browser === 'Chrome';
    }

    public function isFirefox(): bool
    {
        return $this->browser === 'Firefox';
    }

    public function isSafari(): bool
    {
        return $this->browser === 'Safari';
    }

    public function isEdge(): bool
    {
        return $this->browser === 'Edge';
    }

    public function isWindows(): bool
    {
        return str_contains($this->os, 'Windows');
    }

    public function isMacOS(): bool
    {
        return str_contains($this->os, 'macOS');
    }

    public function isLinux(): bool
    {
        return str_contains($this->os, 'Linux');
    }

    public function isIOS(): bool
    {
        return $this->os === 'iOS';
    }

    public function isAndroid(): bool
    {
        return $this->os === 'Android';
    }

    /**
     * Get all device info as array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'browser' => $this->browser,
            'browser_version' => $this->version,
            'os' => $this->os,
            'user_agent' => $this->userAgent,
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
