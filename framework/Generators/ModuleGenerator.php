<?php

namespace Footup\Generators;

/**
 * Module Generator
 * 
 * Generates a new module with complete structure.
 * Optional: Views, Assets, Config, Helpers, Providers, Routes, Migrations, Plugins
 */
class ModuleGenerator
{
    protected string $name;
    protected string $moduleName;
    protected string $namespace;
    protected string $basePath;
    protected string $appPath;
    protected bool $verbose = false;

    public function __construct(string $appPath = '.')
    {
        $this->appPath = rtrim($appPath, '/');
    }

    public function verbose(): self
    {
        $this->verbose = true;
        return $this;
    }

    public function create(string $moduleName): bool
    {
        try {
            $this->name = $moduleName;
            $this->parseName();
            $this->validateName();
            $this->createDirectories();
            $this->createModuleClass();
            $this->createExampleFiles();
            $this->updateModulesConfig();

            $this->success("✅ Module '{$this->moduleName}' created successfully!");
            $this->info("Location: {$this->basePath}");
            $this->info("");
            $this->info("Features included:");
            $this->info("✓ Views (Views/ directory)");
            $this->info("✓ Assets (Assets/css, Assets/js)");
            $this->info("✓ Config (config/ directory)");
            $this->info("✓ Routes (routes.php)");
            $this->info("✓ Helpers (Helpers/ - auto-loaded)");
            $this->info("✓ Providers (Providers/ - auto-booted)");
            $this->info("✓ Models (Models/ directory)");
            $this->info("✓ Services (Services/ directory)");
            $this->info("✓ Middlewares (Middlewares/ directory)");
            $this->info("✓ Controllers (Controllers/ directory)");
            $this->info("✓ Migrations (database/migrations/ - auto-run)");
            $this->info("✓ Module Plugins (Plugins/ - auto-discovered)");
            $this->info("");
            $this->info("Next steps:");
            $this->info("1. Implement boot() and dependencies() in {$this->moduleName}.php");
            $this->info("2. Add to config/modules.php (auto-done)");
            $this->info("3. Create your content in Views/, Models/, Services/, etc");
            $this->info("4. Add routes in routes.php");
            $this->info("5. Add helpers in Helpers/");
            $this->info("6. Add migrations in database/migrations/");

            return true;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return false;
        }
    }

    protected function parseName(): void
    {
        $parts = explode('/', $this->name);
        $this->moduleName = end($parts);
        
        $this->namespace = 'App\\Modules';
        if (count($parts) > 1) {
            array_pop($parts);
            $this->namespace .= '\\' . implode('\\', $parts);
        }

        $this->basePath = $this->appPath . '/app/Modules';
        if (count($parts) > 1) {
            $this->basePath .= '/' . implode('/', $parts);
        }
        $this->basePath .= '/' . $this->moduleName;
    }

    protected function validateName(): void
    {
        if (is_dir($this->basePath)) {
            throw new \RuntimeException("Module directory already exists: {$this->basePath}");
        }

        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $this->moduleName)) {
            throw new \RuntimeException("Invalid module name. Must start with uppercase letter and contain only alphanumeric characters.");
        }
    }

    protected function createDirectories(): void
    {
        $dirs = [
            $this->basePath,
            $this->basePath . '/Controllers',
            $this->basePath . '/Models',
            $this->basePath . '/Views',
            $this->basePath . '/Assets/css',
            $this->basePath . '/Assets/js',
            $this->basePath . '/Middlewares',
            $this->basePath . '/Services',
            $this->basePath . '/Helpers',
            $this->basePath . '/Providers',
            $this->basePath . '/Plugins',
            $this->basePath . '/config',
            $this->basePath . '/database/migrations',
            $this->basePath . '/database/seeders',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    protected function createModuleClass(): void
    {
        $stub = $this->getModuleStub();
        
        $content = str_replace(
            ['{{namespace}}', '{{moduleName}}', '{{modulePath}}'],
            [$this->namespace, $this->moduleName, strtolower($this->moduleName)],
            $stub
        );

        file_put_contents($this->basePath . '/' . $this->moduleName . '.php', $content);
        $this->verbose("📝 Created: {$this->moduleName}.php");
    }

    protected function createExampleFiles(): void
    {
        // Config
        file_put_contents(
            $this->basePath . '/config/' . strtolower($this->moduleName) . '.php',
            $this->getConfigStub()
        );

        // View
        file_put_contents($this->basePath . '/Views/index.html', $this->getViewStub());

        // Controller
        file_put_contents(
            $this->basePath . '/Controllers/IndexController.php',
            str_replace(['{{namespace}}'], [$this->namespace], $this->getControllerStub())
        );

        // Middleware
        file_put_contents(
            $this->basePath . '/Middlewares/Example.php',
            str_replace(['{{namespace}}'], [$this->namespace], $this->getMiddlewareStub())
        );

        // Service
        file_put_contents(
            $this->basePath . '/Services/' . $this->moduleName . 'Service.php',
            str_replace(['{{namespace}}', '{{moduleName}}'], [$this->namespace, $this->moduleName], $this->getServiceStub())
        );

        // Helper
        file_put_contents(
            $this->basePath . '/Helpers/functions.php',
            $this->getHelperStub()
        );

        // Provider
        file_put_contents(
            $this->basePath . '/Providers/ExampleProvider.php',
            str_replace(['{{namespace}}'], [$this->namespace], $this->getProviderStub())
        );

        // Migration
        file_put_contents(
            $this->basePath . '/database/migrations/2024_01_01_000000_create_table.php',
            $this->getMigrationStub()
        );

        // Routes
        file_put_contents($this->basePath . '/routes.php', $this->getRoutesStub());

        // README
        file_put_contents($this->basePath . '/README.md', $this->getReadmeStub());

        // .gitkeep files
        touch($this->basePath . '/Assets/css/.gitkeep');
        touch($this->basePath . '/Assets/js/.gitkeep');
        touch($this->basePath . '/database/migrations/.gitkeep');
        touch($this->basePath . '/database/seeders/.gitkeep');
        touch($this->basePath . '/Models/.gitkeep');
        touch($this->basePath . '/Plugins/.gitkeep');

        $this->verbose("📝 Created example files");
    }

    protected function updateModulesConfig(): void
    {
        $configPath = $this->appPath . '/config/modules.php';
        
        if (!file_exists($configPath)) {
            $this->verbose("⚠️  config/modules.php not found, skipping auto-registration");
            return;
        }

        $content = file_get_contents($configPath);
        $modulePath = '\\' . $this->namespace . '\\' . $this->moduleName . '::class';

        if (strpos($content, $modulePath) === false) {
            $content = str_replace(
                "'list' => [",
                "'list' => [\n        " . $modulePath . ",",
                $content
            );
            file_put_contents($configPath, $content);
            $this->verbose("✏️  Updated: config/modules.php");
        }
    }

    protected function getModuleStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}};

use Footup\Engine\Module;

/**
 * {{moduleName}} Module
 * 
 * Module description and features.
 */
class {{moduleName}} extends Module
{
    public function name(): string
    {
        return '{{modulePath}}';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function description(): string
    {
        return 'Brief description of {{moduleName}} module';
    }

    /**
     * Module dependencies
     * 
     * @return array List of module names this module depends on
     */
    public function dependencies(): array
    {
        // Example: return ['auth', 'database'];
        return [];
    }

    /**
     * Bootstrap the module
     * 
     * Called before all other operations:
     * 1. Dependencies booted
     * 2. Helpers loaded
     * 3. Providers booted
     * 4. Routes loaded
     * 5. Migrations run
     * 6. Module plugins discovered
     * 7. This boot() method called
     */
    public function boot(): void
    {
        // Your module bootstrap logic here
        $this->log('Module booted', 'info');
    }

    public function shutdown(): void
    {
        $this->log('Module shutdown', 'info');
    }
}
PHP;
    }

    protected function getConfigStub(): string
    {
        return <<<'PHP'
<?php

/**
 * {{moduleName}} Module Configuration
 * 
 * Access via: config('modules.{{modulePath}}.{{modulePath}}.*')
 */

return [
    'enabled' => true,
    // Add your configuration here
];
PHP;
    }

    protected function getViewStub(): string
    {
        return <<<'PHP'
<div class="{{modulePath}}-container">
    <h1>Welcome to {{moduleName}} Module</h1>
    <p>This is the default view for your module.</p>
</div>
PHP;
    }

    protected function getControllerStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}}\Controllers;

class IndexController
{
    public function index()
    {
        return view('{{modulePath}}::index');
    }
}
PHP;
    }

    protected function getMiddlewareStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}}\Middlewares;

class Example
{
    public function handle($request, $next)
    {
        // Do something before request
        $response = $next($request);
        // Do something after response
        return $response;
    }
}
PHP;
    }

    protected function getServiceStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}}\Services;

class {{moduleName}}Service
{
    public function example(): string
    {
        return 'Hello from {{moduleName}}Service!';
    }
}
PHP;
    }

    protected function getHelperStub(): string
    {
        return <<<'PHP'
<?php

/**
 * {{moduleName}} Module Helpers
 * 
 * Helper functions specific to this module.
 * Auto-loaded when module boots.
 */

if (!function_exists('example_helper')) {
    /**
     * Example helper function
     */
    function example_helper(): string
    {
        return 'Helper from {{moduleName}} module';
    }
}
PHP;
    }

    protected function getProviderStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}}\Providers;

/**
 * Example Provider
 * 
 * Providers are auto-discovered and booted from Providers/ directory.
 * Each provider can have a boot() method.
 */
class ExampleProvider
{
    public function boot(): void
    {
        // Register services, bindings, etc
        // This is called before module boot() method
    }
}
PHP;
    }

    protected function getMigrationStub(): string
    {
        return <<<'PHP'
<?php

/**
 * {{moduleName}} Module Migration
 * 
 * Migrations are auto-run when module boots.
 * Return a callable that performs the migration.
 */

return function () {
    // Perform database operations
    // Example: Create table, add columns, etc
    // $db->table('users')->create(...);
};
PHP;
    }

    protected function getRoutesStub(): string
    {
        return <<<'PHP'
<?php

/**
 * {{moduleName}} Module Routes
 * 
 * Routes are auto-loaded when module boots.
 * Define your module routes here.
 */

// Example:
// Route::get('/{{modulePath}}', Controllers\IndexController::class);
// Route::post('/{{modulePath}}', Controllers\StoreController::class);
PHP;
    }

    protected function getReadmeStub(): string
    {
        return <<<'MARKDOWN'
# {{moduleName}} Module

Brief description of what this module does.

## Installation

This module is included in the application.

## Configuration

Configure in `config/modules.php`:

```php
'list' => [
    \App\Modules\{{moduleName}}::class,
]
```

## Features

- Views: `Views/` directory
- Assets: `Assets/css/` and `Assets/js/`
- Config: `config/{{modulePath}}.php`
- Routes: `routes.php` (auto-loaded)
- Helpers: `Helpers/` (auto-loaded)
- Providers: `Providers/` (auto-booted)
- Migrations: `database/migrations/` (auto-run)
- Module Plugins: `Plugins/` (auto-discovered)
- Services: `Services/` for business logic
- Middlewares: `Middlewares/` for request handling
- Controllers: `Controllers/` for request handling

## Module Dependencies

Declare dependencies in your module:

```php
public function dependencies(): array
{
    return ['auth', 'database'];
}
```

The module manager ensures dependencies are booted first.

## Usage

Access module views:
```php
view('{{modulePath}}::index')
```

Use module helpers:
```php
example_helper() // From Helpers/functions.php
```

Use module services:
```php
$service = new \App\Modules\{{moduleName}}\Services\{{moduleName}}Service();
```

## License

MIT
MARKDOWN;
    }

    protected function info(string $message): void
    {
        echo "$message\n";
    }

    protected function success(string $message): void
    {
        echo "\033[32m$message\033[0m\n";
    }

    protected function error(string $message): void
    {
        echo "\033[31m$message\033[0m\n";
    }

    protected function verbose(string $message): void
    {
        if ($this->verbose) {
            echo "  $message\n";
        }
    }
}
