<?php

/**
 * FOOTUP - 0.1.5 - 03.2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Utils;

use Footup\Orm\BaseModel;

class Shared{
    
    /**
     * Models instances
     * 
     * @var array<string,BaseModel>
     */
    public static $models = [];

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

        if(isset(self::$models[$class])){
            return self::$models[$class];
        }
        $initializable = "App\\Model\\$class";
        return self::$models[$class] = new $initializable;
    }

}