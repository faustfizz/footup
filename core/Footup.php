<?php
/**
 * FOOTUP - 0.1.4 - 01.2022
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1.4
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup;

use Exception;
use Footup\Http\Request;
use Footup\Http\Session, Footup\Http\Response;
use Footup\Routing\Router;

class Footup
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @throws Exception
     */
    public function terminate()
    {
        return $this->go();
    }

    /**
     * Runs the route
     *
     * @throws Exception
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
        $session = new Session();

        try {
            /**
             * @var \Footup\Controller $controller
             */
            $controller = $this->runMiddles(new $handler(), $method, $request, $response, $session);
            return $controller->__boot($request, $response, $session)->{$method}(...array_values($route->getArgs()));

        } catch (Exception $exception) {
            // Erreur 500.
            throw new Exception(text("Http.error500", [$exception->getMessage()]));
        }
    }

    /**
     * Execute les middleWare
     *
     * @param \Footup\Controller|mixed $controller
     * @param string $method
     * @param Request $request
     * @param Response $response
     * @param Session $session
     * @return \Footup\Controller|mixed
     */
    protected function runMiddles($controller, $method, Request $request, Response $response, Session $session)
    {
        /**
         * For globaux Middles
         */
        foreach($controller->getGlobalMiddles() as $key => $value)
        {
            if(class_exists($value) && !is_string($key) || is_string($key) && $method === $key)
            {
                /**
                 * @var \Footup\Routing\Middle
                 */
                $middle = new $value();
                $return = $middle->execute($request, $response, $session);

                if($return instanceof Response)
                {
                    echo $return;
                    exit;
                }
                if($return !== true)
                {
                    echo $response->die(401, "Non Autorisé à visualiser ce site", "Non Autorisé à visualiser ce site sur ce lien <span style='color:red'>".$request->path()."</span>");
                    exit;
                }
            }
        }

        /**
         * For spécifiques middles
         * @var string|string[]
         */
        $middleware = $controller->getMiddles(trim(get_class($controller), '\\')) ?? $controller->getMiddles('\\'.trim(get_class($controller), '\\'));

        if($middleware)
        {
            if(!is_array($middleware) && class_exists($middleware))
            {
                /**
                 * @var \Footup\Routing\Middle
                 */
                $middle = (new $middleware);
                $return = $middle->execute($request, $response, $session);

                if($return instanceof Response)
                {
                    echo $return;
                    exit;
                }
                if($return !== true)
                {
                    echo $response->die(401, "Non Autorisé à visualiser ce site", "Non Autorisé à visualiser ce site sur ce lien <span style='color:red'>".$request->path()."</span>");
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
                        $return = $middle->execute($request, $response, $session);

                        if($return instanceof Response)
                        {
                            echo $return;
                            exit;
                        }
                        if($return !== true)
                        {
                            echo $response->die(401, "Non Autorisé à visualiser ce site", "Non Autorisé à visualiser ce site sur ce lien <span style='color:red'>".$request->path()."</span>");
                            exit;
                        }
                    }
                }
            }
        }

        return $controller;
    }

}