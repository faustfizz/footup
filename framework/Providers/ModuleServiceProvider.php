<?php

namespace Footup\Providers;

use Footup\Engine\ModuleManager;

/**
 * Module Service Provider
 * 
 * Registers and boots all modules.
 * 
 * Configuration: config/modules.php
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register modules
     */
    public function register(): void
    {
        // Modules can be registered here if needed
        // Or use auto-discovery/configuration
    }

    /**
     * Boot modules
     */
    public function boot(): void
    {
        // Register all configured modules
        $this->registerModules();

        // Load module config BEFORE booting
        // Modules may need config during their boot() method
        $this->loadConfig();

        // Load module views BEFORE booting
        // Modules may need views during their boot() method
        $this->loadViews();

        // Boot all modules (now with config and views available)
        ModuleManager::bootAll();

        // Publish module assets (last, not critical for functionality)
        $this->publishAssets();
    }

    /**
     * Register modules from configuration
     */
    protected function registerModules(): void
    {
        $modules = config('modules.list', []);

        foreach ($modules as $moduleClass) {
            if (!class_exists($moduleClass)) {
                continue;
            }

            $module = new $moduleClass();
            ModuleManager::register($module);
        }
    }

    /**
     * Publish module assets to public directory
     */
    protected function publishAssets(): void
    {
        foreach (ModuleManager::list() as $name) {
            $assetsPath = ModuleManager::assetsPath($name);

            if (!$assetsPath) {
                continue;
            }

            // Copy assets to public/modules/{module-name}/
            $publicPath = public_path("modules/{$name}");

            if (!is_dir($publicPath)) {
                mkdir($publicPath, 0755, true);
            }

            $this->copyAssets($assetsPath, $publicPath);
        }
    }

    /**
     * Copy assets directory recursively
     * 
     * @param string $source Source directory
     * @param string $dest Destination directory
     * @return void
     */
    private function copyAssets(string $source, string $dest): void
    {
        if (!is_dir($source)) {
            return;
        }

        $files = scandir($source);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcFile = $source . '/' . $file;
            $destFile = $dest . '/' . $file;

            if (is_dir($srcFile)) {
                if (!is_dir($destFile)) {
                    mkdir($destFile, 0755, true);
                }
                $this->copyAssets($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
    }
    
    protected function loadViews(): void
    {
        if (!function_exists('view')) {
            return;
        }

        $engine = \Footup\View\TemplateServiceProvider::engine();

        foreach (ModuleManager::list() as $name) {
            $viewsPath = ModuleManager::viewsPath($name);

            if (!$viewsPath || !is_dir($viewsPath)) {
                continue;
            }

            // Register module namespace with engine
            // Allows: view('blog::posts.index')
            $engine->registerNamespace($name, $viewsPath);
        }
    }

    /**
     * Load and merge module configuration
     */
    protected function loadConfig(): void
    {
        if (!function_exists('app')) {
            return;
        }

        $moduleConfigs = [];

        foreach (ModuleManager::list() as $name) {
            $configPath = ModuleManager::configPath($name);

            if (!$configPath) {
                continue;
            }

            $files = scandir($configPath);

            foreach ($files as $file) {
                if (!str_ends_with($file, '.php')) {
                    continue;
                }

                $configName = substr($file, 0, -4);
                $configFile = $configPath . '/' . $file;
                $config = require $configFile;

                // Build modules config array locally
                if (is_array($config)) {
                    if (!isset($moduleConfigs[$name])) {
                        $moduleConfigs[$name] = [];
                    }
                    if (!isset($moduleConfigs[$name][$configName])) {
                        $moduleConfigs[$name][$configName] = [];
                    }
                    $moduleConfigs[$name][$configName] = array_merge(
                        $moduleConfigs[$name][$configName],
                        $config
                    );
                }
            }
        }

        /** @var array $existingConfig */
        $existingConfig = app('config') ?? [];
        $existingConfig['modules'] = array_merge(
            $existingConfig['modules'] ?? [],
            $moduleConfigs
        );
        app()->instance('config', $existingConfig);
    }
}
