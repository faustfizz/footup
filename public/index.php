<?php
/**
 * FOOTUP - 0.1.6-Alpha - 2021 - 2023
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package FOOTUP
 * @version 0.4
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

// Valid PHP Version?
$minPHPVersion = '7.4';
if (version_compare(PHP_VERSION, $minPHPVersion, '<'))
{
	die("Your PHP version must be {$minPHPVersion} or higher to run FootUp. Current version: " . PHP_VERSION);
}
unset($minPHPVersion);

defined("DS") or define("DS", DIRECTORY_SEPARATOR);

/**
 * Le dossier public
 */
defined('BASE_PATH') or define('BASE_PATH', __DIR__ .DS);

/**
 * On se positionne dans le dossier public
 */
chdir(__DIR__);

// Let's put the fuel and boot our amazing app.
// Change this accordingly to your app directory
require realpath(BASE_PATH . '../app/Config/Fuel.php');

// Load our friend FootUP.
(require (rtrim(App\Config\Fuel::sysDir, '\\/ ') . DS . 'Boot.php'))->terminate();