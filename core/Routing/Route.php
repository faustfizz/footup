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

class Route
{
    public const CONTROLLER_DELIMITER = '@';

    /**
     * Lang de l'url
     * 
     * @var string
     */
    protected $lang;

    /**
     * Path de l'url
     *
     * @var string
     */
    protected $uri;

    /**
     * Controller
     *
     * @var string|callable
     */
    protected $handler;

    /**
     * Méthode
     *
     * @var string
     */
    protected $method;

    /**
     * Arguments de l'url pour cette route
     *
     * @var array
     */
    protected $args = [];

    /**
     * Contructeur
     *
     * @param string $uri
     * @param string|\Closure|\callable $handler
     */
    public function __construct(string $uri, $handler)
    {
        $this->uri = rtrim($uri, '/') ?: '/';
        $this->loadHandler($handler);
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param array $args
     *
     * @return \Footup\Routing\Route
     */
    public function withArgs(array $args): Route
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Retrieves the route arguments
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Charger l'action
     *
     * @param string $handler
     */
    public function loadHandler($handler): void
    {
        $config = new Config;
        // If we received a callable, convert it into a controller
        if (is_callable($handler)) {
            $this->handler = $handler;
            $this->method = $config->config['default_method'];

            return;
        }

        // If we did receive neither a callable nor a string, this is just a wrong argument, so bail
        if (! is_string($handler)) {
            throw new Exception(text("Http.controllerBailed", [gettype($handler)]));
        }

        // Split the handler string at the delimiter character, so we receive class and method
        [$controller, $method] = explode(static::CONTROLLER_DELIMITER, $handler);
        //die('CNT '.$method);

        // Set class name and method. If no method has been specified
        $method = $method ?? $config->config['default_method'];

        // No such class - bail.
        if (! class_exists($controller)) {
            throw new Exception(text("Http.controllerNotFound", [$controller]));
        }

        // Create a new instance of the controller
        //$controller = new $controller();

        // No such method - bail.
        if (! method_exists($controller, $method)) {
            throw new Exception(text("Http.methodNotFound", [$method, $controller]));
        }

        $this->handler = $controller;
        $this->method = $method;
    }

    /**
     * Recupere l'Instance du controlleur
     *
     * @return \Footup\Controller|callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Recupere la méthode
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }


    /**
     * Get lang de l'url
     *
     * @return  string
     */ 
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set lang de l'url
     *
     * @param  string  $lang  Lang de l'url
     *
     * @return  self
     */ 
    public function setLang(string $lang)
    {
        $this->lang = $lang;

        return $this;
    }
}
