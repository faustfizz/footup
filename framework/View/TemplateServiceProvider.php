<?php

namespace Footup\View;

/**
 * Template Service Provider
 * 
 * Registers template engine services in Footup container
 */
class TemplateServiceProvider
{
    /**
     * @var Engine Template engine instance
     */
    private static ?Engine $engine = null;

    /**
     * @var LayoutManager Layout manager instance
     */
    private static ?LayoutManager $layoutManager = null;

    /**
     * Register template engine services
     * 
     * @param array $config Template configuration
     * @return void
     */
    public static function register(array $config = []): void
    {
        $config = array_merge(self::getDefaultConfig(), $config);

        // Determine which engine to use
        $engineName = $config['engine'] ?? 'footup';
        $engineConfigs = $config['engines'] ?? [];
        
        if (!isset($engineConfigs[$engineName])) {
            throw new \RuntimeException("Template engine '{$engineName}' not configured in config/view.php");
        }

        $engineConfig = $engineConfigs[$engineName];
        
        // Create engine instance
        if (isset($engineConfig['class'])) {
            // Custom engine class
            $className = $engineConfig['class'];
            if (!class_exists($className)) {
                throw new \RuntimeException("Template engine class '{$className}' not found");
            }
            self::$engine = new $className($engineConfig);
        } else {
            // Built-in Footup Engine
            self::$engine = new Engine(
                $engineConfig['paths'] ?? [],
                $engineConfig['compiled'] ?? 'storage/compiled/views',
                $engineConfig['extensions'] ?? ['.ftup', '.kick']
            );
        }

        // Verify engine implements interface
        if (!self::$engine instanceof TemplateEngineInterface) {
            throw new \RuntimeException("Template engine must implement TemplateEngineInterface");
        }

        // Only Footup Engine has these methods - skip for other engines
        if (self::$engine instanceof Engine) {
            // Set production mode
            if ($engineConfig['production'] ?? false) {
                self::$engine->setProductionMode(true);
            }

            // Set attribute prefixes
            if (isset($engineConfig['attribute_prefixes'])) {
                self::$engine->setAttributePrefixes($engineConfig['attribute_prefixes']);
            }

            // Create layout manager (Footup-specific)
            self::$layoutManager = new LayoutManager(self::$engine);

            // Initialize filter executor (Footup-specific)
            FilterExecutor::init(self::$engine->getFilters());
        }

        // Register template cache prune command (if in CLI)
        if (php_sapi_name() === 'cli') {
            self::registerCLICommands();
        }
    }

    /**
     * Get template engine instance
     * 
     * @return Engine
     */
    public static function engine(): Engine
    {
        if (!self::$engine) {
            self::register();
        }

        return self::$engine;
    }

    /**
     * Get layout manager instance
     * 
     * @return LayoutManager
     */
    public static function layoutManager(): LayoutManager
    {
        if (!self::$layoutManager) {
            self::register();
        }

        return self::$layoutManager;
    }

    /**
     * Get default configuration
     * 
     * @return array Default config
     */
    private static function getDefaultConfig(): array
    {
        return [
            'engine' => 'footup',
            'engines' => [
                'footup' => [
                    'extensions' => ['.ftup', '.kick'],
                    'paths' => [
                        ui_path('/views'),
                        'app/Modules/*/resources/views',
                    ],
                    'compiled' => storage_path('compiled/views'),
                    'production' => false,
                    'attribute_prefixes' => ['ui:', ':'],
                ],
            ],
            'auto_escape' => true,
        ];
    }

    /**
     * Register CLI commands
     * 
     * @return void
     */
    private static function registerCLICommands(): void
    {
        // Would register with CLI system
        // Example: cache:clear:views, cache:prune:views
    }

    /**
     * Render a template
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string Rendered output
     */
    public static function render(string $template, array $data = []): string
    {
        return self::engine()->render($template, $data);
    }

    /**
     * Share data with all templates
     * 
     * @param string|array $key Key or array
     * @param mixed $value Value
     * @return void
     */
    public static function share($key, $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                self::engine()->share($k, $v);
            }
        } else {
            self::engine()->share($key, $value);
        }
    }

    /**
     * Register a custom filter
     * 
     * @param string $name Filter name
     * @param callable $callback Filter function
     * @return void
     */
    public static function filter(string $name, callable $callback): void
    {
        self::engine()->filter($name, $callback);
        FilterExecutor::register($name, $callback);
    }

    /**
     * Register a custom attribute directive
     * 
     * @param string $name Directive name
     * @param callable $callback Handler
     * @return void
     */
    public static function attribute(string $name, callable $callback): void
    {
        self::engine()->attribute($name, $callback);
    }

    /**
     * Clear template cache
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        self::engine()->clearCache();
    }

    /**
     * Get cache statistics
     * 
     * @return array Statistics
     */
    public static function cacheStats(): array
    {
        $cache = new Cache(
            config('view.compiled', 'storage/compiled/views')
        );
        return $cache->getStats();
    }
}
