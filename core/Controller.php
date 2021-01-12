<?php
/**
 * FOOTUP - 0.1 - 12.01.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package FOOTUP/Controller
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Core;
use App\Config\Config;

class Controller {

    public function __construct()
    {
        
    }

    function view( $path , $data = null )
    {
        extract($data);
        $path = trim($path, "/");
        return include_once(Config::$config["view_path"] . $path . Config::$config["view_ext"]);
    }

}