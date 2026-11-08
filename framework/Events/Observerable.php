<?php

declare(strict_types=1);

namespace Footup\Events;

/*
|--------------------------------------------------------------------------
| Observer trait — auto-attach methods to model lifecycle events
|
| Usage:
|   class UserObserver {
|       public function creating(User $user) { }
|       public function created(User $user) { }
|       public function updating(User $user) { }
|       public function updated(User $user) { }
|       public function deleting(User $user) { }
|       public function deleted(User $user) { }
|   }
|
|   // In bootstrap:
|   User::observe(UserObserver::class);
|
|   // OR on instance:
|   $user->observe(new UserObserver());
|--------------------------------------------------------------------------
*/

trait Observerable
{
    protected static array $observers = [];

    public static function observe(string|object $observer): void
    {
        $class = is_string($observer) ? app($observer) : $observer;
        $modelName = static::class;

        if (!isset(self::$observers[$modelName])) {
            self::$observers[$modelName] = [];
        }

        self::$observers[$modelName][] = $class;

        // Register event listeners from observer methods
        foreach (['creating', 'created', 'updating', 'updated', 'deleting', 'deleted', 'forceDeleting', 'forceDeleted', 'restoring', 'restored'] as $event) {
            if (method_exists($class, $event)) {
                Event::listen($modelName . ':' . $event, fn($model) => $class->$event($model));
            }
        }
    }
}
