<?php
/**
 * FOOTUP - 0.1 - 12.01.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package FOOTUP
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
define('BASE_PATH', realpath(dirname(__FILE__)));

spl_autoload_register(function($class){
    $c = explode('\\', $class);
    $str = "";
    for($r = 0; $r < count($c)-1; $r++){
        $str .= strtolower($c[$r]).DIRECTORY_SEPARATOR;
    }
    require_once(BASE_PATH.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.$str.ucfirst(end($c)).'.php');
});

use App\Config\Config;

$config = new Config();
$uri = explode('/', trim($_SERVER["REQUEST_URI"], "/"));
// print_r($uri); die();
if(is_array($uri) && $uri[0] != null){
    $controller = "App\Controller\\".ucfirst($uri[0]);
    $method = isset($uri[1]) ? $uri[1] : "index";

    unset($uri[0], $uri[1]);
    
    (new $controller())->{$method}(...$uri);
}else{
    (new App\Controller\Home())->index();
}