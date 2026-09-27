<?php

declare(strict_types=1);

namespace Footup\Auth;

use Footup\Hashing\Hash;

/*
|--------------------------------------------------------------------------
| Auth — Authentication system
|
| Usage:
|   Auth::attempt(['email' => 'john@example.com', 'password' => 'secret']);
|   Auth::user();
|   Auth::check();
|   Auth::logout();
|
|   Auth::create([
|       'name' => 'John Doe',
|       'email' => 'john@example.com',
|       'password' => Hash::make('secret'),
|   ]);
|--------------------------------------------------------------------------
*/

class Auth
{
    private static ?object $user = null;
    private static string $userModel = Authenticable::class;
    private static string $sessionKey = 'ft_user_id';

    /**
     * Attempt to authenticate user with credentials
     */
    public static function attempt(array $credentials): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return false;
        }

        $userClass = self::$userModel;
        $user = $userClass::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        if (!Hash::check($password, $user->password)) {
            return false;
        }

        self::login($user);
        return true;
    }

    /**
     * Login a user
     */
    public static function login(object $user): void
    {
        session_start();
        $_SESSION[self::$sessionKey] = $user->id;
        self::$user = $user;
    }

    /**
     * Logout the current user
     */
    public static function logout(): void
    {
        session_start();
        unset($_SESSION[self::$sessionKey]);
        self::$user = null;
        session_destroy();
    }

    /**
     * Get the current user
     */
    public static function user(): ?object
    {
        if (self::$user) {
            return self::$user;
        }

        session_start();

        if (!isset($_SESSION[self::$sessionKey])) {
            return null;
        }

        $userClass = self::$userModel;
        $userId = $_SESSION[self::$sessionKey];

        self::$user = $userClass::find($userId);
        return self::$user;
    }

    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Check if user is NOT authenticated
     */
    public static function guest(): bool
    {
        return !self::check();
    }

    /**
     * Get user ID
     */
    public static function id(): ?int
    {
        return self::user()?->id;
    }

    /**
     * Create a new user
     */
    public static function create(array $data): object
    {
        $userClass = self::$userModel;
        return $userClass::create($data);
    }

    /**
     * Set the user model class
     */
    public static function setUserModel(string $model): void
    {
        self::$userModel = $model;
    }

    /**
     * Set the session key
     */
    public static function setSessionKey(string $key): void
    {
        self::$sessionKey = $key;
    }

    public function __call($method, $args)
    {
      if (method_exists(__CLASS__, $method)) {
          static::$method(...$args);
      }
      return null;
    }
}