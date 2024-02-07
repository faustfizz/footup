<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Routing
 * @version 0.2
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Routing;

use Exception;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Utils\Shared;

/**
 * Router implementation
 * =====================
 *
 * Les Routes peuvent être enregistrées en utilisant les methodes (get(), post(), patch(), delete() etc.).
 * Ça prend deux (2) arguments: l'expression d'URL à correspondre et l'expression executeur. 
 * 
 * Les expressions d'URL permettent d'utiliser des noms spécials (placeholders / parametres nommés ).
 * Vous pouvez tout de même spécifier un path comme `/home/index` qui vas correspondre uniquement à cet exact Url
 * mais alternativement, vous pouvez utiliser des placeholders qui doivent être encadrés par les caractères
 * délimiteurs ( `MATCH_DELIMITER_OPENING` et `MATCH_DELIMITER_CLOSING`).
 * ex: `/foo/{bar}/{baz}`. pourra correspondre à `/foo/123/test`, comme à `/foo/test/123`, comme aussi à `/foo/ab/12`. 
 * 
 * Pour des complexes règles, vous pouvez utiliser ces formats : `{variable_name:regex}`, ex: `{id:\d+}` qui 
 * correspond juste au nombre. Soyez juste sûr que le regex est valid et correct.
 *
 * @package Footup\Routing
 * 
 * @method self get(string $uri, Closure|string $handler, string|array $options = null)
 * @method self post(string $uri, Closure|string $handler, string|array $options = null)
 * @method self put(string $uri, Closure|string $handler, string|array $options = null)
 * @method self patch(string $uri, Closure|string $handler, string|array $options = null)
 * @method self delete(string $uri, Closure|string $handler, string|array $options = null)
 * @method self head(string $uri, Closure|string $handler, string|array $options = null)
 * @method self any(string $uri, Closure|string $handler, string|array $options = null)
 */
class Router
{
    public const MATCH_DELIMITER_OPENING = '{';

    public const MATCH_DELIMITER_CLOSING = '}';

    public const MATCH_DELIMITER_SEPARATOR = ':';

    public const METHOD_ALL = 'GET|POST|PUT|PATCH|DELETE|HEAD';

    public static $auto_route = false;

    protected $framework_name = "";

    protected $framework_version = "";

    protected $controllerName = "App\\Controller\Home";

    protected $controllerMethod = "index";

    /**
     * @var Request
     */
    public $request;

    /**
     * Les routes définies
     *
     * @var array
     */
    protected $routes = [];

    /**
     * URL prefix.
     *
     * @var string
     */
    protected $prefix = '';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * AutoRoute status
     *
     * @return bool
     */
    public function autoRoute()
    {
        return self::$auto_route;
    }

    /**
     * Set the name of the recently added one route
     * 
     * @param string $name
     * @return self
     */
    public function name($name)
    {
        $lastRoutes = end($this->routes);
        $verb = key($this->routes);
        /**
         * @var Route
         */
        $route = end($lastRoutes);
        $this->routes[$verb][$route->getUri()] = $route->setName($name);
        reset($this->routes);
        
        return $this;
    }

    /**
     * Alias of name
     * 
     * @param string $name
     * @return self
     */
    public function as($name)
    {
        return $this->name($name);
    }

    /**
     * Activate auto-routing if no route defined
     * 
     * @param boolean $active
     * @return Router
     */
    public function shouldAutoRoute(bool $active = true)
    {
        if (!isset($this->routes[$this->request->method(true)]))
        {
            return $this->setAutoRoute($active);
        }
        return $this;
    }

    /**
     * Activer l'auto-routing
     * 
     * @param boolean $active
     * @return self
     */
    public function setAutoRoute(bool $active = true)
    {
        self::$auto_route = $active;
        return $this;
    }

    /**
     * Retrouve le prefixe d'URL
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return rtrim($this->prefix, "/");
    }

    /**
     * Changer le Prefix d'URL pour toutes les routes
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * grouper des routes sous un prefix d'url
     *
     * @param string   $prefix     URI prefix for the group
     * @param callable $definition Definition callback
     * @return self
     */
    public function group(string $prefix, callable $definition): self
    {
        // Store the current prefix
        $oldPrefix = $this->getPrefix();

        // Append the new prefix to the old prefix
        $this->setPrefix($oldPrefix . $prefix);

        // Execute the group definition callback, passing the current router instance as a parameter
        $definition($this);

        // Restore the old prefix
        $this->setPrefix($oldPrefix);

        return $this;
    }

    /**
     * Enregistrer une route dans le routeur
     *
     * @param string    $method  HTTP request method
     * @param string    $uri     HTTP request URI
     * @param callable|\Closure|string $handler Route handler. May be a
     *  callable, a controller instance or a fully qualified class path
     * @param string|array    $options     the format is ["as" => "route_name", "name" => "route_name"] 
     * // You use as or name to define your name_route or simply a string
     * @return self
     */
    protected function register(string $method, string $uri, $handler, $options = null): self
    {
        $route = new Route($this->getPrefix() . $uri, $handler, $options);

        // METHOD_ALL stand for all method so
        $method = ($method === self::METHOD_ALL) ? explode('|', self::METHOD_ALL) : [$method];
        foreach ($method as $verb) {
            if (! isset($this->routes[$verb])) {
                $this->routes[$verb] = [];
            }
    
            $this->routes[$verb][$route->getUri()] = $route;
        }

        return $this;
    }

    /**
     * Magic Method to register a route
     *
     * @param string $method
     * @param array $arguments
     * @return self
     */
    public function __call($method, $arguments)
    {
        $method = strtoupper($method); $allMethod = explode("|", self::METHOD_ALL);
        $registerMethod = strtolower($method) === "any" ? self::METHOD_ALL : (in_array($method, $allMethod) ? $method : null);

        if(is_null($registerMethod))
        {
            throw new Exception(text("Core.classNoMethod", [strtolower($method), get_class()]));
        }
        return $this->register($registerMethod, ...$arguments);
    }

    /**
     * Replace {lang} or {locale} by {locale:\w{2}}
     *
     * @param string $uri
     * @param int $charNumber max number of chars - default 2
     * @return string
     */
    public static function localePlaceholder(string $uri, $charNumber = 2)
    {
        return  stripos($uri, self::MATCH_DELIMITER_OPENING. "lang" .self::MATCH_DELIMITER_CLOSING) || stripos($uri, self::MATCH_DELIMITER_OPENING. "locale" .self::MATCH_DELIMITER_CLOSING) ? strtr($uri, ["lang" => "locale:\w{{$charNumber}}", "locale" => "locale:\w{{$charNumber}}"]) : (stripos($uri, self::MATCH_DELIMITER_OPENING. "lang:") ? strtr($uri, ["lang" => "locale"]) : $uri);
    }

    /**
     * Return a url that match the route's name given in parameter
     * 
     * @throws Exception
     * @param string $route_name
     * @param array $params
     * @return string|null
     */
    public function url(string $route_name, $params = [])
    {
        $method = $this->request->method(true);
        /**
         * @var Route|null
         */
        $route = null;

        // we search for the route with $route_name as name
        if (isset($this->routes[$method])) {
            // I used foreach instead of array_* function because of speed performance
            foreach ($this->routes[$method] as $uri => $needle) {
                /**
                 * @var string $uri
                 * @var Route $needle
                 */
                if($needle->getName() === $route_name){
                    $route = $needle;
                    break;
                }
            }
        }
        // if no route so return null
        if(is_null($route)) return null;

        //Oh Yeah we got a route, so
        $uri = $route->getUri();

        if(strpos($uri, static::MATCH_DELIMITER_OPENING) === false) return  $this->request->url(false, true).$uri;

        return $this->internalMatch([$route->getUri() => $route], $params, true);
    }

    /**
     * Match a route
     *
     * @param Route[] $routes
     * @param array $params
     * @param boolean $reverse
     * @return Route|string|null
     */
    private function internalMatch($routes, $params = [], $reverse = false)
    {
        // The actual path
        $requestUri = $this->request->path();

        // Build the placeholder regex from the delimiter characters
        $placeholderExpression = sprintf(
            '#(?:\%s(\w+?)(?:%s[^/]+)?\??\%s)#',
            static::MATCH_DELIMITER_OPENING,
            static::MATCH_DELIMITER_SEPARATOR,
            static::MATCH_DELIMITER_CLOSING
        );

        /**
         * @var string $uri
         * @var Route $route
         */
        foreach ($routes as $uri => $route) {
            // A hack to replace lang
            $uri = self::localePlaceholder($uri);

            // If the current route doesn't contain any placeholder delimiters, we can skip it
            if (strpos($uri, static::MATCH_DELIMITER_OPENING) === false) {
                continue;
            }

            // If the first character isn't the delimiter of a placeholder and doesn't match the first character
            // of the request URI, continue straight away.
            if (
                strpos($uri, static::MATCH_DELIMITER_OPENING) !== 1 &&
                $uri[1] !== ($requestUri[1] ?? '')
            ) {
                continue;
            }
            
            // Store the found placeholders
            $placeholders = [];
            
            // Match all placeholders in the current URI
            preg_match_all(
                $placeholderExpression,
                $uri,
                $placeholders,
                PREG_SET_ORDER
            );

            // Create a copy of the URI (we'll need it later on)
            $expression = $uri;

            // Create a list of placeholder names
            $placeholderNames = [];

            // Pop the last params if params's size bigger than placeholders size
            if(count($params) > count($placeholders))
            {
                $params = array_slice($params, 0, count($placeholders));
            }
            // We continue as we have placeholder so create parameters
            $parameters = !empty($params) ? implode("/", $params) : "";

            foreach ($placeholders as $item) {
                $replacement = '[^/]+?';

                // Extract full match and partial match from the result
                list($placeholder, $name) = $item;

                // Save the variable name
                $placeholderNames[] = $name;

                // Check if we've got a custom expression (by looking for the separator sequence)
                if (strpos($placeholder, static::MATCH_DELIMITER_SEPARATOR) !== false) {
                    // Variable expression starts after "{<name>:"
                    $offset = strpos($placeholder, $name) + strlen($name) + 1;

                    // Replacement will be anything between the offset and the last character ("}")
                    // This should probably account for the closing delimiter length.
                    $replacement = substr($placeholder, $offset, strpos($placeholder, '?'. static::MATCH_DELIMITER_CLOSING) !== false ? -2 : -1);
                }
                
                // Replace the full placeholder with a match-anything rule
                $expression = str_replace($placeholder, strpos($placeholder, '?'. static::MATCH_DELIMITER_CLOSING) !== false ? "?($replacement)?" : "($replacement)", $expression);
            }

            // Keep the matched variable values
            $matches = [];

            // Try to match the compiled regular expression against the request URI
            if (preg_match("~^$expression\$~", ($reverse ? "/".ltrim($parameters, "/") : $requestUri), $matches)) {
                // Remove the useless full matches
                $o = array_shift($matches);

                if(count($placeholderNames) > count($matches))
                {
                    $placeholderNames = array_slice($placeholderNames, 0, count($matches));
                }
                
                // Assign the placeholder names to the matched values from the URI
                $variables = array_combine($placeholderNames, $matches);

                if(!$reverse)
                {
                    // Remove lang from parameters
                    if(array_key_exists("locale", $variables) && !empty($variables["locale"]))
                    {
                        $route->setLang($variables["locale"]);
                        $this->request->setLang($variables["locale"]);
                        unset($variables["locale"]);
                    }

                    // Pass the variables to the route instance
                    $route = $route->withArgs($variables);
    
                    // Add the URI arguments to the request
                    return $this->populateRequest($route);
                }else{

                    $finalUri = preg_replace(array_map(function($placeholderName){
                        return "/{".$placeholderName."}/";
                    }, array_keys($variables)), array_values($variables), strtr($route->getUri(), ["lang" => "locale"]));

                    return $this->request->url(false, true). $finalUri;
                }
            }
        }

        return null;
    }

    /**
     * Recherche de correspondantes de routes
     * 
     * @throws Exception
     * @return Route|void
     */
    public function match()
    {
        $requestMethod = $this->request->method(true);
        $requestUri = $this->request->path();

        // If the request method doesn't exist, something seems to be fucked up.
        if (!isset($this->routes[$requestMethod]) && !$this->autoRoute()) {
            // throw a fucking Exception
            throw new Exception(text("Http.routeMethodNotFound", [$requestMethod]));
        }

        // Merge the any-method-routes and those matching the current request method
        $routes = $this->routes[$requestMethod] ?? $this->routes["GET"];
        
        // Check for direct matches
        if (isset($this->routes[$requestMethod][$requestUri])) {
            /**
             * @var Route
             */
            $route = $this->routes[$requestMethod][$requestUri];

            $this->setControllerAndMethodNames($route);
            
            return $route;
        }

        /**
         * @var Route|null
         */
        $route = $this->internalMatch($routes);

        if($route instanceof Route)
        {
            return $route;
        }

        /**
         * Sorry but nothing match, so we go with autodiscovery if enabled
         */
        if ($this->autoRoute())
        {
            $route = $this->doAutoRoute($requestUri);

        }

        $this->notFound(null, text("Http.pageNotFoundMessage", [$requestUri]));
    }

    /**
     * Go with auto discovery
     * 
     * @param string
     * @return Route
     */
    public function doAutoRoute($requestUri)
    {
        $uriSegments = explode('/', $requestUri);
        $directory = '';
        // When we filter filter, first index can remain 1 as index 0 removed
        // so we use array_values for our 0 index 
        $uriSegments = array_values(array_filter($uriSegments));
        
        // Searh directories and concatenate with $directory
        array_walk($uriSegments, function($segment, $index, $uri) use (&$directory, &$uriSegments){
            $counter                 = count($uri);

            // Loop through our uri segments, is there a folder or just controller ?
            while ($counter-- > 0)
            {
                $controller = $directory . ucfirst($uri[0]);

                // if it is not a file, it's should be a directory or what ?
                if (! is_file(APP_PATH . 'Controller/' . $controller . '.php') && is_dir(APP_PATH . 'Controller/' . $directory . ucfirst($uri[0])))
                {
                    // As array_walk don't work with reference, we passed it into the closure
                    array_shift($uriSegments);
                    $directory .= ucfirst(array_shift($uri)).DS;
                    continue;
                }
                // if we are here, that mean the rest are controller and/or method
                return $uri;
            }
            // We are because of $counter was 1 and inside the loop, we shifted that as directory
            return $uri;
        }, $uriSegments);

        $config = Shared::loadConfig();

        if (!empty($uriSegments))
        {
            // We shift all so only parameters will stay in $uriSegments
            list($class, $action) = count($uriSegments) < 2 ? [array_shift($uriSegments), $config->default_method] : [array_shift($uriSegments), array_shift($uriSegments)];

            // Why we add DS => "\\" because of when we ride on a foolish system like Windows (a fucking system)
            $controller = "App\\Controller\\" . strtr($directory . ucfirst($class), ["/" => "\\", DS => "\\"]);
            $method = $action;
        }
        else
        {
            // We use $directory here as it can be shifted before or empty
            $controller = "App\\Controller\\" . strtr($directory . ucfirst($config->default_controller), ["/" => "\\", DS => "\\"]);
            $method = $config->default_method;
        }

        if(!method_exists($controller, $method))
        {
            $this->notFound(null, text("Http.pageNotFoundMessage", [$requestUri]));
        }

        $route = (new Route($requestUri, $controller . Route::CONTROLLER_DELIMITER . $method))->withArgs($uriSegments ?? []);

        // Add the URI arguments to the request
        return $this->populateRequest($route);
    }

    /**
     * Populate matched arguments => value to the request
     *
     * @param Route $route
     * @return Route
     */
    private function populateRequest(Route $route)
    {
        foreach ($route->getArgs() as $key => $value) {
            # code...
            $this->request->{$key} = $value;
        }
        return $this->setControllerAndMethodNames($route);
    }

    /**
     * Set controller and method names
     *
     * @param Route $route
     * @return Route
     */
    private function setControllerAndMethodNames(Route $route)
    {
        $this->setControllerName($route->getHandler())
            ->setControllerMethod($route->getMethod());

        $this->request->controllerName = $route->getHandler();
        $this->request->controllerMethod = $route->getMethod();

        return $route;
    }

    /**
     * @param string $status
     * @param string $message
     * @return void
     */
    public function notFound($title = null, $message = "")
    {
        $title = $title ?? text("Http.pageNotFound");
        (new Response())->die("404", $title, $message);
        exit;
    }

    /**
     * Get the value of controllerName
     */ 
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Set the value of controllerName
     *
     * @return  self
     */ 
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;

        return $this;
    }

    /**
     * Get the value of controllerMethod
     */ 
    public function getControllerMethod()
    {
        return $this->controllerMethod;
    }

    /**
     * Set the value of controllerMethod
     *
     * @return  self
     */ 
    public function setControllerMethod($controllerMethod)
    {
        $this->controllerMethod = $controllerMethod;

        return $this;
    }

    /**
     * Get the value of request
     *
     * @return  Request
     */ 
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the value of framework_name
     */ 
    public function getFrameworkName()
    {
        return $this->framework_name;
    }

    /**
     * Set the value of framework_name
     *
     * @param string $name
     * @return  self
     */ 
    public function setFrameworkName($framework_name)
    {
        $this->framework_name = $framework_name;

        return $this;
    }

    /**
     * Get the value of framework_version
     */ 
    public function getFrameworkVersion()
    {
        return $this->framework_version;
    }

    /**
     * Set the value of framework_version
     *
     * @param string
     * @return  self
     */ 
    public function setFrameworkVersion($framework_version)
    {
        $this->framework_version = $framework_version;

        return $this;
    }
}