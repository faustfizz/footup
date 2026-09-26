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
    protected array $optionalParams = [];
    protected array $constraints = [];
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

    /**
     * Add a constraint regex for a parameter
     * e.g. ->where('id', '\d+')
     */
    public function where(string $param, string $regex): self
    {
        $this->constraints[$param] = $regex;
        // Recompile with new constraints
        $this->compile();
        return $this;
    }

    /**
     * Check if a parameter is optional
     */
    public function isOptionalParam(string $name): bool
    {
        return in_array($name, $this->optionalParams, true);
    }

    protected function compile(): void
    {
        $uri = trim($this->uri, '/');

        // Split by segments and process each
        $segments = explode('/', $uri);
        $compiledSegments = [];
        $foundOptional = false;

        foreach ($segments as $segment) {
            if (preg_match('#^\{([\w]+)\??\}$#', $segment, $matches)) {
                $paramName = $matches[1];
                
                // Remove trailing ? from param name if present
                $paramName = rtrim($paramName, '?');
                $isOptional = str_contains($segment, '?');
                
                // Get constraint regex if defined, otherwise use default
                $constraintRegex = $this->constraints[$paramName] ?? null;
                
                if ($isOptional) {
                    $this->optionalParams[] = $paramName;
                    $foundOptional = true;
                    
                    // Optional params with constraint: (?:/(constraint))? 
                    // Optional params without constraint: (?:/([^/]+))?
                    if ($constraintRegex) {
                        $compiledSegments[] = '(?:/(' . $constraintRegex . '))?';
                    } else {
                        $compiledSegments[] = '(?:/([^/]+))?';
                    }
                    $this->paramNames[] = $paramName;
                } else {
                    // Required param
                    if ($foundOptional) {
                        throw new \LogicException(
                            "Required parameter {$paramName} cannot follow optional parameter. "
                            . "Optional parameters must be at the end of the route."
                        );
                    }
                    
                    // Required param with constraint: (constraint)
                    // Required param without constraint: ([^/]+)
                    if ($constraintRegex) {
                        $compiledSegments[] = '(' . $constraintRegex . ')';
                    } else {
                        $compiledSegments[] = '([^/]+)';
                    }
                    $this->paramNames[] = $paramName;
                }
            } else {
                // Literal segment
                $compiledSegments[] = preg_quote($segment, '#');
            }
        }

        $regex = implode('/', $compiledSegments);
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

        $params = [];
        foreach ($this->paramNames as $index => $name) {
            // Get the captured value (or empty string if optional group didn't match)
            $value = $matches[$index] ?? null;
            // Convert empty string to null for optional params
            $params[$name] = ($value === '' || $value === false) ? null : $value;
        }

        return $params;
    }
    
    public function getController() {
      return is_array($this->action) ? $this->action[0] : '';
    }

    public function getControllerMethod() {
      return $this->action instanceof \Closure ? $this->action : $this->action[1];
    }
}
