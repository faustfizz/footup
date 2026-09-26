<?php

namespace Footup\Engine;

class PluginManager
{
    private static array $plugins = [];
    private static array $booted = [];

    public static function register(Plugin $plugin): void
    {
        self::$plugins[$plugin->name()] = $plugin;
    }

    public static function boot(string $name): void
    {
        if (isset(self::$plugins[$name]) && !isset(self::$booted[$name])) {
            self::$plugins[$name]->boot();
            self::$booted[$name] = true;
        }
    }
}
