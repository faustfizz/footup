<?php

namespace Footup\Engine;

/**
 * Module Base Class
 * 
 * Base class for all modules. Modules are self-contained
 * framework extensions with views, assets, routes, helpers,
 * providers, migrations, models, and middlewares.
 * 
 * Works both as local modules (app/Modules/) and
 * Composer packages (vendor/author/module-name/).
 */
abstract class Module
{
  /**
   * Module name (lowercase identifier)
   * 
   * @example 'blog', 'auth', 'comments'
   */
  abstract public function name(): string;

  /**
   * Boot the module
   * 
   * Called when module is booted. At this point:
   * - Dependencies are booted
   * - Helpers are loaded
   * - Providers are booted
   * - Routes are loaded
   * - Models are discovered
   * - Middlewares are registered
   * - Migrations are run
   * - Module plugins are discovered
   */
  abstract public function boot(): void;

  /**
   * Module version
   */
  public function version(): string
  {
    return '1.0.0';
  }

  /**
   * Module description
   */
  public function description(): string
  {
    return '';
  }

  /**
   * Module dependencies
   * 
   * List of module names this module depends on.
   * They will be booted before this module.
   * 
   * @return array<string> Module names
   */
  public function dependencies(): array
  {
    return [];
  }

  /**
   * Shutdown the module
   * 
   * Called when module is shut down.
   * Clean up resources, close connections, etc.
   */
  public function shutdown(): void {}

  /**
   * Get module base path
   * 
   * Intelligently detects module root directory from class file location.
   * Works for any directory structure:
   * 
   * Local modules:
   *   app/Modules/BlogModule/BlogModule.php → app/Modules/BlogModule/
   *   app/Modules/Blog/BlogModule.php → app/Modules/Blog/
   *
   * Composer packages:
   *   vendor/author/blog/BlogModule.php → vendor/author/blog/
   *   vendor/author/blog/src/BlogModule.php → vendor/author/blog/
   *   vendor/author/blog/src/Module/BlogModule.php → vendor/author/blog/
   * 
   * Strategy:
   * 1. If in src/, go up to package root
   * 2. If parent directory contains composer.json, use that
   * 3. If in app/Modules/, return that directory
   * 4. Otherwise, look for markers (routes.php, config/, database/)
   * 5. Fallback: parent directory
   * 
   * @return string Absolute path to module root
   */
  public function basePath(): string
  {
    $reflector = new \ReflectionClass($this);
    $filePath = $reflector->getFileName();
    $classDir = dirname($filePath);

    // Strategy 1: Composer package in vendor/ with src/ directory
    $parts = explode(DIRECTORY_SEPARATOR, $classDir);
    if (in_array('src', $parts)) {
      $srcIndex = array_search('src', $parts);
      if (isset($parts[$srcIndex - 2]) && $parts[$srcIndex - 2] === 'vendor') {
        return implode(DIRECTORY_SEPARATOR, array_slice($parts, 0, $srcIndex));
      }
    }

    // Strategy 2: Local module in app/Modules/
    // Pattern: app/Modules/Blog/BlogModule.php
    if (str_contains($classDir, '/app/Modules') || str_contains($classDir, '\\app\\Modules')) {
      // The module class is directly in the module directory
      // app/Modules/Blog/BlogModule.php → return app/Modules/Blog
      return $classDir;
    }

    // Strategy 3: Check for module marker files (routes.php, config/, etc.)
    if (
      file_exists($classDir . '/routes.php') ||
      is_dir($classDir . '/config') ||
      is_dir($classDir . '/resources')
    ) {
      return $classDir;
    }

    // Strategy 4: Fallback - parent directory
    return dirname($classDir);
  }

  /**
   * Check if directory has typical module markers
   */
  private function hasModuleMarkers(string $dir): bool
  {
    return file_exists($dir . '/routes.php') ||
      is_dir($dir . '/config') ||
      is_dir($dir . '/database') ||
      is_dir($dir . '/Models') ||
      is_dir($dir . '/Views') ||
      is_dir($dir . '/Helpers') ||
      is_dir($dir . '/Providers');
  }

  /**
   * Get module namespace
   * 
   * Extracts the namespace from the module class.
   * 
   * @example 'App\Modules', 'Acme\BlogModule', 'Vendor\Package'
   */
  public function namespace(): string
  {
    return substr(static::class, 0, strrpos(static::class, '\\'));
  }

  /**
   * Get views directory path
   * 
   * @return string Path to Views directory
   */
  public function viewsPath(): string
  {
    return $this->basePath() . '/Views';
  }

  /**
   * Get assets directory path
   * 
   * @return string Path to Assets directory
   */
  public function assetsPath(): string
  {
    return $this->basePath() . '/Assets';
  }

  /**
   * Get config directory path
   * 
   * @return string Path to config directory
   */
  public function configPath(): string
  {
    return $this->basePath() . '/config';
  }

  /**
   * Get models directory path
   * 
   * @return string Path to Models directory
   */
  public function modelsPath(): string
  {
    return $this->basePath() . '/Models';
  }

  /**
   * Get middlewares directory path
   * 
   * @return string Path to Middlewares directory
   */
  public function middlewaresPath(): string
  {
    return $this->basePath() . '/Middlewares';
  }

  /**
   * Get helpers directory path
   * 
   * @return string Path to Helpers directory
   */
  public function helpersPath(): string
  {
    return $this->basePath() . '/Helpers';
  }

  /**
   * Get providers directory path
   * 
   * @return string Path to Providers directory
   */
  public function providersPath(): string
  {
    return $this->basePath() . '/Providers';
  }

  /**
   * Get migrations directory path
   * 
   * @return string Path to migrations directory
   */
  public function migrationsPath(): string
  {
    return $this->basePath() . '/database/migrations';
  }

  /**
   * Get plugins directory path
   * 
   * @return string Path to Plugins directory
   */
  public function pluginsPath(): string
  {
    return $this->basePath() . '/Plugins';
  }

  /**
   * Get module CLI commands path
   * 
   * @return string Path to Commands/ directory
   */
  public function commandsPath(): string
  {
    return $this->basePath() . '/src/Commands';
  }

  public function routesPath(): string
  {
    return $this->basePath() . '/routes';
  }

  /**
   * Get module configuration value
   * 
   * Reads from module's config/ directory directly.
   * 
   * @param string $key Config key (e.g. 'blog.posts_per_page')
   * @param mixed $default Default value if key not found
   * @return mixed
   */
  protected function config(string $key, mixed $default = null): mixed
  {
    $parts = explode('.', $key);
    $file = array_shift($parts);
    $configFile = $this->configPath() . '/' . $file . '.php';

    if (!file_exists($configFile)) {
      return $default;
    }

    $config = require $configFile;

    // Navigate nested keys
    foreach ($parts as $part) {
      if (!is_array($config) || !isset($config[$part])) {
        return $default;
      }
      $config = $config[$part];
    }

    return $config;
  }

  /**
   * Log a message
   * 
   * @param string $message Log message
   * @param string $level Log level (info, warning, error)
   */
  protected function log(string $message, string $level = 'info'): void
  {
    $timestamp = date('Y-m-d H:i:s');
    $moduleName = $this->name();
    error_log("[$timestamp] [$level] [$moduleName] $message");
  }

  /**
   * Get module info
   * 
   * @return array Module information
   */
  public function info(): array
  {
    return [
      'name' => $this->name(),
      'namespace' => $this->namespace(),
      'version' => $this->version(),
      'description' => $this->description(),
      'basePath' => $this->basePath(),
    ];
  }
}
