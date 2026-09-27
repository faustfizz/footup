<?php

declare(strict_types=1);

namespace Footup\Engine;

use Footup\Orm\Connection;
use Footup\Debug\Reporter;
use Footup\Engine\Env;
use Footup\Exceptions\ExceptionHandler;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Exceptions\ExceptionHandlerInterface;
use Footup\Routing\Router;
use Footup\Http\Middleware\MiddlewareInterface;
use Footup\Utils\i18n;
use Footup\Monitoring\Log;
use Footup\View\View;

/*
|--------------------------------------------------------------------------
| The Kernel — boots the container, the router, and the
| middleware pipeline, and turns an incoming Request into a Response.
| Any HttpException raised along the way is converted to a proper error
| Response instead of leaking a stack trace.
|--------------------------------------------------------------------------
*/

class Application extends Container
{
  protected static ?self $instance = null;

  protected Router $router;
  protected ?string $currentContext = null; // 'web', 'api', or null

  public const NAME = "FootUP Framework";

  public const VERSION = "1.0.0-beta";

  /** @var array<class-string<\Footup\Http\Middleware\MiddlewareInterface>> global middleware, run on every request */
  protected array $middleware = [];

  // Default service providers to register on boot. Can be overridden in bootstrap/app.php
  protected array $defaultProviders = [
    \Footup\Providers\ModuleServiceProvider::class,
  ];

  // Path configuration
  protected string $appPath = '';
  protected string $databasePath = '';
  protected string $configPath = '';
  protected string $uiPath = '';
  protected string $publicPath = '';
  protected string $storagePath = '';
  protected string $assetPath = '';

  public function __construct(
    public readonly string $basePath = ''
  ) {
    self::$instance = $this;

    $this->instance(Container::class, $this);
    $this->instance(self::class, $this);

    $this->router = new Router();
    $this->instance(Router::class, $this->router);

    // Initialize default paths
    $this->appPath = $basePath . '/app';
    $this->databasePath = $basePath . '/database';
    $this->configPath = $basePath . '/config';
    $this->storagePath = $basePath . '/storage';
    $this->publicPath = $basePath . '/public';
    $this->assetPath = $basePath . '/public/assets';
    $this->uiPath = $basePath . '/ui';

    $this->singleton(View::class, fn(self $app) => new View($app->uiPath . '/views'));

    $this->loadConfigs();

    $this->singleton(Connection::class, function (Application $app) {
      $default = config('database.default');

      return new Connection(config("database.connections.{$default}"));
    });

    // Default error rendering — override with:
    //   $app->singleton(ExceptionHandlerInterface::class, MyHandler::class);
    $this->singleton(ExceptionHandlerInterface::class, ExceptionHandler::class);

    $this->registerFatalErrorHandler();
  }

/**
 * Register shutdown handler for fatal errors
 * Internal — automatically called during Application initialization
 * 
 * @return void
 */
private function registerFatalErrorHandler(): void
{
    register_shutdown_function(function () {
        $error = error_get_last();
        
        if ($error === null) {
            return;
        }
        
        $fatalErrors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        
        if (!in_array($error['type'], $fatalErrors, true)) {
            return;
        }
        
        try {
          /** @var ExceptionHandler */
            $handler = $this->make(ExceptionHandlerInterface::class);
            
            $exception = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            $response = $handler->render($exception, Request::capture());
            
            if ($response) {
                $response->send();
            }
        } catch (\Throwable $e) {
          dd($e);
            http_response_code(500);
            echo 'Internal Server Error';
        }
    });
}

  public static function getInstance()
  {
    return self::current();
  }

  public function version()
  {
    return static::VERSION;
  }

  public function name()
  {
    return static::NAME;
  }

  public function useView(
    \Closure $registerView
  ) {
    $this->singleton(View::class, $registerView);
  }

  /**
   * The currently booted Application — lets global helpers (app(),
   * config(), view(), ...) reach the container without it being
   * threaded through every function call by hand.
   */
  public static function current(): self
  {
    if (self::$instance === null) {
      throw new \RuntimeException('No Application has been booted yet.');
    }

    return self::$instance;
  }

  protected function loadEnvAndSetErrorHandler()
  {
    Env::load($this->basePath . '/.env');
    i18n::setLocale(env('APP_LOCALE', 'en'));
    Reporter::new()->enableFatalErrorHandler(
      env('APP_ENV', 'dev')
    );
  }

  protected function bootDefaultProviders()
  {
    foreach ($this->defaultProviders as $providerClass) {
      $this->provider($providerClass);
    }
  }

  protected function loadConfigs()
  {
    $this->loadEnvAndSetErrorHandler();

    $config = [];

    foreach (glob($this->configPath . '/*.php') as $file) {
      $config[basename($file, '.php')] = require $file;
    }

    $this->instance('config', $config);
  }

  public function router(): Router
  {
    return $this->router;
  }

  /** @param array<class-string<MiddlewareInterface>|MiddlewareInterface> $middleware */
  public function middleware(array $middleware): self
  {
    $this->middleware = $middleware;

    return $this;
  }

  /** @param array<class-string<\Footup\Providers\ServiceProvider>> $providers */
  public function providers(array $providers): self
  {
    foreach ($providers as $provider) {
      $this->provider($provider);
    }

    return $this;
  }

  public function provider(string $providerClass): self
  {
    $provider = new $providerClass($this);
    $provider->register();
    $provider->boot();

    return $this;
  }

  public function context(): ?string
  {
    return $this->currentContext;
  }

  public function routesFrom(
    string $web,
    string $api
  ): self {
    (function (Router $router) use ($web, $api) {
      $this->currentContext = 'api';
      $router->group(['prefix' => 'api', 'context' => 'api'], function () use ($api) {
        require $api;
      });

      $this->currentContext = 'web';
      $router->group(['context' => 'web'], function () use ($web) {
        require $web;
      });
    })($this->router);

    return $this;
  }

  /**
   * The whole request lifecycle: global middleware -> route resolution
   * -> route-specific middleware -> controller/closure -> Response.
   * Any HttpException raised along the way is converted to a proper
   * error Response instead of leaking a stack trace.
   */
  public function handle(Request $request): Response
  {
    // Bind the live request so any class can simply type-hint Request
    // in its constructor or method signature and receive it via DI.
    $this->instance(Request::class, $request);

    try {
      $this->bootDefaultProviders();

      [$route, $params] = $this->router->match($request);

      foreach ($params as $key => $value) {
        $request->setAttribute($key, $value);
      }

      if ($route->context) {
        $request->setAttribute('_route_context', $route->context);
      }

      $pipeline = new Pipeline($this);

      return $pipeline
        ->send($request)
        ->through([...$this->middleware, ...$route->middleware])
        ->then(fn(Request $request) => $this->toResponse(
          $this->call($route->action, $params)
        ));
    } catch (\Throwable $e) {
      Log::error($e->getMessage(), [
        'exception' => get_class($e),
        'class' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
      ]);

      /** @var ExceptionHandlerInterface $handler */
      $handler = $this->make(ExceptionHandlerInterface::class);

      return $handler->render($e, $request);
    }
  }

  protected function toResponse(mixed $result): Response
  {
    if ($result instanceof Response) {
      return $result;
    }

    if (is_array($result) || $result instanceof \JsonSerializable || (is_object($result) && method_exists($result, 'toArray'))) {
      return Response::json($result);
    }

    return Response::make((string) $result);
  }

  /**
   * Check if running in production
   */
  public function isProduction(): bool
  {
    return env('APP_ENV') === 'production';
  }

  /**
   * Check if running in local development
   */
  public function isLocal(): bool
  {
    return env('APP_ENV') === 'local';
  }

  /**
   * Check if running in test mode
   */
  public function isTest(): bool
  {
    return env('APP_ENV') === 'testing';
  }

  /**
   * Check if debug mode is enabled
   */
  public function isDebug(): bool
  {
    return (bool) env('APP_DEBUG', false);
  }

  /**
   * Set the app directory path (fluent)
   * Usage: $app->appPath('/custom/app')
   */
  public function appPath(string $path): self
  {
    $this->appPath = $path;
    return $this;
  }

  /**
   * Set the app directory path (fluent)
   */
  public function uiPath(string $path): self
  {
    $this->uiPath = $path;
    return $this;
  }

  /**
   * Set the app directory path (fluent)
   */
  public function configPath(string $path): self
  {
    $this->configPath = $path;
    return $this;
  }

  /**
   * Set the app directory path (fluent)
   */
  public function publicPath(string $path): self
  {
    $this->publicPath = $path;
    return $this;
  }

  /**
   * Set the database directory path (fluent)
   * Usage: $app->databasePath('/custom/database')
   */
  public function databasePath(string $path): self
  {
    $this->databasePath = $path;
    return $this;
  }

  /**
   * Set the storage directory path (fluent)
   * Usage: $app->storagePath('/custom/storage')
   */
  public function storagePath(string $path): self
  {
    $this->storagePath = $path;
    return $this;
  }

  /**
   * Set the assets directory path (fluent)
   * Usage: $app->assetPath('/custom/assets')
   */
  public function assetPath(string $path): self
  {
    $this->assetPath = $path;
    return $this;
  }

  /**
   * Set multiple paths at once (fluent)
   * Usage: $app->usePaths([
   *     'app' => '/custom/app',
   *     'database' => '/custom/database',
   *     'storage' => '/custom/storage',
   *     'assets' => '/custom/assets',
   * ])
   */
  public function usePaths(array $paths): self
  {
    if (isset($paths['app'])) {
      $this->appPath = $paths['app'];
    }
    if (isset($paths['public'])) {
      $this->publicPath = $paths['public'];
    }
    if (isset($paths['config'])) {
      $this->configPath = $paths['config'];
    }
    if (isset($paths['ui'])) {
      $this->uiPath = $paths['ui'];
    }
    if (isset($paths['database'])) {
      $this->databasePath = $paths['database'];
    }
    if (isset($paths['storage'])) {
      $this->storagePath = $paths['storage'];
    }
    if (isset($paths['assets'])) {
      $this->assetPath = $paths['assets'];
    }
    return $this;
  }

  /**
   * Get the app directory path
   */
  public function getAppPath(): string
  {
    return $this->appPath;
  }

  /**
   * Get the public directory path
   */
  public function getPublicPath(): string
  {
    return $this->publicPath;
  }

  /**
   * Get the config directory path
   */
  public function getConfigPath(): string
  {
    return $this->configPath;
  }

  /**
   * Get the database directory path
   */
  public function getDatabasePath(): string
  {
    return $this->databasePath;
  }

  /**
   * Get the database directory path
   */
  public function getUiPath(): string
  {
    return $this->uiPath;
  }

  /**
   * Get the storage directory path
   */
  public function getStoragePath(): string
  {
    return $this->storagePath;
  }

  /**
   * Get the assets directory path
   */
  public function getAssetPath(): string
  {
    return $this->assetPath;
  }

  public function pathToUrl(string $path): string
  {
    $basePath = rtrim($this->basePath, '/');
    $relativePath = str_replace(
      'public',
      '',
      ltrim(str_replace($basePath, '', $path), '/')
    );

    return url('/' . ltrim($relativePath, '/'));
  }

  public function removeBasePath(string $path, $withoutPublicDir = true): string
  {
    $basePath = rtrim($this->basePath, '/');
    $relativePath = ltrim(str_replace($basePath, '', $path), '/');
    
    return $withoutPublicDir ? str_replace(
      'public',
      '',
      $relativePath
    ) : $relativePath;
  }
}
