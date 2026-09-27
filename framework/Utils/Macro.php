<?php

declare(strict_types=1);

namespace Footup\Utils;

/*
|--------------------------------------------------------------------------
| Macro — Add custom methods to classes dynamically
|
| Usage:
|   Request::macro('isAdmin', function() {
|       return $this->user()?->isAdmin() ?? false;
|   });
|
|   $request->isAdmin();  // Works!
|--------------------------------------------------------------------------
*/

trait Macro
{
    /**
     * Registered macros
     */
    protected static array $macros = [];

    /**
     * Register a macro
     */
    public static function macro(string $name, callable $callback): void
    {
        static::$macros[static::class][$name] = $callback;
    }

    /**
     * Register multiple macros
     */
    public static function macros(array $macros): void
    {
        foreach ($macros as $name => $callback) {
            static::macro($name, $callback);
        }
    }

    /**
     * Check if macro exists
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[static::class][$name]);
    }

    /**
     * Get macro
     */
    public static function getMacro(string $name): ?callable
    {
        return static::$macros[static::class][$name] ?? null;
    }

    /**
     * Call macro
     */
    public function __call(string $name, array $arguments)
    {
        $macro = static::getMacro($name);

        if (!$macro) {
            throw new \BadMethodCallException(
                "Call to undefined method " . static::class . "::{$name}()"
            );
        }

        return $macro(...$arguments)->call($this);
    }

    /**
     * Static macro call
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $macro = static::getMacro($name);

        if (!$macro) {
            throw new \BadMethodCallException(
                "Call to undefined static method " . static::class . "::{$name}()"
            );
        }

        return $macro(...$arguments);
    }
}
