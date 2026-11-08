<?php

declare(strict_types=1);

namespace App\Observers;

/*
|--------------------------------------------------------------------------
| Example Observer
|
| Observers listen to model lifecycle events and run code automatically.
| Place observer files in: app/Observers/
|
| Register in bootstrap.php:
|   User::observe(UserObserver::class);
|--------------------------------------------------------------------------
*/

class UserObserver
{
    /**
     * Fires BEFORE creating a new record
     */
    public function creating(\App\User $user): void
    {
        // e.g. generate username from email
        if (!$user->username) {
            $user->username = strtolower(explode('@', $user->email)[0]);
        }

        \Footup\Log::info("Creating user: {$user->email}");
    }

    /**
     * Fires AFTER creating a new record
     */
    public function created(\App\User $user): void
    {
        // Send welcome email
        \Footup\Mail::to($user->email)
            ->subject('Welcome!')
            ->body('<h1>Welcome ' . $user->name . '!</h1>')
            ->send();

        \Footup\Log::info("User created: {$user->email}");
    }

    /**
     * Fires BEFORE updating an existing record
     */
    public function updating(\App\User $user): void
    {
        \Footup\Log::info("Updating user: {$user->email}");
    }

    /**
     * Fires AFTER updating an existing record
     */
    public function updated(\App\User $user): void
    {
        // Notify admins of critical changes
        if ($user->isDirty('role')) {
            \Footup\Mail::to('admin@app.com')
                ->subject('User role changed!')
                ->body("User {$user->email} role changed")
                ->send();
        }

        \Footup\Log::info("User updated: {$user->email}");
    }

    /**
     * Fires BEFORE deleting a record
     */
    public function deleting(\App\User $user): void
    {
        \Footup\Log::info("Deleting user: {$user->email}");
    }

    /**
     * Fires AFTER deleting a record (soft or hard)
     */
    public function deleted(\App\User $user): void
    {
        \Footup\Log::info("User deleted: {$user->email}");
    }
}
