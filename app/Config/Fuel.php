<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace App\Config;

/**
 * ---------------------------------------
 * The Class that you config your structure directory for ease
 * ---------------------------------------
 * 
 * All that you can configure here, you can at the .env file but we did this file to indicate where to load the 
 * core files and where you hold your app and where to locate your views.
 * 
 * I did it also for ease to me to declare all contants related to path
 */
class Fuel
{
    /**
     * The app directory
     *
     * @var string
     */
    const appDir = __DIR__ . "/../";

    /**
     * The directory of the core files
     *
     * @var string
     */
    const sysDir = __DIR__ . "/../../core/";

    /**
     * Yo ! Where you hold your view files
     *
     * @var string
     */
    const viewDir = __DIR__ . "/../View/";
}