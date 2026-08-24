<?php

declare(strict_types=1);

namespace Footup\Routing;

use Footup\Exceptions\MethodNotAllowedHttpException;
use Footup\Exceptions\NotFoundHttpException;
use Footup\Http\Request;

/*
|--------------------------------------------------------------------------
| Registers routes — with grouping, prefixes, and inherited
| middleware — and matches an incoming Request against them, telling
| 404 (no such path) apart from 405 (right path, wrong method).
|--------------------------------------------------------------------------
*/

class Router
{
    /** @var Route[] flat list — kept only for the rare 404-vs-405 fallback scan */
    protected array $routes = [];
    
    /** @var Route*/
    protected Route $matched;

    /** @var array<string, array<string, Route[]>> method => staticSegment => Route[] */
    protected array $staticIndex = [];

    /** @var array<string, Route[]> method => Route[] (routes starting with a {param}) */
    protected array $dynamicIndex = [];

    protected string $groupPrefix = '';
    protected array $groupMiddleware = [];
    protected ?string $groupContext = null; // 'web', 'api', or null

    public function get(string $uri, \Closure|array $action): Route
    {
        return $this->add('GET', $uri, $action);
    }

    public function post(string $uri, \Closure|array $action): Route
    {
        return $this->add('POST', $uri, $action);
    }

    public function put(string $uri, \Closure|array $action): Route
    {
        return $this->add('PUT', $uri, $action);
    }

    public function patch(string $uri, \Closure|array $action): Route
    {
        return $this->add('PATCH', $uri, $action);
    }

    public function delete(string $uri, \Closure|array $action): Route
    {
        return $this->add('DELETE', $uri, $action);
    }

    /**
     * Group routes under a shared prefix and/or middleware stack, and optionally a context.
     *
     *   $router->group(['prefix' => 'api', 'context' => 'api', 'middleware' => [...]], function (Router $r) {
     *       $r->get('/users', [UserController::class, 'index']);
     *   });
     */
    public function group(array $attributes, \Closure $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;
        $previousContext = $this->groupContext;

        $this->groupPrefix = trim($previousPrefix . '/' . ($attributes['prefix'] ?? ''), '/');
        $this->groupMiddleware = [...$previousMiddleware, ...($attributes['middleware'] ?? [])];
        $this->groupContext = $attributes['context'] ?? $previousContext;

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
        $this->groupContext = $previousContext;
    }

    public function add(string $method, string $uri, \Closure|array $action): Route
    {
        $fullUri = '/' . trim(trim($this->groupPrefix, '/') . '/' . trim($uri, '/'), '/');
        $method = strtoupper($method);

        $route = new Route($method, $fullUri, $action, $this->groupMiddleware, $this->groupContext);
        $this->routes[] = $route;

        $segment = $route->firstStaticSegment();

        if ($segment === null) {
            $this->dynamicIndex[$method][] = $route;
        } else {
            $this->staticIndex[$method][$segment][] = $route;
        }

        return $route;
    }
 
    /**
     * Find a route by its name
     */
    public function namedRoute(string $name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }
 
        return null;
    }

    /** @return Route[] */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @return array{0: Route, 1: array} the matched route and its params
     */
    public function match(Request $request): array
    {
        $method = strtoupper($request->method);
        $trimmed = trim($request->uri, '/');
        $firstSegment = $trimmed === '' ? '' : explode('/', $trimmed)[0];

        // Only test routes that *could* match: same method, and either the
        // same first static segment or a route starting with a {param}.
        $candidates = [
            ...($this->staticIndex[$method][$firstSegment] ?? []),
            ...($this->dynamicIndex[$method] ?? []),
        ];

        foreach ($candidates as $route) {
            $params = $route->matches($method, $request->uri);

            if ($params !== null) {
              $this->matched = $route;
                return [$route, $params];
            }
        }

        // Nothing matched for this method — fall back to a full scan (rare:
        // only hit on 404/405 error paths) purely to give an accurate 405.
        foreach ($this->routes as $route) {
            if ($route->matchesUriOnly($request->uri)) {
                throw new MethodNotAllowedHttpException();
            }
        }

        throw new NotFoundHttpException("No route matched [{$method} {$request->uri}]");
    }

    public function current()
    {
      return $this->matched;
    }
}