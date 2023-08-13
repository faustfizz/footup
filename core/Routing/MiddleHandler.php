<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Routing
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Routing;

use Footup\Http\Request;
use Footup\Http\Response;
use InvalidArgumentException;

class MiddleHandler
{
    /**
     * @var int $index
     */
    protected $index = 0;

    /**
     * @var array 
     */
    protected $middlewares = [];
    
    /**
     * @var Response $response 
     */
    protected $response;

    /**
     * @var callable middleware resolver
     */
    private $resolver;

    /**
     * @var mixed[] unresolved middleware stack
     */
    private $stack;

    /**
     * @param (callable|MiddleInterface|\Closure)[] $stack middleware stack (with at least one middleware component)
     *
     * @throws InvalidArgumentException if an empty middleware stack was given
     */
    public function __construct($middles)
    {
        array_map([$this, 'addMiddle'], $middles);
    }
        
    /**
     * Add a middleware to the end of the queue.
     *
     * @param string|callable|MiddleInterface|\Closure $middleware
     * @return void 
     * @throws \InvalidArgumentException 
     */
    public function addMiddle($middle)
    {
        if (!is_string($middle) && !$middle instanceof MiddleInterface && !$middle instanceof \Closure && !is_callable($middle)) {
            throw new InvalidArgumentException('Middle must be a string, Closure, Callable or an instance of MiddleInterface');
        }

        $class = is_string($middle) && class_exists($middle) ? new $middle : $middle;
        array_push($this->middlewares, $class);
    }

    /**
     * Dispatch the middleware queue.
     * 
     * @param Request $request 
     * @param Response $response
     * @return Response|mixed
     */
    public function dispatch(Request $request, Response $response)
    {
        reset($this->middlewares);
        $this->response = $response;
        return $this->handle($request, $response);
    }

    /**
     * Handle the request, return a response and calls
     * next middleware.
     * 
     * @param Request $request 
     * @return Response|mixed|void
     */
    public function handle(Request $request, Response $response)
    {
        if (!isset($this->middlewares[$this->index])) {
            return $this->response;
        }

        /**
         * @var Middle|\Closure
         */
        $middleware = $this->middlewares[$this->index];
        if(is_object($middleware) && method_exists($middleware, "execute")) {
            $result = $middleware->execute($request, $response, $this->next());
        } else {
            $result = $middleware($request, $response, $this->next());
        }
        
        // If the result is not an instance of Response so you don't need to continue
        if ($result instanceof Response) {
            return $result;
        } elseif (is_scalar($result) || is_array($result) || is_object($result) ) {
            return $response->body($result)->send();
        }
        return null;
    }

    /**
     * Dispatch the next available middleware and return the response.
     *
     * This method duplicates `handle()` to provide support for `callable` middleware.
     *
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response)
    {
        return $this->handle($request, $response);
    }

    /**
     * Next middleware.
     * 
     * @return static
     */
    private function next()
    {
        // $clone = clone $this;
        $this->index++;
        return $this;
    }
}
