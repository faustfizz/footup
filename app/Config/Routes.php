<?php
/**
 * FOOTUP 
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\App\Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace App\Config;

use Footup\Utils\Shared;

/**
 * Default route is $router->get('/', "App\Controller\Home@index") if autoroute is enabled
 * if we delete all codes in this file, this code $router->setAutoRoute(true)->get('/', "App\Controller\Home@index") will be executed
 * 
 * for using language file by specifying in the uri use the placeholder {lang} or {locale} but in the beginning eg:
 * $router->get('/{lang}/segment1/segment2', "App\Controller\Home@index")
 * $router->get('/{locale}/segment1/segment2', "App\Controller\Home@index")
 */
Shared::loadRouter()->setAutoRoute(true)
    ->get('/', "App\Controller\Home@index");