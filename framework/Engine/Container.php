<?php

declare(strict_types=1);

namespace Footup\Engine;

/*
|--------------------------------------------------------------------------
| A minimal service container: bind()/singleton()/make() with
| constructor autowiring via reflection, plus call() for invoking
| closures/[class, method] pairs with dependency + route-param injection.
|--------------------------------------------------------------------------
*/

class Container
{
    /** @var array<string, callable> */
    protected array $bindings = [];

    /** @var array<string, bool> */
    protected array $shared = [];

    /** @var array<string, mixed> */
    protected array $instances = [];

    public function bind(string $abstract, \Closure|string|null $concrete = null): void
    {
        $this->register($abstract, $concrete, false);
    }

    public function singleton(string $abstract, \Closure|string|null $concrete = null): void
    {
        $this->register($abstract, $concrete, true);
    }

    /**
     * Bind an already-built value under a key — an object (most common),
     * but arrays/scalars work too (e.g. instance('config', [...])).
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    protected function register(string $abstract, \Closure|string|null $concrete, bool $shared): void
    {
        $concrete ??= $abstract;

        $this->bindings[$abstract] = $concrete instanceof \Closure
            ? $concrete
            : fn (Container $c) => $c->build($concrete);

        $this->shared[$abstract] = $shared;
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $object = isset($this->bindings[$abstract])
            ? ($this->bindings[$abstract])($this)
            : $this->build($abstract);

        if ($this->shared[$abstract] ?? false) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Build a concrete class, auto-resolving constructor dependencies
     * via reflection. This is what lets controllers/middleware simply
     * type-hint what they need instead of pulling it from a registry.
     */
    public function build(string $concrete): object
    {
        $reflector = new \ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new \RuntimeException("Class [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new \RuntimeException(
                "Cannot resolve parameter [{$parameter->getName()}] for [{$concrete}]."
            );
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Call any callable (Closure, [Controller::class, 'method'], etc.),
     * auto-injecting dependencies and merging in extra named params
     * (e.g. route parameters like {id}).
     */
    public function call(callable|array $callback, array $params = []): mixed
    {
        if (is_array($callback)) {
            [$classOrObject, $method] = $callback;
            $instance = is_object($classOrObject) ? $classOrObject : $this->make($classOrObject);
            $reflection = new \ReflectionMethod($instance, $method);
            $args = $this->resolveParameters($reflection->getParameters(), $params);

            return $reflection->invokeArgs($instance, $args);
        }

        $reflection = new \ReflectionFunction($callback);
        $args = $this->resolveParameters($reflection->getParameters(), $params);

        return $callback(...$args);
    }

    protected function resolveParameters(array $reflectionParams, array $params): array
    {
        $args = [];

        foreach ($reflectionParams as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
                continue;
            }

            if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                $args[] = $this->make($type->getName());
                continue;
            }

            if ($params && ! $type instanceof \ReflectionNamedType) {
                // positional fallback (e.g. route params by order)
                $args[] = array_shift($params);
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            $args[] = null;
        }

        return $args;
    }
}
