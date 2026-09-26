<?php

namespace Footup\Engine;

/**
 * Module Manager
 * 
 * Manages complete module lifecycle with support for:
 * - Local modules (app/Modules/)
 * - Composer packages (vendor/author/module/)
 * - Dependencies and boot ordering
 * - Auto-discovery of models, middlewares, helpers, providers, routes, migrations
 * - Publishing module migrations and config to project directories
 */
class ModuleManager
{
  /** @var array<string, Module> */
    private static array $modules = [];
    private static array $booted = [];
    private static array $disabled = [];
    private static array $metadata = [];

    /**
     * Register a module
     */
    public static function register(Module $module): void
    {
        $name = $module->name();

        if (self::exists($name)) {
            throw new \RuntimeException("Module '{$name}' already registered");
        }

        self::$modules[$name] = $module;
        self::$metadata[$name] = [
            'helpers_loaded' => false,
            'routes_loaded' => false,
            'providers_booted' => false,
            'migrations_run' => false,
        ];
    }

    /**
     * Boot a specific module
     */
    public static function boot(string $name): void
    {
        if (!self::exists($name)) {
            throw new \RuntimeException("Module '{$name}' not registered");
        }

        if (self::isBooted($name)) {
            return;
        }

        if (self::isDisabled($name)) {
            return;
        }

        $module = self::$modules[$name];

        // Boot dependencies first
        self::bootDependencies($name);

        // PSR-4 autoloading handles models, middlewares, controllers, services, etc.
        // They're accessible via their full namespace (e.g., \App\Modules\Blog\Models\Post)

        // Load helpers (makes functions globally available)
        self::loadHelpers($name);

        // Boot providers (service registration)
        self::bootProviders($name);

        // Load routes (define URLs)
        self::loadRoutes($name);

        // Register CLI commands (auto-discover from Commands/)
        self::registerCommands($name);

        // Discover module-specific plugins
        self::discoverModulePlugins($name);

        // NOTE: Migrations are NOT auto-executed!
        // They must be published first via: php bin/footup module:publish BlogModule
        // Then run manually via: php bin/migrate
        // This ensures admin approval for database changes

        // Call module boot method
        $module->boot();

        self::$booted[$name] = true;
    }

    /**
     * Boot all modules in proper order
     */
    public static function bootAll(): void
    {
        $booted = [];
        $maxIterations = count(self::$modules) + 10;
        $iteration = 0;

        while (count($booted) < count(self::$modules) && $iteration < $maxIterations) {
            $iteration++;
            $made_progress = false;

            foreach (self::$modules as $name => $module) {
                if (isset($booted[$name]) || self::isDisabled($name)) {
                    continue;
                }

                $deps = $module->dependencies();
                $deps_ready = true;

                foreach ($deps as $dep) {
                    if (!self::isBooted($dep)) {
                        $deps_ready = false;
                        break;
                    }
                }

                if ($deps_ready) {
                    self::boot($name);
                    $booted[$name] = true;
                    $made_progress = true;
                }
            }

            if (!$made_progress && count($booted) < count(self::$modules)) {
                $remaining = array_keys(array_diff_key(self::$modules, $booted));
                throw new \RuntimeException(
                    "Circular dependency or missing dependency detected. " .
                    "Remaining modules: " . implode(', ', $remaining)
                );
            }
        }
    }

    /**
     * Boot module dependencies
     */
    private static function bootDependencies(string $name): void
    {
        $module = self::$modules[$name];
        $deps = $module->dependencies();

        foreach ($deps as $dep) {
            if (!self::exists($dep)) {
                throw new \RuntimeException(
                    "Module '{$name}' depends on '{$dep}' but it is not registered"
                );
            }

            if (!self::isBooted($dep)) {
                self::boot($dep);
            }
        }
    }

    /**
     * Load models from Models/ directory
     * 
     * Auto-discovers models using PSR-4 namespace.
     * Registers them globally for easy access.
     */
    /**
     * Load module helpers
     */
    private static function loadHelpers(string $name): void
    {
        if (self::$metadata[$name]['helpers_loaded'] ?? false) {
            return;
        }

        $module = self::$modules[$name];
        $helpersPath = $module->helpersPath();

        if (!is_dir($helpersPath)) {
            self::$metadata[$name]['helpers_loaded'] = true;
            return;
        }

        $files = scandir($helpersPath);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            require_once $helpersPath . '/' . $file;
        }

        self::$metadata[$name]['helpers_loaded'] = true;
    }

    /**
     * Boot module providers
     */
    private static function bootProviders(string $name): void
    {
        if (self::$metadata[$name]['providers_booted'] ?? false) {
            return;
        }

        $module = self::$modules[$name];
        $providersPath = $module->providersPath();

        if (!is_dir($providersPath)) {
            self::$metadata[$name]['providers_booted'] = true;
            return;
        }

        $namespace = $module->namespace();
        $files = scandir($providersPath);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $className = substr($file, 0, -4);
            $providerClass = $namespace . '\\Providers\\' . $className;

            if (class_exists($providerClass)) {
                $provider = new $providerClass();

                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
            }
        }

        self::$metadata[$name]['providers_booted'] = true;
    }

    /**
     * Load module routes
     */
    private static function loadRoutes(string $name): void
    {
        if (self::$metadata[$name]['routes_loaded'] ?? false) {
            return;
        }

        $module = self::$modules[$name];
        $routesFile = $module->routesPath() . '/routes.php';
        echo $module->basePath() . "\n";

        if (file_exists($routesFile)) {
            require $routesFile;
        }

        self::$metadata[$name]['routes_loaded'] = true;
    }

    /**
     * Discover module-specific plugins
     */
    private static function discoverModulePlugins(string $name): void
    {
        $module = self::$modules[$name];
        $pluginsPath = $module->pluginsPath();

        if (!is_dir($pluginsPath)) {
            return;
        }

        $namespace = $module->namespace();
        $files = scandir($pluginsPath);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $className = substr($file, 0, -4);
            $pluginClass = $namespace . '\\Plugins\\' . $className;

            if (class_exists($pluginClass) && is_subclass_of($pluginClass, Plugin::class)) {
                $plugin = new $pluginClass();
                PluginManager::register($plugin);
                PluginManager::boot($plugin->name());
            }
        }
    }

    /**
     * Run module migrations
     */
    /**
     * Register CLI commands from module's Commands/ directory
     * 
     * Auto-discovers commands extending Command class and registers them.
     * Usage: php bin/footup blog:create-post
     */
    private static function registerCommands(string $name): void
    {
        $module = self::$modules[$name];
        $commandsPath = $module->commandsPath();

        if (!is_dir($commandsPath)) {
            return;
        }

        $namespace = $module->namespace();
        $files = scandir($commandsPath);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.php') || $file === '.gitkeep') {
                continue;
            }

            $className = substr($file, 0, -4);
            $commandClass = $namespace . '\\Commands\\' . $className;

            if (class_exists($commandClass)) {
                try {
                    $command = new $commandClass();
                    
                    // Register command with Cli (adhocore/php-cli)
                    // This makes the command available via: php bin/footup command:name
                    app(\Footup\Cli\Konsole::class)->add($command);
                } catch (\Throwable $e) {
                    // Silently skip if command can't be instantiated
                    // CLI errors should be caught during command execution
                }
            }
        }
    }

    /**
     * Shutdown a specific module
     */
    public static function shutdown(string $name): void
    {
        if (!self::exists($name)) {
            return;
        }

        if (!self::isBooted($name)) {
            return;
        }

        $module = self::$modules[$name];
        $module->shutdown();
        unset(self::$booted[$name]);
    }

    /**
     * Shutdown all modules
     */
    public static function shutdownAll(): void
    {
        foreach (array_keys(self::$booted) as $name) {
            self::shutdown($name);
        }
    }

    /**
     * Check if module is registered
     */
    public static function exists(string $name): bool
    {
        return isset(self::$modules[$name]);
    }

    /**
     * Check if module is booted
     */
    public static function isBooted(string $name): bool
    {
        return isset(self::$booted[$name]);
    }

    /**
     * Enable a module
     */
    public static function enable(string $name): void
    {
        if (isset(self::$disabled[$name])) {
            unset(self::$disabled[$name]);
        }
    }

    /**
     * Disable a module
     */
    public static function disable(string $name): void
    {
        self::$disabled[$name] = true;
        self::shutdown($name);
    }

    /**
     * Check if module is disabled
     */
    public static function isDisabled(string $name): bool
    {
        return isset(self::$disabled[$name]);
    }

    /**
     * Get a module instance
     */
    public static function get(string $name): ?Module
    {
        return self::$modules[$name] ?? null;
    }

    /**
     * Get list of module names
     */
    public static function list(bool $booted = false): array
    {
        if ($booted) {
            return array_keys(self::$booted);
        }

        return array_keys(self::$modules);
    }

    /**
     * Get count of registered modules
     */
    public static function count(): int
    {
        return count(self::$modules);
    }

    /**
     * Get count of booted modules
     */
    public static function bootedCount(): int
    {
        return count(self::$booted);
    }

    /**
     * Get detailed info about all modules
     */
    public static function info(): array
    {
        $info = [];

        foreach (self::$modules as $name => $module) {
            $info[$name] = array_merge(
                $module->info(),
                [
                    'booted' => self::isBooted($name),
                    'disabled' => self::isDisabled($name),
                    'dependencies' => $module->dependencies(),
                    'metadata' => self::$metadata[$name] ?? [],
                ]
            );
        }

        return $info;
    }

    /**
     * Get module views path
     */
    public static function viewsPath(string $name): ?string
    {
        $module = self::get($name);

        if (!$module) {
            return null;
        }

        $path = $module->viewsPath();

        return is_dir($path) ? $path : null;
    }

    /**
     * Get module assets path
     */
    public static function assetsPath(string $name): ?string
    {
        $module = self::get($name);

        if (!$module) {
            return null;
        }

        $path = $module->assetsPath();

        return is_dir($path) ? $path : null;
    }

    /**
     * Get module config path
     */
    public static function configPath(string $name): ?string
    {
        $module = self::get($name);

        if (!$module) {
            return null;
        }

        $path = $module->configPath();

        return is_dir($path) ? $path : null;
    }

    /**
     * Clear all modules
     */
    public static function clear(): void
    {
        self::shutdownAll();
        self::$modules = [];
        self::$booted = [];
        self::$disabled = [];
        self::$metadata = [];
    }

    /**
     * Reload all modules
     */
    public static function reload(): void
    {
        self::shutdownAll();
        self::$booted = [];
        self::bootAll();
    }

    /**
     * Publish module assets (migrations, config)
     * 
     * Copies module migrations and config to project directories.
     * Allows local modification and versioning.
     * 
     * @param string $name Module name
     * @param string $basePath Project base path (default: current directory)
     * @return array Published files summary
     * 
     * @example
     * $published = ModuleManager::publish('blog');
     * // Copies to: database/migrations/ and config/
     */
    public static function publish(string $name, string $basePath = '.'): array
    {
        if (!self::exists($name)) {
            throw new \RuntimeException("Module '{$name}' not registered");
        }

        $module = self::$modules[$name];
        $published = [
            'migrations' => 0,
            'config' => 0,
            'paths' => [],
        ];

        // Publish migrations
        $migrationsCount = self::publishMigrations($name, $basePath);
        $published['migrations'] = $migrationsCount;

        if ($migrationsCount > 0) {
            $published['paths']['migrations'] = $basePath . '/database/migrations';
        }

        // Publish config
        $configCount = self::publishConfig($name, $basePath);
        $published['config'] = $configCount;

        if ($configCount > 0) {
            $published['paths']['config'] = $basePath . '/config';
        }

        return $published;
    }

    /**
     * Publish module migrations
     * 
     * Copies module migrations to project database/migrations/ directory.
     * Prevents duplication - only copies if file doesn't exist.
     * 
     * @param string $name Module name
     * @param string $basePath Project base path
     * @return int Number of migrations copied
     */
    private static function publishMigrations(string $name, string $basePath): int
    {
        $module = self::$modules[$name];
        $moduleMigrationsPath = $module->migrationsPath();

        if (!is_dir($moduleMigrationsPath)) {
            return 0;
        }

        $projectMigrationsPath = $basePath . '/database/migrations';

        // Create directory if needed
        if (!is_dir($projectMigrationsPath)) {
            mkdir($projectMigrationsPath, 0755, true);
        }

        $files = scandir($moduleMigrationsPath);
        $count = 0;

        foreach ($files as $file) {
            if (!str_ends_with($file, '.php') || $file === '.gitkeep') {
                continue;
            }

            $source = $moduleMigrationsPath . '/' . $file;
            $destination = $projectMigrationsPath . '/' . $name . '_' . $file;

            // Avoid overwriting existing files
            if (!file_exists($destination)) {
                copy($source, $destination);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Publish module config
     * 
     * Copies module config files directly to project config/ directory.
     * Prevents duplication - only copies if file doesn't exist.
     * 
     * @param string $name Module name
     * @param string $basePath Project base path
     * @return int Number of config files copied
     */
    private static function publishConfig(string $name, string $basePath): int
    {
        $module = self::$modules[$name];
        $moduleConfigPath = $module->configPath();

        if (!is_dir($moduleConfigPath)) {
            return 0;
        }

        $projectConfigPath = $basePath . '/config';

        // Create directory if needed
        if (!is_dir($projectConfigPath)) {
            mkdir($projectConfigPath, 0755, true);
        }

        $files = scandir($moduleConfigPath);
        $count = 0;

        foreach ($files as $file) {
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $source = $moduleConfigPath . '/' . $file;
            // Copy directly without prefixing (like Laravel)
            $destination = $projectConfigPath . '/' . $file;

            // Avoid overwriting existing files
            if (!file_exists($destination)) {
                copy($source, $destination);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Publish all modules
     * 
     * Publishes assets for all registered modules.
     * 
     * @param string $basePath Project base path
     * @return array Summary of published files
     */
    public static function publishAll(string $basePath = '.'): array
    {
        $summary = [
            'total_migrations' => 0,
            'total_config' => 0,
            'modules' => [],
        ];

        foreach (self::$modules as $name => $module) {
            $published = self::publish($name, $basePath);
            $summary['modules'][$name] = $published;
            $summary['total_migrations'] += $published['migrations'];
            $summary['total_config'] += $published['config'];
        }

        return $summary;
    }
}
