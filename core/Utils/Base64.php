<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Utils
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Utils;

class Base64{

    /**
     * Save base64 data to file
     *
     * @param string $base64
     * @param string|null $filename
     * @param string $prefix
     * @return string|false return the file name to store in database
     */
    public static function base64ToFile($base64, $filename = null, $prefix = 'file')
    {
        preg_match("/\/(.*?);/", $base64, $match);
        $extension = $match[1];
        if (($prefix == 'file' || is_null($prefix)) && preg_match("/:(.*?)\//", $base64, $matchPrefix)) {
            $prefix = $matchPrefix[1];
        }

        if ($filename) {
            $filename = strtr("$prefix-$filename.$extension", [".$extension" => ".$extension"]);
        }else{
            $filename = "$prefix-".Str::random().".$extension";
        }

        $fileData = file_get_contents($base64);
        
        if(file_put_contents(Shared::loadConfig()->store_dir . $filename . '.'. $extension, $fileData)) {
            return $filename;
        }
        return false;
    }

    /**
     * Read a file and return base64 data
     *
     * @param string $filepath
     * @return string|null
     */
    public static function fileToBase64($filepath)
    {
        if ($filepath && file_exists($filepath)) {
            $mime = mime_content_type($filepath);
            $data = file_get_contents($filepath);
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        }
        return null;
    }
    
}