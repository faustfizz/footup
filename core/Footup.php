<?php
/**
 * FOOTUP - 0.1.5 - 03.2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1.5
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup;

use Exception;
use Footup\Http\Request;
use Footup\Http\Session, Footup\Http\Response;
use Footup\Routing\Router;
use Footup\Utils\Shared;

class Footup
{
    protected $router;
    protected $name = "FOOTUP MVC Framework";

    protected $_version = "0.1.5";

    protected $_code = 00105;

    public function __construct(Router &$router)
    {
        $this->router = &$router;
        $this->router->setFrameworkName($this->name())
            ->setFrameworkVersion($this->version())
            ->setFrameworkVersionCode($this->code());
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
        /**
         * @var Session
         */
        $session = Shared::loadSession();

        try {
            if($handler instanceof \Closure)
            {
                $return = $handler(...array_values($route->getArgs()));

                if($return instanceof Response){
                    echo $return;
                }
                return;
            }
            /**
             * @var \Footup\Controller $controller
             */
            $controller = $this->runMiddles(new $handler(), $method, $request, $response, $session);
            $return = $controller->__boot($request, $response)->{$method}(...array_values($route->getArgs()));

            if($return instanceof Response){
                echo $return;
            }
            return;

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


    /**
     * Get the value of _version
     */ 
    public function version()
    {
        return $this->_version;
    }

    /**
     * Get the value of name
     */ 
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the value of _code
     */ 
    public function code()
    {
        return $this->_code;
    }
}