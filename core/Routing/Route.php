<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Routing
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Routing;
use Footup\Config\Config;
use Exception;
use Footup\Utils\Shared;

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
     * Méthode
     *
     * @var string
     */
    protected $name = '';

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
     * @param string|array    $options     the format is ["as" => "route_name", "name" => "route_name"]
     * // You use as or name to define your name_route or simply a string
     */
    public function __construct(string $uri, $handler, $options = null)
    {
        $this->uri = rtrim($uri, '/') ?: '/';
        $this->loadHandler($handler);
        if(!empty($options))
        {
            $this->setName((is_string($options) ? $options : $options[0]));
        }
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
        /**
         * @var Config
         */
        $config = Shared::loadConfig();
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
        list($controller, $method) = explode(static::CONTROLLER_DELIMITER, $handler);

        // Set class name and method. If no method has been specified
        $method = $method ?? $config->config['default_method'];

        // No such class - bail.
        if (! class_exists($controller)) {
            throw new Exception(text("Http.controllerNotFound", [$controller]));
        }

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

    /**
     * Get méthode
     *
     * @return  string
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set méthode
     *
     * @param  string  $name  Méthode
     * @return  self
     */ 
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }
}
