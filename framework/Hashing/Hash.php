<?php

declare(strict_types=1);

namespace Footup\Hashing;

/*
|--------------------------------------------------------------------------
| Hash — password hashing and verification
|
| Usage:
|   $hashed = Hash::make('my-password');
|   if (Hash::check('my-password', $hashed)) { ... }
|
|   // In models:
|   $user->password = Hash::make($request->input('password'));
|   if (Hash::check($input, $user->password)) { /* valid 
|--------------------------------------------------------------------------
*/

class Hash
{
    protected static int $cost = 10;
    protected static string $algorithm = PASSWORD_BCRYPT;

    /**
     * Hash a password using bcrypt
     */
    public static function make(string $password): string
    {
        return password_hash($password, self::$algorithm, ['cost' => self::$cost]);
    }

    /**
     * Verify a password against a hash
     */
    public static function check(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a hash needs to be rehashed (cost changed, etc.)
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::$algorithm, ['cost' => self::$cost]);
    }

    /**
     * Set the bcrypt cost factor (4-31, default 10)
     * Higher = slower = more secure, but slower
     */
    public static function setCost(int $cost): void
    {
        self::$cost = max(4, min(31, $cost));
    }

    /**
     * Get the current cost factor
     */
    public static function getCost(): int
    {
        return self::$cost;
    }
}
