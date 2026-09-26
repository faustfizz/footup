<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Debug
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 *
 * 
 * Primarly was:
 * Ouch error handler for PHP.
 *
 * @author      Lotfio Lakehal <lotfiolakehal@gmail.com>
 * @copyright   2018 Lotfio Lakehal
 * @license     MIT
 *
 * @link        https://github.com/lotfio/ouch
 */

namespace Footup\Debug;

require_once __DIR__. '/Support/functions.php';

class View
{
    /**
     * write line method.
     *
     * @param string $line
     * @param int    $color
     * @param int    $bg
     * @param int    $bold
     *
     * @return bool|int
     */
    public static function writeLn($line, $color = 37, $bg = 48, $bold = 0)
    {
        if ((strpos(php_uname('v'), 'Windows 7') === false)) { // if not windows 7
            $line = "\e[".$bold.';'.$color.';'.$bg.'m'.$line."\e[0m";
        }

        return fwrite(STDOUT, $line);
    }

    /**
     * render view method.
     *
     * @param string $file view file name
     * @param object $ex   exception
     *
     * @return string
     */
    public static function render($file, $ex, $env)
    {
        ob_get_clean(); // remove html before errors
        if (strtolower($env) === 'prod') {
            static::renderProduction();
        }

        if (php_sapi_name() === 'cli') { // if cli

            self::writeLn("\n   ");
            self::writeLn(' => '.get_class($ex).' ', '37', '41', '3');
            self::writeLn("\n\n          ");
            self::writeLn(wordwrap($ex->message, 100), '32');

            self::writeLn("\n\n   ");
            self::writeLn(' => File  : ', '33');
            self::writeLn($ex->file, '32');

            self::writeLn("\n   ");
            self::writeLn(' => Line  : ', '33');
            self::writeLn($ex->line, '32');

            self::writeLn("\n\n");

            self::writeLn(rtrim(readErrorFileConsole($ex->file, $ex->line, false)));

            self::writeLn("\n\n    => Code  :  ", '33');
            self::writeLn(($ex->type), '32');

            self::writeLn("\n    => Trace :  ", '33');
            self::writeLn(json_encode(array_slice($ex->trace, 0, 2)), '32');
            self::writeLn("\n");

            die;
        }

        return require ouch_views($file);
    }

    /**
     * render production.
     *
     * @return void
     */
    public static function renderProduction(): void
    {
        if (php_sapi_name() === 'cli') {
            die(
                self::writeLn("\n    Woops ! an error occurred.  \n", '37', '41')
            );
        }

        // if http
        http_response_code(500);
        $view = file_get_contents(app()->getUiPath()."/views/errors/500.html");
        die(
            strtr($view, ["{title}" => "Internal Error", "{status}" => "Error 500", "{message}" => "Internal Server Error !"])
        );
    }
}
