<?php
/**
 * FOOTUP FRAMEWORK  2021 - 2023
 * *****************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1.7-alpha.6
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup;

use Exception;
use Footup\Http\RedirectResponse;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Routing\MiddleHandler;
use Footup\Routing\Router;

class Footup
{
    protected $router;
    public const NAME = "FootUP Framework";

    public const VERSION = "0.1.7-alpha.8";

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

        $request = $this->router->getRequest();
        $response = new Response();

        if ($handler instanceof \Closure) {
            $this->endTime($request);
            $responseOrContent = $handler(...array_values($route->getArgs()));

            $this->redirectIfNeeded($responseOrContent);

            if ($responseOrContent)
                return $response->body($responseOrContent ?? '')->send();
        }
        
        /**
         * @var \Footup\Controller $controller
         */
        list($controller, $middleResult) = $this->runMiddles(new $handler(), $method, $request, $response);
        // Recalculate endTime as we can run many Middles before
        $this->endTime($request);

        $this->redirectIfNeeded($middleResult);

        if ($middleResult instanceof Response) {
            $responseOrContent = $controller->__boot($request, $response->body($middleResult))->{$method}(...array_values($route->getArgs()));

            $this->redirectIfNeeded($responseOrContent);

            if ($responseOrContent)
                return $response->body($responseOrContent ?? '')->send();
        }
        
    }

    /**
     * Redirect if we have a RedirectResponse on the way
     *
     * @param mixed $testableResponse
     * @return void
     */
    public function redirectIfNeeded($testableResponse) {
        if ($testableResponse instanceof RedirectResponse) {
            $testableResponse->send();
            exit(0);
        }
    }

    /**
     * Execute les middleWare
     *
     * @param \Footup\Controller|mixed $controller
     * @param string $method
     * @param Request $request
     * @param Response $response
     * @return array<\Footup\Controller, mixed>
     */
    protected function runMiddles($controller, $method, Request $request, Response &$response)
    {
        $middlesStack = [];
        /**
         * For globaux Middles
         */
        foreach ($controller->getGlobalMiddles($method) as $key => $value) {
            if ($value instanceof \Closure || class_exists($value) || $method === $key) {
                $middlesStack[] = $value;
            }
        }

        $class = trim(get_class($controller), '\\');
        /**
         * For spécifiques middles
         * @var string|string[]
         */
        $middles = $controller->getMiddles($class) ?? $controller->getMiddles(rtrim($class, '\\'));

        if ($middles) {
            if ($middles instanceof \Closure || !is_array($middles) && class_exists($middles)) {
                $middlesStack[] = $middles;
            } elseif (is_array($middles)) {
                foreach ($middles as $key => $middle) {
                    if ($middle instanceof \Closure || class_exists($middle) && $method === $key) {
                        $middlesStack[] = $middle;
                    }
                }
            }
        }

        $middleResult = $response;

        if (!empty($middlesStack)) {
            $middleResult = (new MiddleHandler($middlesStack))->dispatch($request, $response);
        }

        return [$controller, $middleResult];
    }

    protected function endTime(Request $request)
    {
        $request->setEnv("end_time", microtime(true));

        list($start_time, $end_time) = [(float) $request->env("start_time"), (float) $request->env("end_time")];

        $request->setEnv("delayed_time", (float) number_format($end_time - $start_time, 4));
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