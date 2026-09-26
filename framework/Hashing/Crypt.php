<?php

declare(strict_types=1);

namespace Footup\Hashing;

/*
|--------------------------------------------------------------------------
| Crypt — encryption and decryption using OpenSSL
|
| Usage:
|   $encrypted = Crypt::encrypt('secret data');
|   $decrypted = Crypt::decrypt($encrypted);
|
| Configuration via .env:
|   APP_KEY=base64:your-32-byte-key-here
|--------------------------------------------------------------------------
*/

class Crypt
{
    protected static ?string $key = null;
    protected static string $cipher = 'AES-256-CBC';

    /**
     * Initialize with key from .env
     */
    public static function setKey(string $key): void
    {
        // Handle base64: prefix
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (strlen($key) !== 32) {
            throw new \RuntimeException('Encryption key must be 32 bytes (256-bit). Generate with: php bin/console.php key:generate');
        }

        self::$key = $key;
    }

    /**
     * Encrypt a string
     */
    public static function encrypt(string $value): string
    {
        if (!self::$key) {
            self::setKey(env('APP_KEY', ''));
        }

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher));
        $encrypted = openssl_encrypt($value, self::$cipher, self::$key, 0, $iv);

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // Return: iv + encrypted (base64 encoded)
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a string
     */
    public static function decrypt(string $payload): string
    {
        if (!self::$key) {
            self::setKey(env('APP_KEY', ''));
        }

        $data = base64_decode($payload, true);

        if ($data === false) {
            throw new \RuntimeException('Invalid encryption payload');
        }

        $iv_length = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);

        $decrypted = openssl_decrypt($encrypted, self::$cipher, self::$key, 0, $iv);

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }
}
