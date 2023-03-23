<?php
/**
 * FOOTUP - 0.1.6 - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
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
class Fuel{
    /**
     * The app directory
     *
     * @var string
     */
    public static $appDir = __DIR__."/../";

    /**
     * The directory of the core files
     *
     * @var string
     */
    public static $sysDir = __DIR__."/../../core/";

    /**
     * Yo ! Where you hold your view files
     *
     * @var string
     */
    public static $viewDir = __DIR__."/../View/";
}
