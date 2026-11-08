<?php

declare(strict_types=1);

namespace Footup\Events;

/*
|--------------------------------------------------------------------------
| Event — minimal event listener system for models and app events
|
| Usage:
|   Event::listen('user.created', fn($user) => Mail::send(...))
|   Event::dispatch('user.created', $user)
|
|   // In Model observers:
|   Event::dispatch('model:creating', $this)
|   Event::dispatch('model:created', $this)
|--------------------------------------------------------------------------
*/

class Event
{
    protected static array $listeners = [];

    public static function listen(string $event, callable $callback): void
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }

        self::$listeners[$event][] = $callback;
    }

    public static function dispatch(string $event, mixed ...$payload): void
    {
        foreach (self::$listeners[$event] ?? [] as $listener) {
            call_user_func($listener, ...$payload);
        }
    }

    public static function forget(string $event): void
    {
        unset(self::$listeners[$event]);
    }

    public static function flush(): void
    {
        self::$listeners = [];
    }
}
