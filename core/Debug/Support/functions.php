<?php

/**
 * FOOTUP -  2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
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
if (!function_exists('ouch_root')) {

    /**
     * root() directory function.
     *
     * @return string root dir path
     */
    function ouch_root()
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('ouch_assets')) {
    /**
     * assets() function path.
     *
     * @param string $file
     *
     * @return string
     */
    function ouch_assets($file)
    {
        return file_get_contents(ouch_root().'resources'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$file);
    }
}

if (!function_exists('ouch_views')) {
    /**
     * views() function path.
     *
     * @param string $file
     *
     * @return string
     */
    function ouch_views($file)
    {
        return ouch_root().'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$file;
    }
}

if (!function_exists('str_last')) {
    /**
     * get last word from a string.
     *
     * @param string $str string
     * @param string $del delimiter
     *
     * @return void
     */
    function str_last($str, $del = '\\')
    {
        $str = explode($del, $str);

        return $str[count($str) - 1];
    }
}

if (!function_exists('unpackError')) {
    /**
     * recursively unpack exception.
     *
     * @param array $array
     *
     * @return void
     */
    function unpackError($array)
    {
        $arr = [];

        foreach ($array as  $key => $value) {
            if (isset($value['args'])) {
                unset($value['args']);
            }
            $arr[] = $value;
        }

        foreach ($arr as $ar) {
            echo '<li>';
            foreach ($ar as $key => $value) {
                echo '<b>'.ucfirst($key).'</b> : '.ucfirst($value).'<br>';
            }
            echo '</li>';
        }
    }
}

if (!function_exists('readErrorFile')) {
    /**
     * read error file function.
     *
     * @param string $file error file
     * @param int    $line error line
     *
     * @return string
     */
    function readErrorFile($fileName, $errorLine, $escape = true)
    {
        $output = '';
        $file = file($fileName);
        $file = array_combine(range(1, count($file)), $file); // change index to 1

        $numberOfLines = count($file);

        $start = $errorLine >= 6 ? $errorLine - 6 : 1;
        $end = ($errorLine + 6) <= $numberOfLines ? $errorLine + 6 : $numberOfLines;

        for ($i = $start; $i <= $end; $i++) {
            if(($i == $start || $i == $end) && empty(trim($file[$i])))
            {
                $file[$i] = "ㅤ".$file[$i]."";
            }
            $output .= ($escape) ? htmlentities($file[$i], ENT_QUOTES, 'UTF-8') : $file[$i];
        }

        return $output;
    }
}

if (!function_exists('readErrorLine')) {
    /**
     * read error line function.
     *
     * @param string $file error file
     * @param int    $line error line
     *
     * @return string
     */
    function readErrorLine($fileName, $errorLine)
    {
        $file = file($fileName);
        $file = array_combine(range(1, count($file)), $file); // change index to 1

        return isset($file[$errorLine]) ? trim($file[$errorLine]) : "line : $errorLine";
    }
}

if (!function_exists('readErrorFileConsole')) {
    /**
     * read error file function.
     *
     * @param string $file error file
     * @param int    $line error line
     *
     * @return string
     */
    function readErrorFileConsole($fileName, $errorLine, $print)
    {
        $output = '';
        $file = file($fileName);
        $file = array_combine(range(1, count($file)), $file); // change index to 1

        $numberOfLines = count($file);

        $start = $errorLine >= 6 ? $errorLine - 4 : 1;
        $end = ($errorLine + 4) <= $numberOfLines ? $errorLine + 4 : $numberOfLines;

        $lineSpace = strlen($end); // number of charachters in line to determin how much space

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $errorLine) {
                $output .= '       '."\e[3;39;33m-> \e[0m".$start++;
            } else {
                $output .= '          '.$start++;
            }

            if ($numberOfLines >= 10) {
                $output .= str_repeat(' ', $lineSpace - strlen($i) + 1).'> ';
            } // +1 desired space

            if ($numberOfLines < 10) {
                $output .= ' > ';
            }

            if ($i == $errorLine) {
                if ((strpos(php_uname('v'), 'Windows 7') === false)) { // if not windows 7
                    $output .= "\e[3;39;41m".$file[$i]."\e[0m";
                } else {
                    $output .= $file[$i];
                }
            } else {
                $output .= $file[$i];
            }
        }

        return $output;
    }
}
