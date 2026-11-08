<?php

declare(strict_types=1);

namespace Footup\Sessions;

/*
|--------------------------------------------------------------------------
| Session — manage user sessions with driver support + Flash Data
|
| Drivers: file, database, cookie
| Configuration: SESSION_DRIVER env variable
|
| Usage:
|   Session::start();
|   Session::put('user_id', 123);
|   Session::get('user_id');
|   Session::has('user_id');
|   Session::forget('user_id');
|   Session::flush();
|
| Flash Data (temporary, persists 1 request):
|   Session::flash('success', 'User created!');
|   Session::flash('errors', ['email' => 'Required']);
|   $msg = Session::getFlash('success');           // Get and auto-delete
|   Session::hasFlash('errors');                    // Check existence
|   Session::keepFlash(['errors']);                 // Keep for another request
|   Session::reflash();                             // Keep all flash data
|
| Flash data lifecycle:
|   1. Set: flash('key', val) → stored as _flash:key
|   2. Request 1: Available via getFlash() or hasFlash()
|   3. On next request start: purgeFlash() moves old flash to garbage
|   4. Use keepFlash() to preserve across requests
|--------------------------------------------------------------------------
*/

class Session
{
    private static ?string $driver = null;
    private static ?string $sessionId = null;
    private static array $flashKeysToKeep = [];

    /**
     * Get configured session driver (from SESSION_DRIVER env)
     */
    private static function driver(): string
    {
        if (self::$driver === null) {
            self::$driver = env('SESSION_DRIVER', 'file');
        }
        return self::$driver;
    }

    /**
     * Get or create session ID
     */
    private static function sessionId(): string
    {
        if (self::$sessionId === null) {
            // Try to get from cookie
            if (isset($_COOKIE['SESSIONID'])) {
                self::$sessionId = $_COOKIE['SESSIONID'];
            } else {
                // Generate new session ID
                self::$sessionId = bin2hex(random_bytes(32));
                setcookie('SESSIONID', self::$sessionId, time() + 86400, '/', '/');
            }
        }
        return self::$sessionId;
    }

    public static function start(): void
    {
        $driver = self::driver();

        if ($driver === 'file') {
            self::startFile();
        } elseif ($driver === 'database') {
            self::startDatabase();
        } elseif ($driver === 'cookie') {
            self::startCookie();
        }

        // Purge old flash data on session start
        self::purgeFlash();
    }

    // ========== REGULAR SESSION DATA ==========

    public static function put(string $key, mixed $value): void
    {
        $driver = self::driver();

        match ($driver) {
            'file' => self::putFile($key, $value),
            'database' => self::putDatabase($key, $value),
            'cookie' => self::putCookie($key, $value),
            default => null,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $driver = self::driver();

        return match ($driver) {
            'file' => self::getFile($key, $default),
            'database' => self::getDatabase($key, $default),
            'cookie' => self::getCookie($key, $default),
            default => $default,
        };
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function forget(string $key): void
    {
        $driver = self::driver();

        match ($driver) {
            'file' => self::forgetFile($key),
            'database' => self::forgetDatabase($key),
            'cookie' => self::forgetCookie($key),
            default => null,
        };
    }

    public static function flush(): void
    {
        $driver = self::driver();

        match ($driver) {
            'file' => self::flushFile(),
            'database' => self::flushDatabase(),
            'cookie' => self::flushCookie(),
            default => null,
        };
    }

    // ========== FLASH DATA ==========

    /**
     * Set flash data (temporary, persists 1 request)
     */
    public static function flash(string $key, mixed $value): void
    {
        self::put("_flash:{$key}", $value);
    }

    /**
     * Get flash data and mark for deletion
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = self::get("_flash:{$key}", $default);

        // Mark old flash for deletion
        if ($value !== null) {
            self::forget("_flash:{$key}");
        }

        return $value;
    }

    /**
     * Check if flash data exists
     */
    public static function hasFlash(string $key): bool
    {
        return self::has("_flash:{$key}");
    }

    /**
     * Keep certain flash keys for another request
     * Usage: Session::keepFlash(['errors', 'success'])
     */
    public static function keepFlash(array $keys): void
    {
        foreach ($keys as $key) {
            if (self::has("_flash:{$key}")) {
                self::$flashKeysToKeep[] = $key;
            }
        }
    }

    /**
     * Keep all flash data for another request
     */
    public static function reflash(): void
    {
        $driver = self::driver();

        if ($driver === 'file') {
            $sessionFile = storage_path("sessions/{self::sessionId()}.json");
            if (file_exists($sessionFile)) {
                $data = json_decode(file_get_contents($sessionFile), true) ?? [];
                foreach (array_keys($data) as $key) {
                    if (str_starts_with($key, '_flash:')) {
                        self::$flashKeysToKeep[] = substr($key, 7);
                    }
                }
            }
        } elseif ($driver === 'database') {
            $connection = \Footup\Orm\DB::connection();
            $data = $connection->selectOne(
                'SELECT data FROM sessions WHERE id = ?',
                [self::sessionId()]
            );
            if ($data) {
                $decoded = json_decode($data['data'], true) ?? [];
                foreach (array_keys($decoded) as $key) {
                    if (str_starts_with($key, '_flash:')) {
                        self::$flashKeysToKeep[] = substr($key, 7);
                    }
                }
            }
        }
    }

    /**
     * Purge old flash data (called on Session::start())
     * Removes flash keys that weren't marked to keep
     */
    private static function purgeFlash(): void
    {
        $driver = self::driver();

        if ($driver === 'file') {
            self::purgeFlashFile();
        } elseif ($driver === 'database') {
            self::purgeFlashDatabase();
        }

        self::$flashKeysToKeep = [];
    }

    private static function purgeFlashFile(): void
    {
        $sessionFile = storage_path("sessions/" . self::sessionId() . ".json");
        if (!file_exists($sessionFile)) {
            return;
        }

        $data = json_decode(file_get_contents($sessionFile), true) ?? [];
        $keysToKeep = array_map(fn($k) => "_flash:{$k}", self::$flashKeysToKeep);

        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, '_flash:') && !in_array($key, $keysToKeep)) {
                unset($data[$key]);
            }
        }

        file_put_contents($sessionFile, json_encode($data));
    }

    private static function purgeFlashDatabase(): void
    {
        $connection = \Footup\Orm\DB::connection();
        $sessionId = self::sessionId();

        // Get current session data
        $session = $connection->selectOne(
            'SELECT data FROM sessions WHERE id = ?',
            [$sessionId]
        );

        if (!$session) {
            return;
        }

        $data = json_decode($session['data'], true) ?? [];
        $keysToKeep = array_map(fn($k) => "_flash:{$k}", self::$flashKeysToKeep);

        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, '_flash:') && !in_array($key, $keysToKeep)) {
                unset($data[$key]);
            }
        }

        $connection->table('sessions')
            ->where('id', $sessionId)
            ->update(['data' => json_encode($data)]);
    }

    // ========== FILE DRIVER ==========

    private static function startFile(): void
    {
        $sessionFile = storage_path("sessions/" . self::sessionId() . ".json");
        if (!file_exists($sessionFile)) {
            @mkdir(dirname($sessionFile), 0755, true);
            file_put_contents($sessionFile, json_encode([]));
        }
    }

    private static function getFile(string $key, mixed $default = null): mixed
    {
        $sessionFile = storage_path("sessions/" . self::sessionId() . ".json");
        if (!file_exists($sessionFile)) {
            return $default;
        }

        $data = json_decode(file_get_contents($sessionFile), true) ?? [];
        return $data[$key] ?? $default;
    }

    private static function putFile(string $key, mixed $value): void
    {
        $sessionFile = storage_path("sessions/" . self::sessionId() . ".json");
        $data = json_decode(file_get_contents($sessionFile), true) ?? [];
        $data[$key] = $value;
        file_put_contents($sessionFile, json_encode($data));
    }

    private static function forgetFile(string $key): void
    {
        $sessionFile = storage_path("sessions/" . self::sessionId() . ".json");
        if (!file_exists($sessionFile)) {
            return;
        }

        $data = json_decode(file_get_contents($sessionFile), true) ?? [];
        unset($data[$key]);
        file_put_contents($sessionFile, json_encode($data));
    }

    private static function flushFile(): void
    {
        $sessionFile = storage_path("sessions/" . self::sessionId() . ".json");
        file_put_contents($sessionFile, json_encode([]));
    }

    // ========== DATABASE DRIVER ==========

    private static function startDatabase(): void
    {
        $connection = \Footup\Orm\DB::connection();
        $sessionId = self::sessionId();

        $session = $connection->selectOne(
            'SELECT id FROM sessions WHERE id = ?',
            [$sessionId]
        );

        if (!$session) {
            $connection->insertGetId(
                'INSERT INTO sessions (id, data, expires_at) VALUES (?, ?, ?)',
                [$sessionId, json_encode([]), date('Y-m-d H:i:s', time() + 86400)],
                'id'
            );
        }
    }

    private static function getDatabase(string $key, mixed $default = null): mixed
    {
        $connection = \Footup\Orm\DB::connection();
        $session = $connection->selectOne(
            'SELECT data FROM sessions WHERE id = ?',
            [self::sessionId()]
        );

        if (!$session) {
            return $default;
        }

        $data = json_decode($session['data'], true) ?? [];
        return $data[$key] ?? $default;
    }

    private static function putDatabase(string $key, mixed $value): void
    {
        $connection = \Footup\Orm\DB::connection();
        $sessionId = self::sessionId();

        $session = $connection->selectOne(
            'SELECT data FROM sessions WHERE id = ?',
            [$sessionId]
        );

        $data = json_decode($session['data'] ?? '{}', true) ?? [];
        $data[$key] = $value;

        $connection->table('sessions')
            ->where('id', $sessionId)
            ->update(['data' => json_encode($data)]);
    }

    private static function forgetDatabase(string $key): void
    {
        $connection = \Footup\Orm\DB::connection();
        $sessionId = self::sessionId();

        $session = $connection->selectOne(
            'SELECT data FROM sessions WHERE id = ?',
            [$sessionId]
        );

        if (!$session) {
            return;
        }

        $data = json_decode($session['data'], true) ?? [];
        unset($data[$key]);

        $connection->table('sessions')
            ->where('id', $sessionId)
            ->update(['data' => json_encode($data)]);
    }

    private static function flushDatabase(): void
    {
        $connection = \Footup\Orm\DB::connection();

        $connection->table('sessions')
            ->where('id', self::sessionId())
            ->update(['data' => json_encode([])]);
    }

    private static function cleanupDatabase(): void
    {
        $connection = \Footup\Orm\DB::connection();

        // Delete expired sessions
        $connection->table('sessions')
            ->where('expires_at', '<', date('Y-m-d H:i:s'))
            ->delete();
    }

    // ========== COOKIE DRIVER ==========

    private static function startCookie(): void
    {
        // Cookies are handled automatically by HTTP
    }

    private static function putCookie(string $key, mixed $value): void
    {
        $encrypted = base64_encode(json_encode($value));
        setcookie($key, $encrypted, time() + 3600, '/', '/');
    }

    private static function getCookie(string $key, mixed $default = null): mixed
    {
        if (!isset($_COOKIE[$key])) {
            return $default;
        }

        return json_decode(base64_decode($_COOKIE[$key]), true) ?? $default;
    }

    private static function forgetCookie(string $key): void
    {
        setcookie($key, '', time() - 3600, '/', '/');
        unset($_COOKIE[$key]);
    }

    private static function flushCookie(): void
    {
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, '', time() - 3600, '/', '/');
        }
        $_COOKIE = [];
    }
}
