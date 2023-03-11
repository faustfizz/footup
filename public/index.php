<?php
/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package FOOTUP
 * @version 0.1
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
 * Le Dossier ROOT du projet
 */
defined('ROOT_PATH') or define('ROOT_PATH', realpath(__DIR__.DS.'/../').DS);

/**
 * Le dossier publique
 */
defined('BASE_PATH') or define('BASE_PATH', __DIR__ .DS);

/**
 * @todo Configure ceci pour pointer au dossier contenant vos fichier de configuration
 */
defined('CONFIG_PATH') or define('CONFIG_PATH', realpath(__DIR__.DS.'/../app/Config').DS);
defined('APP_PATH') or define('APP_PATH', realpath(CONFIG_PATH."..").DS);

/**
 * C'est ici que je charge vos contantes donc ne faites pas de vos constates une partie très importante
 * du framework mais plutôt de votre application
 */
if(file_exists(CONFIG_PATH."Constants.php"))
	require_once(CONFIG_PATH."Constants.php");

/**
 * @todo Vous pouvez modifier ceci dans le cas où le dossier système n'est pas le dossier core
 * @example - require_once(ROOT_PATH.'sys/Boot.php');
 */
require_once(ROOT_PATH.'core/Boot.php');