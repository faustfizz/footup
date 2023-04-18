<?php
/**
 * FOOTUP - 0.1.6 - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1.6
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup;

use Exception;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Routing\MiddleHandler;
use Footup\Routing\Router;

class Footup
{
    protected $router;
    public const NAME = "FootUP Framework";

    public const VERSION = "0.1.6";

    public function __construct(Router &$router)
    {
        $this->router = &$router;
        $this->router->setFrameworkName($this->name())
            ->setFrameworkVersion($this->version());
        $router->getRequest()->setEnv("start_time", microtime(true));
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @throws Exception
     * @return void
     */
    public function terminate()
    { 
        return $this->go();
    }

    /**
     * Runs the route
     *
     * @throws Exception
     * @return void
     */
    protected function go()
    {
        // Find a route
        $route = $this->router->match();
        
        /**
         * Controller
         */
        $handler = $route->getHandler();

        /**
         * Méthode
         */
        $method = $route->getMethod();

        $request = $this->router->request;
        $response = new Response();

        $this->endTime($request);

        try {
            if($handler instanceof \Closure)
            {
                $return = $handler(...array_values($route->getArgs()));

                if(!$return instanceof Response && !empty($return)){
                    return $response->body($return)->send(true);
                }
                return $return->send(true);
            }
            /**
             * @var \Footup\Controller $controller
             */
            $controller = $this->runMiddles(new $handler(), $method, $request, $response);
            // Recalculate endTime as we can run many Middles before
            $this->endTime($request);
            $return = $controller->__boot($request, $response)->{$method}(...array_values($route->getArgs()));

            if(!$return instanceof Response && !empty($return)){
                return $response->body($return)->send(true);
            }
            return $return->send(true);

        } catch (\ErrorException $exception) {
            // Erreur 500.
            throw new \ErrorException(text("Http.error500", [self::NAME, $exception->getMessage()]), $exception->getCode(), $exception->getSeverity(), $exception->getFile(), $exception->getLine(), $exception);
        }
    }

    /**
     * Execute les middleWare
     *
     * @param \Footup\Controller|mixed $controller
     * @param string $method
     * @param Request $request
     * @param Response $response
     * @return \Footup\Controller|mixed
     */
    protected function runMiddles($controller, $method, Request $request, Response &$response)
    {
        $middlesStack = [];
        /**
         * For globaux Middles
         */
        foreach($controller->getGlobalMiddles($method) as $key => $value)
        {
            if($value instanceof \Closure || class_exists($value) || $method === $key)
            {
                $middlesStack[] = $value;
            }
        }

        /**
         * For spécifiques middles
         * @var string|string[]
         */
        $middles = $controller->getMiddles(trim(get_class($controller), '\\')) ?? $controller->getMiddles(rtrim(get_class($controller), '\\'));

        if($middles)
        {
            if($value instanceof \Closure || !is_array($middles) && class_exists($middles))
            {
                $middlesStack[] = $middles;
            }
            elseif(is_array($middles))
            {
                foreach($middles as $key => $middle)
                {
                    if($value instanceof \Closure || class_exists($middle) && $method === $key)
                    {
                        $middlesStack[] = $middle;
                    }
                }
            }
        }

        if(!empty($middlesStack))
        {
            $response = (new MiddleHandler($middlesStack))->dispatch($request, $response);
        }
        
        return $controller;
    }
    
    protected function endTime(Request $request)
    {
        $request->setEnv("end_time",  microtime(true));

        list($start_time, $end_time) = [(float)$request->env("start_time"), (float)$request->env("end_time")];

        $request->setEnv("delayed_time",  (float) number_format($end_time - $start_time, 4));
    }

    /**
     * Get the value of VERSION
     */ 
    public function version()
    {
        return self::VERSION;
    }

    /**
     * Get the value of NAME
     */ 
    public function name()
    {
        return self::NAME;
    }
}