<?php

declare(strict_types=1);

namespace Footup\Facades;

use Footup\Auth\Guard;

/*
|--------------------------------------------------------------------------
| Auth Facade — Easy access to authentication
|
| Usage:
|   use Footup\Facades\Auth;
|
|   Auth::attempt(['email' => $email, 'password' => $password]);
|   Auth::login($user);
|   Auth::logout();
|   Auth::user();
|   Auth::check();
|
| Or use global helper:
|   auth()->attempt([...]);
|   auth()->user();
|   auth()->check();
|--------------------------------------------------------------------------
*/

class Auth
{
    private static ?Guard $guard = null;

    /**
     * Set the guard instance
     */
    public static function setGuard(Guard $guard): void
    {
        self::$guard = $guard;
    }

    /**
     * Get the guard instance
     */
    private static function guard(): Guard
    {
        if (self::$guard === null) {
            throw new \Exception('Auth guard not initialized');
        }
        return self::$guard;
    }

    /**
     * Attempt to authenticate user
     */
    public static function attempt(array $credentials): bool
    {
        return self::guard()->attempt($credentials);
    }

    /**
     * Login a user
     */
    public static function login($user): void
    {
        self::guard()->login($user);
    }

    /**
     * Logout the current user
     */
    public static function logout(): void
    {
        self::guard()->logout();
    }

    /**
     * Get authenticated user
     */
    public static function user()
    {
        return self::guard()->user();
    }

    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        return self::guard()->check();
    }

    /**
     * Check if user is not authenticated
     */
    public static function guest(): bool
    {
        return self::guard()->guest();
    }

    /**
     * Get user ID
     */
    public static function id(): ?int
    {
        return self::guard()->id();
    }

    /**
     * Check if user has permission
     */
    public static function can(string $permission): bool
    {
        return self::guard()->can($permission);
    }

    /**
     * Verify user's password
     */
    public static function verify(string $password): bool
    {
        return self::guard()->verify($password);
    }
}
