<?php

namespace Footup\Generators;

/**
 * Plugin Generator
 * 
 * Generates a new plugin with basic structure.
 * 
 * Usage (in PHP):
 *   $generator = new PluginGenerator();
 *   $generator->create('EmailPlugin');
 * 
 * Usage (CLI):
 *   php bin/footup plugin:create EmailPlugin
 * 
 * @example
 * $generator = new PluginGenerator();
 * $generator->create('EmailPlugin');           // app/Plugins/EmailPlugin
 * $generator->create('Notifications/Slack');   // app/Plugins/Notifications/Slack
 */
class PluginGenerator
{
    protected string $name;
    protected string $pluginName;
    protected string $namespace;
    protected string $basePath;
    protected string $appPath;
    protected bool $verbose = false;

    /**
     * Constructor
     * 
     * @param string $appPath Application base path (default: current directory)
     */
    public function __construct(string $appPath = '.')
    {
        $this->appPath = rtrim($appPath, '/');
    }

    /**
     * Enable verbose output
     */
    public function verbose(): self
    {
        $this->verbose = true;
        return $this;
    }

    /**
     * Create a new plugin
     * 
     * @param string $pluginName Plugin name (e.g. "EmailPlugin" or "Notifications/SlackPlugin")
     * @return bool Success
     */
    public function create(string $pluginName): bool
    {
        try {
            $this->name = $pluginName;
            $this->parseName();
            $this->validateName();
            $this->createDirectories();
            $this->createPluginClass();
            $this->createReadme();

            $this->success("✅ Plugin '{$this->pluginName}' created successfully!");
            $this->info("Location: {$this->basePath}");
            $this->info("");
            $this->info("Next steps:");
            $this->info("1. Implement boot() method in {$this->pluginName}.php");
            $this->info("2. Add event listeners:");
            $this->info("   \$this->listen('event:name', [\$this, 'handler']);");
            $this->info("3. Plugin will be auto-discovered from app/Plugins/");

            return true;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse plugin name and set paths
     */
    protected function parseName(): void
    {
        $parts = explode('/', $this->name);
        $this->pluginName = end($parts);
        
        $this->namespace = 'App\\Plugins';
        if (count($parts) > 1) {
            array_pop($parts);
            $this->namespace .= '\\' . implode('\\', $parts);
        }

        $this->basePath = $this->appPath . '/app/Plugins';
        if (count($parts) > 1) {
            $this->basePath .= '/' . implode('/', $parts);
        }
        $this->basePath .= '/' . $this->pluginName;
    }

    /**
     * Validate plugin name
     */
    protected function validateName(): void
    {
        if (is_dir($this->basePath)) {
            throw new \RuntimeException("Plugin directory already exists: {$this->basePath}");
        }

        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $this->pluginName)) {
            throw new \RuntimeException("Invalid plugin name. Must start with uppercase letter and contain only alphanumeric characters.");
        }
    }

    /**
     * Create directory structure
     */
    protected function createDirectories(): void
    {
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
            $this->verbose("📁 Created: {$this->basePath}");
        }
    }

    /**
     * Create plugin class
     */
    protected function createPluginClass(): void
    {
        $stub = $this->getPluginStub();
        
        $content = str_replace(
            ['{{namespace}}', '{{pluginName}}'],
            [$this->namespace, $this->pluginName],
            $stub
        );

        $file = $this->basePath . '/' . $this->pluginName . '.php';
        file_put_contents($file, $content);
        $this->verbose("📝 Created: {$this->pluginName}.php");
    }

    /**
     * Create README
     */
    protected function createReadme(): void
    {
        $stub = $this->getReadmeStub();
        file_put_contents($this->basePath . '/README.md', $stub);
        $this->verbose("📝 Created: README.md");
    }

    /**
     * Get plugin class stub
     */
    protected function getPluginStub(): string
    {
        return <<<'PHP'
<?php

namespace {{namespace}};

use Footup\Engine\Plugin;

/**
 * {{pluginName}} Plugin
 * 
 * Plugin description and features go here.
 */
class {{pluginName}} extends Plugin
{
    public function name(): string
    {
        return '{{pluginName}}';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function description(): string
    {
        return 'Brief description of {{pluginName}} plugin';
    }

    /**
     * Bootstrap the plugin
     * 
     * Register event listeners here.
     */
    public function boot(): void
    {
        // Example: Listen to events
        // $this->listen('user:created', [$this, 'onUserCreated']);
        // $this->listen('post:published', [$this, 'onPostPublished']);

        $this->log('Plugin booted', 'info');
    }

    /**
     * Shutdown the plugin
     */
    public function shutdown(): void
    {
        $this->log('Plugin shutdown', 'info');
    }

    /**
     * Example event handler
     */
    public function onUserCreated($user): void
    {
        // Handle the event
        $this->log("User created: {$user->email}", 'info');
    }

    /**
     * Example event handler
     */
    public function onPostPublished($post): void
    {
        // Handle the event
        $this->log("Post published: {$post->title}", 'info');
    }
}
PHP;
    }

    /**
     * Get README stub
     */
    protected function getReadmeStub(): string
    {
        return <<<'MARKDOWN'
# {{pluginName}} Plugin

Brief description of what this plugin does.

## Installation

This plugin is automatically discovered from `app/Plugins/`.

## Configuration

Disable if needed in `config/plugins.php`:

```php
'exclude' => ['{{pluginName}}'],
```

Or for specific environments:

```php
'exclude_environments' => [
    'production' => ['{{pluginName}}'],
],
```

## Events Listened

This plugin listens to:

- `event:name` — Description of what happens
- `another:event` — Description of what happens

## Usage

The plugin automatically listens to configured events.

### Example

When a `user:created` event is dispatched:

```php
Event::dispatch('user:created', $user);
```

The plugin's `onUserCreated()` method is called.

## Development

### Add Event Listeners

```php
public function boot(): void
{
    $this->listen('user:created', [$this, 'onUserCreated']);
    $this->listen('post:published', [$this, 'onPostPublished']);
}
```

### Implement Handlers

```php
public function onUserCreated($user): void
{
    // Handle the event
    $this->log("User created: {$user->email}", 'info');
}
```

### Logging

```php
$this->log('Message', 'info');
$this->log('Error', 'error');
```

### Configuration

Access plugin config:

```php
$value = $this->config('key');
```

## License

MIT
MARKDOWN;
    }

    /**
     * Output message
     */
    protected function info(string $message): void
    {
        echo "$message\n";
    }

    /**
     * Output success message
     */
    protected function success(string $message): void
    {
        echo "\033[32m$message\033[0m\n";
    }

    /**
     * Output error message
     */
    protected function error(string $message): void
    {
        echo "\033[31m$message\033[0m\n";
    }

    /**
     * Output verbose message
     */
    protected function verbose(string $message): void
    {
        if ($this->verbose) {
            echo "  $message\n";
        }
    }
}
