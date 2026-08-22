<?php

declare(strict_types=1);

namespace Footup\Engine;

use Footup\Http\Middleware\MiddlewareInterface;

/*
|--------------------------------------------------------------------------
| Runs a Request through a stack of middleware in the same
| 'onion' style Laravel uses, then into a final destination closure.
|--------------------------------------------------------------------------
*/

class Pipeline
{
  protected mixed $passable = null;
  protected array $pipes = [];

  public function __construct(protected Container $container) {}

  public function send(mixed $passable): self
  {
    $this->passable = $passable;

    return $this;
  }

  /** @param array<class-string<MiddlewareInterface>> $pipes */
  public function through(array $pipes): self
  {
    $this->pipes = $pipes;

    return $this;
  }

  public function then(\Closure $destination): mixed
  {
    $pipeline = array_reduce(
      array_reverse($this->pipes),
      $this->carry(),
      $destination
    );

    return $pipeline($this->passable);
  }

  protected function carry(): \Closure
  {
    return function (\Closure $next, string|MiddlewareInterface $pipe) {
      return function ($passable) use ($next, $pipe) {
        // Support both class strings (auto-instantiated) and pre-instantiated middleware
        $instance = $pipe instanceof MiddlewareInterface
          ? $pipe
          : $this->container->make($pipe);

        return $instance->handle($passable, $next);
      };
    };
  }
}
