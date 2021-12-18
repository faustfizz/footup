<?php
/**
 * FOOTUP - 0.1 - 12.01.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/App/Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace App\Config;

use Footup\Http\Request;
use Footup\Routing\Router;

$router = new Router(new Request());
$router->setAutoRoute(true)
    ->get('/', "App\Controller\Home@index")
    ->post('/', "App\Controller\Home@index");