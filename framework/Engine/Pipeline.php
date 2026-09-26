<?php

declare(strict_types=1);

namespace Footup\Engine;

use Footup\Http\Middleware\MiddlewareInterface;

/*
|--------------------------------------------------------------------------
| Pipeline — Advanced middleware chaining
|
| Runs a Request through a stack of middleware in the same
| 'onion' style, then into a final destination closure.
|
| Features:
|   - Basic middleware chaining
|   - Named middleware groups
|   - Conditional middleware
|   - Append/Prepend middleware
|   - Skip middleware
|   - Middleware parameters
|--------------------------------------------------------------------------
*/

class Pipeline
{
  protected mixed $passable = null;
  protected array $pipes = [];
  protected array $skipped = [];
  protected array $parameters = [];
  protected static array $groups = [];
  protected array $before = [];
  protected array $after = [];

  public function __construct(protected Container $container) {}

  /**
   * Register named middleware group
   */
  public static function registerGroup(string $name, array $middleware): void
  {
    static::$groups[$name] = $middleware;
  }

  /**
   * Send passable through pipeline
   */
  public function send(mixed $passable): self
  {
    $this->passable = $passable;
    return $this;
  }

  /**
   * Set middleware to pass through
   * Supports class strings, instances, and named groups
   */
  public function through(array|string $pipes): self
  {
    if (is_string($pipes) && isset(static::$groups[$pipes])) {
      $this->pipes = static::$groups[$pipes];
    } else {
      $this->pipes = is_array($pipes) ? $pipes : [$pipes];
    }

    return $this;
  }

  /**
   * Add middleware to the beginning (execute first)
   */
  public function prepend(array|string $middleware): self
  {
    $middleware = is_array($middleware) ? $middleware : [$middleware];
    $this->pipes = array_merge($middleware, $this->pipes);
    return $this;
  }

  /**
   * Add middleware to the end (execute last)
   */
  public function append(array|string $middleware): self
  {
    $middleware = is_array($middleware) ? $middleware : [$middleware];
    $this->pipes = array_merge($this->pipes, $middleware);
    return $this;
  }

  /**
   * Skip middleware by class name
   */
  public function skip(array|string $middleware): self
  {
    $middleware = is_array($middleware) ? $middleware : [$middleware];
    $this->skipped = array_merge($this->skipped, $middleware);
    return $this;
  }

  /**
   * Set middleware parameters (used with named groups)
   */
  public function with(array $parameters): self
  {
    $this->parameters = $parameters;
    return $this;
  }

  /**
   * Add hook to run before middleware chain
   */
  public function before(\Closure $callback): self
  {
    $this->before[] = $callback;
    return $this;
  }

  /**
   * Add hook to run after middleware chain
   */
  public function after(\Closure $callback): self
  {
    $this->after[] = $callback;
    return $this;
  }

  /**
   * Execute pipeline
   */
  public function then(\Closure $destination): mixed
  {
    // Run before hooks
    foreach ($this->before as $hook) {
      $hook($this->passable);
    }

    // Filter out skipped middleware
    $pipes = array_filter(
      $this->pipes,
      fn($pipe) => !in_array(
        is_string($pipe) ? $pipe : get_class($pipe),
        $this->skipped
      )
    );

    // Build pipeline
    $pipeline = array_reduce(
      array_reverse($pipes),
      $this->carry(),
      $destination
    );

    $result = $pipeline($this->passable);

    // Run after hooks
    foreach ($this->after as $hook) {
      $hook($result);
    }

    return $result;
  }

  /**
   * Carry closure - processes each middleware
   */
  protected function carry(): \Closure
  {
    return function (\Closure $next, string|MiddlewareInterface $pipe) {
      return function ($passable) use ($next, $pipe) {
        // Support class strings and pre-instantiated middleware
        if ($pipe instanceof MiddlewareInterface) {
          $instance = $pipe;
        } else {
          // Parse middleware with parameters: 'Class:param1,param2'
          [$class, $params] = $this->parseMiddleware($pipe);
          $instance = $this->container->make($class);

          if (!empty($params) && method_exists($instance, 'setParameters')) {
            $instance->setParameters(explode(',', $params));
          }
        }

        return $instance->handle($passable, $next);
      };
    };
  }

  /**
   * Parse middleware string with parameters
   * Format: 'ClassName' or 'ClassName:param1,param2'
   */
  protected function parseMiddleware(string $pipe): array
  {
    if (strpos($pipe, ':') === false) {
      return [$pipe, ''];
    }

    [$class, $params] = explode(':', $pipe, 2);
    return [$class, $params];
  }
}
