<?php

/**
 * FOOTUP - 0.1.6 - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Utils
 * @version 0.2
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Utils;

use Footup\Http\{Request, Session};
use Footup\Orm\BaseModel;
use Footup\Routing\Router;
use Footup\Config\Config;
use Footup\Utils\Validator\Validator;

class Shared{
    
    /**
     * Models instances
     * 
     * @var array<string,BaseModel>
     */
    protected static $models = [];

    /**
     * Router instance
     * 
     * @var Router
     */
    protected static $router;

    /**
     * Config instance
     * 
     * @var Config
     */
    protected static $config;
    
    /**
     * Session instance
     * 
     * @var Session
     */
    protected static $session;

    /**
     * Validator instance
     * 
     * @var Validator
     */
    protected static $validator;

    /**
     * Load a shared Model
     *
     * @param string $class
     * @param boolean $shared
     * @throws \Exception
     * @return BaseModel
     */
    public static function loadModels($class, $shared = true)
    {
        if(!class_exists("App\\Model\\".$class, true)){
            throw new \Exception(text("Core.classNotFound", ["App\\Model\\".$class]));
        }

        if(isset(self::$models[$class]) && $shared){
            return self::$models[$class];
        }
        $initializable = "App\\Model\\$class";
        return self::$models[$class] = new $initializable;
    }

    /**
     * Load a shared Router
     *
     * @param boolean $shared
     * @throws \Exception
     * @return Router
     */
    public static function loadRouter($shared = true)
    {
        if(isset(self::$router) && $shared){
            return self::$router;
        }
        return self::$router = new Router(new Request);
    }

    /**
     * Load a shared Config
     *
     * @param boolean $shared
     * @throws \Exception
     * @return Config
     */
    public static function loadConfig($shared = true)
    {
        if(isset(self::$config) && $shared){
            return self::$config;
        }
        return self::$config = new Config();
    }

    /**
     * Load a shared Config
     *
     * @param boolean $shared
     * @throws \Exception
     * @return Session
     */
    public static function loadSession($shared = true)
    {
        if(isset(self::$session) && $shared){
            return self::$session;
        }
        return self::$session = new Session();
    }

    /**
     * Load a shared Config
     *
     * @param boolean $shared
     * @throws \Exception
     * @return Validator
     */
    public static function loadValidator($shared = true)
    {
        if(isset(self::$validator) && $shared){
            return self::$validator;
        }
        return self::$validator = new Validator();
    }

}