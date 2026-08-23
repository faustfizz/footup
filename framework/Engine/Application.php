<?php

declare(strict_types=1);

namespace Footup\Engine;

use Footup\Database\Connection;
use Footup\Env;
use Footup\Exceptions\ExceptionHandler;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Exceptions\ExceptionHandlerInterface;
use Footup\Routing\Router;
use Footup\Http\Middleware\MiddlewareInterface;
use Footup\Log;
use Footup\View;

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

  public function __construct(
    public readonly string $basePath = '',
    public ?string $publicPath = '',
    public ?string $storagePath  = '',
    public ?string $uiPath  = '',
    public ?string $configPath  = '',
    public ?string $databasePath  = '',
  ) {
    self::$instance = $this;

    $this->instance(Container::class, $this);
    $this->instance(self::class, $this);

    $this->router = new Router();
    $this->instance(Router::class, $this->router);

    if (!$publicPath)
      $this->publicPath = $this->basePath . '/public';
    if (!$storagePath)
      $this->storagePath = $this->basePath . '/storage';
    if (!$uiPath)
      $this->uiPath = $this->basePath . '/ui';
    if (!$configPath)
      $this->configPath = $this->basePath . '/config';
    if (!$databasePath)
      $this->databasePath = $this->basePath . '/database';

    $this->singleton(View::class, fn(self $app) => new View($app->uiPath . '/views'));

    $this->singleton(Connection::class, function (Application $app) {
      $default = config('database.default');

      return new Connection(config("database.connections.{$default}"));
    });

    // Default error rendering — override with:
    //   $app->singleton(ExceptionHandlerInterface::class, MyHandler::class);
    $this->singleton(ExceptionHandlerInterface::class, ExceptionHandler::class);
  }

  public function usePaths(
    ?string $publicPath,
    ?string $storagePath,
    ?string $uiPath,
    ?string $configPath,
    ?string $databasePath,
  ) {
    if ($publicPath)
      $this->publicPath = $publicPath;
    if ($storagePath)
      $this->storagePath = $storagePath;
    if ($uiPath)
      $this->uiPath = $uiPath;
    if ($configPath)
      $this->configPath = $configPath;
    if ($databasePath)
      $this->databasePath = $databasePath;
  }

  public function version() {
    return static::VERSION;
  }

  public function name() {
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

  protected function loadConfigs()
  {
    Env::load($this->basePath . '/.env');

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

  /** @param array<class-string<\Footup\ServiceProvider>> $providers */
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
    string $api,
    string $cli
  ): self {
    (function (Router $router) use ($web, $api, $cli) {
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
    $this->loadConfigs();
    // Bind the live request so any class can simply type-hint Request
    // in its constructor or method signature and receive it via DI.
    $this->instance(Request::class, $request);

    try {
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

    if (is_array($result) || is_object($result)) {
      return Response::json($result);
    }

    return Response::make((string) $result);
  }
}
