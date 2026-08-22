<?php

declare(strict_types=1);

namespace Footup\Auth;

/*
|--------------------------------------------------------------------------
| Auth — session + JWT authentication
|
| Usage:
|   Auth::login($user); // store in session
|   Auth::user();       // get current user
|   Auth::logout();
|   Auth::token($user); // generate JWT
|   Auth::fromToken($token); // decode JWT
|--------------------------------------------------------------------------
*/

class Auth
{
    protected static ?object $user = null;
    protected static string $jwtSecret = '';

    public static function setSecret(string $secret): void
    {
        self::$jwtSecret = $secret;
    }

    public static function login(object $user): void
    {
        session_start();
        $_SESSION['user_id'] = $user->id ?? null;
        self::$user = $user;
    }

    public static function logout(): void
    {
        session_start();
        unset($_SESSION['user_id']);
        self::$user = null;
    }

    public static function user(): ?User
    {
        if (self::$user) {
            return self::$user;
        }

        session_start();
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId && class_exists(User::class)) {
            self::$user = User::find($userId);
        }

        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function token(object $user): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['sub' => $user->id ?? null, 'iat' => time()]);

        $headerB64 = self::base64UrlEncode($header);
        $payloadB64 = self::base64UrlEncode($payload);
        $signature = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", self::$jwtSecret, true);
        $signatureB64 = self::base64UrlEncode($signature);

        return "{$headerB64}.{$payloadB64}.{$signatureB64}";
    }

    public static function fromToken(string $token): ?object
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;
        $signature = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", self::$jwtSecret, true);

        if (self::base64UrlEncode($signature) !== $signatureB64) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadB64), true);

        if (! $payload || ! isset($payload['sub'])) {
            return null;
        }

        if (class_exists(User::class)) {
            return User::find($payload['sub']);
        }

        return null;
    }

    protected static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}
