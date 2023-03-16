<?php
/**
 * FOOTUP - 0.1.5 - 03.2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package FOOTUP
 * @version 0.2
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

// Valid PHP Version?
$minPHPVersion = '7.1';
if (version_compare(PHP_VERSION, $minPHPVersion, '<'))
{
	die("Your PHP version must be {$minPHPVersion} or higher to run FootUp. Current version: " . PHP_VERSION);
}
unset($minPHPVersion);

/**
 * On se positionne dans le dossier public
 */
chdir(__DIR__);

// Directory Separator
defined("DS") or define("DS", DIRECTORY_SEPARATOR);

/**
 * Le dossier publique
 */
defined('BASE_PATH') or define('BASE_PATH', __DIR__ .DS);

/**
 * @todo Configure ceci pour pointer au dossier contenant vos fichier de configuration
 */
defined('APP_PATH') or define('APP_PATH', realpath(__DIR__."/../app").DS);
defined('CONFIG_PATH') or define('CONFIG_PATH', realpath(APP_PATH.'Config').DS);

/**
 * Le Dossier ROOT du projet (Where to find .env)
 */
defined('ROOT_PATH') or define('ROOT_PATH', realpath(APP_PATH.'../').DS);

/**
 * @todo Vous pouvez modifier ceci dans le cas où le dossier système n'est pas le dossier core
 * @example - require_once(ROOT_PATH.'sys/Boot.php');
 * @return \Footup\Footup
 */
$footup = require(ROOT_PATH.'core/Boot.php');
$footup->terminate();