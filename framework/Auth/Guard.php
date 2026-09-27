<?php

declare(strict_types=1);

namespace Footup\Auth;

use App\User;
use Footup\Sessions\Session;

/*
|--------------------------------------------------------------------------
| Guard — Authentication guard for session-based auth
|
| Usage:
|   Auth::attempt(['email' => $email, 'password' => $password]);
|   Auth::login($user);
|   Auth::logout();
|   Auth::user();
|   Auth::check();
|--------------------------------------------------------------------------
*/

class Guard
{
    private ?User $user = null;

    public function __construct(
        private Session $session,
    ) {
        $this->loadUserFromSession();
    }

    /**
     * Attempt to authenticate user
     */
    public function attempt(array $credentials): bool
    {
        $user = User::firstWhere('email', $credentials['email'] ?? null);

        if (!$user || !$user->matchPassword($credentials['password'] ?? '')) {
            return false;
        }

        $this->login($user);
        return true;
    }

    /**
     * Login a user
     */
    public function login(User $user): void
    {
        $this->user = $user;
        $this->session->put('auth_id', $user->id);
        $this->session->put('auth_user', serialize($user));
    }

    /**
     * Logout the current user
     */
    public function logout(): void
    {
        $this->user = null;
        $this->session->forget('auth_id');
        $this->session->forget('auth_user');
    }

    /**
     * Get authenticated user
     */
    public function user(): ?User
    {
        return $this->user;
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return $this->user !== null;
    }

    /**
     * Check if user is not authenticated
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get user ID
     */
    public function id(): ?int
    {
        return $this->user?->id;
    }

    /**
     * Load user from session
     */
    private function loadUserFromSession(): void
    {
        $userId = $this->session->get('auth_id');

        if ($userId) {
            $this->user = User::find($userId);
        }
    }

    /**
     * Check if user has permission/role
     */
    public function can(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }

        // Simple permission check (expand as needed)
        if (method_exists($this->user, 'hasPermission')) {
            return $this->user->hasPermission($permission);
        }

        return false;
    }

    /**
     * Verify user's password
     */
    public function verify(string $password): bool
    {
        if (!$this->check()) {
            return false;
        }

        return $this->user->matchPassword($password);
    }
}
