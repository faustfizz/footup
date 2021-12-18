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
$minPHPVersion = '7.3';
if (version_compare(PHP_VERSION, $minPHPVersion, '<'))
{
	die("Your PHP version must be {$minPHPVersion} or higher to run FootUp. Current version: " . PHP_VERSION);
}
unset($minPHPVersion);

/**
 * On se positionne dans le dossier public
 */
chdir(__DIR__);

/**
 * Le Dossier ROOT du projet
 */
defined('ROOT_PATH') or define('ROOT_PATH', realpath(__DIR__.DIRECTORY_SEPARATOR.'/../').DIRECTORY_SEPARATOR);

/**
 * Le dossier publique
 */
defined('BASE_PATH') or define('BASE_PATH', __DIR__ .DIRECTORY_SEPARATOR);

/**
 * @todo Configure ceci pour pointer au dossier contenant vos fichier de configuration
 */
defined('CONFIG_PATH') or define('CONFIG_PATH', realpath(__DIR__.DIRECTORY_SEPARATOR.'/../app/Config').DIRECTORY_SEPARATOR);
defined('APP_PATH') or define('APP_PATH', realpath(CONFIG_PATH."..").DIRECTORY_SEPARATOR);

/**
 * @todo Vous pouvez modifier ceci dansle cas où le dossier système n'est pas le dossier core
 * @example - require_once(ROOT_PATH.'sys/Boot.php');
 */
require_once(ROOT_PATH.'core/Boot.php');