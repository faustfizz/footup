<?php

declare(strict_types=1);

namespace Footup\Routing;

/*
|--------------------------------------------------------------------------
| A single registered route: HTTP method, uri pattern, action
| (Closure or [Controller::class, 'method']), and its own middleware.
|--------------------------------------------------------------------------
*/

class Route
{
    protected string $pattern;
    protected array $paramNames = [];
    protected ?string $routeName = null;

    public function __construct(
        public string $method,
        public string $uri,
        public \Closure|array $action,
        public array $middleware = [],
        public ?string $context = null, // 'web', 'api', or null
    ) {
        $this->compile();
    }
 
    /**
     * Assign a name to this route for URL generation
     * e.g. $route->name('users.show')
     */
    public function name(string $name): self
    {
        $this->routeName = $name;
 
        return $this;
    }
 
    public function getName(): ?string
    {
        return $this->routeName;
    }

    protected function compile(): void
    {
        $uri = trim($this->uri, '/');

        $regex = preg_replace_callback('#\{([\w]+)\}#', function ($matches) {
            $this->paramNames[] = $matches[1];

            return '([^/]+)';
        }, $uri);

        $this->pattern = '#^' . $regex . '$#';
    }

    /**
     * Returns an associative array of matched params, or null if no match.
     */
    public function matches(string $method, string $uri): ?array
    {
        if (strtoupper($method) !== $this->method) {
            return null;
        }

        return $this->matchUri($uri);
    }

    /**
     * Checks the URI against this route's pattern regardless of HTTP verb.
     * Used by the router to tell "no such path" (404) apart from
     * "path exists, wrong verb" (405).
     */
    public function matchesUriOnly(string $uri): bool
    {
        return $this->matchUri($uri) !== null;
    }

    /**
     * The first literal path segment (e.g. "users" for /users/{id}), or
     * null if the route starts with a {param} and so can't be indexed by
     * a static segment. Used by the router to bucket routes so matching
     * doesn't need to regex-test every route on every request.
     */
    public function firstStaticSegment(): ?string
    {
        $trimmed = trim($this->uri, '/');

        if ($trimmed === '') {
            return ''; // the root route "/"
        }

        $first = explode('/', $trimmed)[0];

        return str_starts_with($first, '{') ? null : $first;
    }

    protected function matchUri(string $uri): ?array
    {
        $uri = trim($uri, '/');

        if (! preg_match($this->pattern, $uri, $matches)) {
            return null;
        }

        array_shift($matches);

        return array_combine($this->paramNames, $matches) ?: [];
    }

    public function getController() {
      return is_array($this->action) ? $this->action[0] : '';
    }

    public function getControllerMethod() {
      return $this->action instanceof \Closure ? $this->action : $this->action[1];
    }
}