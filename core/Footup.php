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

        $request->setEnv("end_time",  microtime(true));

        list($start_time, $end_time) = [(float)$request->env("start_time"), (float)$request->env("end_time")];

        $request->setEnv("delayed_time",  (float) number_format($end_time - $start_time, 4));

        try {
            if($handler instanceof \Closure)
            {
                $return = $handler(...array_values($route->getArgs()));

                if(!$return instanceof Response){
                    return $response->body($return)->send(true);
                }
                return $return->send(true);
            }
            /**
             * @var \Footup\Controller $controller
             */
            $controller = $this->runMiddles(new $handler(), $method, $request, $response);
            $return = $controller->__boot($request, $response)->{$method}(...array_values($route->getArgs()));

            if(!$return instanceof Response){
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
    protected function runMiddles($controller, $method, Request $request, Response $response)
    {
        /**
         * For globaux Middles
         */
        foreach($controller->getGlobalMiddles($method) as $key => $value)
        {
            if(class_exists($value) || $method === $key)
            {
                /**
                 * @var \Footup\Routing\Middle
                 */
                $middle = new $value();
                $return = $middle->execute($request, $response);

                if($return instanceof Response)
                {
                    $return->send(true);
                    exit;
                }
                if($return !== true)
                {
                    $response->die(401, "Non Autorisé à visualiser ce site", "Non Autorisé à visualiser ce site sur ce lien <span style='color:red'>".$request->path()."</span>")->send(true);
                    exit;
                }
            }
        }

        /**
         * For spécifiques middles
         * @var string|string[]
         */
        $middleware = $controller->getMiddles(trim(get_class($controller), '\\')) ?? $controller->getMiddles(rtrim(get_class($controller), '\\'));

        if($middleware)
        {
            if(!is_array($middleware) && class_exists($middleware))
            {
                /**
                 * @var \Footup\Routing\Middle
                 */
                $middle = (new $middleware);
                $return = $middle->execute($request, $response);

                if($return instanceof Response)
                {
                    $return->send(true);
                    exit;
                }
                if($return !== true)
                {
                    $response->die(401, "Non Autorisé à visualiser ce site", "Non Autorisé à visualiser ce site sur ce lien <span style='color:red'>".$request->path()."</span>")->send(true);
                    exit;
                }
            }
            elseif(is_array($middleware))
            {
                foreach($middleware as $k => $mid)
                {
                    if(class_exists($mid) && $method === $k)
                    {
                        /**
                         * @var \Footup\Routing\Middle
                         */
                        $middle = new $mid();
                        $return = $middle->execute($request, $response);

                        if($return instanceof Response)
                        {
                            $return->send(true);
                            exit;
                        }
                        if($return !== true)
                        {
                            $response->die(401, "Non Autorisé à visualiser ce site", "Non Autorisé à visualiser ce site sur ce lien <span style='color:red'>".$request->path()."</span>")->send(true);
                            exit;
                        }
                    }
                }
            }
        }

        return $controller;
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