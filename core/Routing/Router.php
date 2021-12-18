<?php

/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Routing
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Routing;

use App\Config\Config;
use Exception;
use Footup\Http\Request;
use Footup\Http\Response;

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
 */
class Router
{
    public const MATCH_DELIMITER_CLOSING = '}';

    public const MATCH_DELIMITER_OPENING = '{';

    public const MATCH_DELIMITER_SEPARATOR = ':';

    public const METHOD_ANY = 'ANY';

    public const METHOD_DELETE = 'DELETE';

    public const METHOD_GET = 'GET';

    public const METHOD_HEAD = 'HEAD';

    public const METHOD_PATCH = 'PATCH';

    public const METHOD_POST = 'POST';

    public const METHOD_PUT = 'PUT';

    public static $auto_route = true;

    protected $controllerName = "App\Controller\Home";

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
        return $this->prefix;
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
     * @param string $uri
     * @param string|colable|\Footup\Controller $handler
     * @return self
     */
    public function any(string $uri, $handler): self
    {
        return $this->register(static::METHOD_ANY, $uri, $handler);
    }

    /**
     * @param string $uri
     * @param string|colable|\Footup\Controller $handler
     * @return self
     */
    public function get(string $uri, $handler): self
    {
        return $this->register(static::METHOD_GET, $uri, $handler);
    }

    /**
     * @param string $uri
     * @param string|colable|\Footup\Controller $handler
     * @return self
     */
    public function post(string $uri, $handler): self
    {
        return $this->register(static::METHOD_POST, $uri, $handler);
    }

    /**
     * @param string $uri
     * @param string|colable|\Footup\Controller $handler
     * @return self
     */
    public function put(string $uri, $handler): self
    {
        return $this->register(static::METHOD_PUT, $uri, $handler);
    }

    /**
     * @param string $uri
     * @param string|colable|\Footup\Controller $handler
     * @return self
     */
    public function delete(string $uri, $handler): self
    {
        return $this->register(static::METHOD_DELETE, $uri, $handler);
    }

    /**
     * @param string $uri
     * @param string|colable|\Footup\Controller $handler
     * @return self
     */
    public function patch(string $uri, $handler): self
    {
        return $this->register(static::METHOD_PATCH, $uri, $handler);
    }

    /**
     * @param string $uri
     * @param string|colable|\Footup\Controller $handler
     * @return self
     */
    public function head(string $uri, $handler): self
    {
        return $this->register(static::METHOD_HEAD, $uri, $handler);
    }

    /**
     * Recherche de correspondantes de routes
     * 
     * @todo Au lieu de renvoyer une Exeception, nous devons définir une route qui affichera la page 404
     *
     * @return \Footup\Routing\Route
     * @throws \Exception
     */
    public function match(): Route
    {
        $requestMethod = strtoupper($this->request->method());
        $requestUri = $this->request->path();

        // If the request method doesn't exist, something seems to be fucked up.
        if (! isset($this->routes[$requestMethod])) {
            throw new Exception("La méthode n'éxiste pas dans les route définies {$requestMethod}");
        }

        // Merge the any-method-routes and those matching the current request method
        $routes = array_merge(
            $this->routes[static::METHOD_ANY] ?? [],
            $this->routes[$requestMethod] ?? []
        );

        // Check for direct matches
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $route = $this->routes[$requestMethod][$requestUri];

            return $route;
        }

        // Build the placeholder regex from the delimiter characters
        $placeholderExpression = sprintf(
            '#(?:\%s(.+?)(?:%s.*)?\%s)#',
            static::MATCH_DELIMITER_OPENING,
            static::MATCH_DELIMITER_SEPARATOR,
            static::MATCH_DELIMITER_CLOSING
        );

        /**
         * @var string                         $uri
         * @var \Footup\Routing\Route $route
         */
        foreach ($routes as $uri => $route) {
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
                    $replacement = substr($placeholder, $offset, -1);
                }

                // Replace the full placeholder with a match-anything rule
                $expression = str_replace($placeholder, "($replacement)", $expression);
            }

            // Keep the matched variable values
            $matches = [];

            // Try to match the compiled regular expression against the request URI
            if (preg_match("~^$expression\$~", $requestUri, $matches)) {
                // Remove the useless full matches
                array_shift($matches);

                // Assign the placeholder names to the matched values from the URI
                $variables = array_combine($placeholderNames, $matches);

                // Pass the variables to the route instance
                $route = $route->withArgs($variables);

                // Add the URI arguments to the request
                foreach ($route->getArgs() as $key => $value) {
                    # code...
                    $this->request->{$key} = $value;
                }
                
                $this->request->controllerName = $route->getHandler();
                $this->request->controllerMethod = $route->getMethod();
                $this->setControllerName($route->getHandler())->setControllerMethod($route->getMethod());

                return $route;
            }
        }

        if($this->autoRoute())
        {
            $uri = explode('/', trim($this->request->path(), "/"));
            $uri = array_filter($uri);
            [$class, $action] = count($uri) < 2 ? [$uri[0], null] : $uri;

            if(ucfirst($class) === (new Config)->config['default_controller'])
            {
                $controller = "App\Controller\\".(new Config)->config['default_controller'];
                $method = (!is_null($action) ? $action : (new Config)->config['default_method']);

                $route = new Route($this->request->path().'/'.$method,  $controller.'@'.$method);
                
                // Add the URI arguments to the request
                foreach ($route->getArgs() as $key => $value) {
                    # code...
                    $this->request->{$key} = $value;
                }
                
                $this->request->controllerName = $route->getHandler();
                $this->request->controllerMethod = $route->getMethod();
                $this->setControllerName($route->getHandler())->setControllerMethod($route->getMethod());

                return $route;
            }

            if(is_null($class))
            {
                $this->die();
            }
        }

        $this->die('404', "Page: Not Found !", "Erreur 404 : L'Url <span style='color: blue'>{$requestUri}</span> ne correspond à aucune des routes définies");
    }

    /**
     * @param string $status
     * @param string $message
     * @return void
     */
    public function die($status = '404', $title = "404: Not Found !", $message = "")
    {
        echo (new Response())->die($status, $title, $message);
        exit;
    }

    /**
     * Enregistrer une route dans le routeur
     *
     * @param string    $method  HTTP request method
     * @param string    $uri     HTTP request URI
     * @param callable|\Footup\Controller|string $handler Route handler. May be a
     *  callable, a controller instance or a fully qualified class path
     * @return self
     */
    protected function register(string $method, string $uri, $handler): self
    {
        $route = new Route($this->getPrefix() . $uri, $handler);

        if (! isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][$route->getUri()] = $route;

        return $this;
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
}