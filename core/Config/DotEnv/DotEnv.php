<?php

namespace Footup\Config\DotEnv;

use Footup\Config\DotEnv\Exception\InvalidPathException;
use function serialize;
use function unserialize;

class DotEnv extends \ArrayObject
{
    private $_path;
    private $_file;
    private $_dir;
    private $_data;

    public function __construct($path = ROOT_PATH, bool $setEnvironmentVariables = true, bool $processSections = true, int $scannerMode = INI_SCANNER_TYPED)
    {
        if(file_exists(rtrim($path, DS).DS.".env"))
        {
            $this->setPath($path);
            $data = self::parseFile($this->_file, $processSections, $scannerMode);
            $this->setData($data);
            if ($setEnvironmentVariables){
                self::setEnvironmentVariables($this->_data);
            }
        }
    }

    public function setPath($path){
        if (is_dir($path)){
            $path = rtrim($path, DS) . DS;
            $this->_path = $path;
            $this->setDir($this->_path);
        } else if (file_exists($path)){
            $this->_path = $path;
            $this->setFile($this->_path);
        }
    }

    public function setDir(string $dir){
        $this->_dir = $dir;
        if (!is_dir($this->_dir)){
            throw new InvalidPathException(text("File.dirNotExist", [$this->_file]));
        }
        $this->_file = $dir . ".env";
        if (!file_exists($this->_file)){
            throw new InvalidPathException(text("File.fileNotExist", [$this->_file]));
        }
    }

    public function setFile(string $file){
        $this->_dir = dirname($file);
        $this->_file = $file;
        if (!file_exists($this->_file)){
            throw new InvalidPathException(text("File.fileNotExist", [$this->_file]));
        }
    }

    public function loadArray(array $array, bool $setEnvironmentVariables, int $scannerMode = INI_SCANNER_TYPED){
        if ($scannerMode == INI_SCANNER_TYPED){
            $array = self::scanArrayTypes($array);
        }
        $this->_data = $array;
        if ($setEnvironmentVariables){
            self::setEnvironmentVariables($this->_data);
        }
    }

    public static function scanArrayTypes(array $array){
        foreach ($array as $property => $value){
            if (is_array($value)){
                $value = self::scanArrayTypes($array);
            } else {
                if (is_string($value)){
                    switch ($value) {
                        case 'true':
                        case 'yes':
                        case 'on':
                        case '1':
                            $value = true;
                        break;
                        case 'false':
                        case 'no':
                        case 'off':
                        case '0':
                            $value = false;
                        break;
                    }
                }
            }
            $array[$property] = $value;
        }
        return $array;
    }

    public function loadString(string $string, bool $setEnvironmentVariables = true, bool $processSections = true, int $scannerMode = INI_SCANNER_TYPED){
        $data = self::parseString($string, $processSections, $scannerMode);
        $this->setData($data);
        if ($setEnvironmentVariables){
            self::setEnvironmentVariables($this->_data);
        }
    }

    public static function parseFile(string $file, bool $processSections = true, int $scannerMode = INI_SCANNER_TYPED):array{
        $newFile = fopen(substr($file, 0, strrpos($file, DS)).'/footup.ini', "w");
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            # code...
            $line = trim($line);
            if(strpos($line, "#") === 0) continue;

            $pos = strpos($line, "#") ? strpos($line, "#") : strlen($line);
            $line = substr($line, 0, $pos);
            fwrite($newFile, $line."\n");
        }
        fclose($newFile);
        $data = parse_ini_file(substr($file, 0, strrpos($file, DS)).'/footup.ini', $processSections, $scannerMode);
        @unlink(substr($file, 0, strrpos($file, DS)).'/footup.ini');
        return $data;
    }

    public static function parseString(string $string, bool $processSections = true, int $scannerMode = INI_SCANNER_TYPED):array{
        return parse_ini_string($string, $processSections, $scannerMode);
    }

    public static function setEnvironmentVariables($iterable, string $accessName=""){
        foreach ($iterable as $variable => $value){
            if(is_object($value))
            {
                $value = (array) $value;
            }
            $_ENV[$variable] = $value;
            $_SERVER[$variable] = $value;
        }
    }

    public static function setEnv($variable, $value){
        if (is_array($value) || is_object($value)){
            self::setEnvironmentVariables($value, $variable);
        } else {
            putenv("$variable=$value");
            $_ENV[$variable] = $value;
            $_SERVER[$variable] = $value;
        }
    }

    public static function arrayToObject($array){
        $object = (object) $array;
        foreach ($object as $variable => $value){
            if (is_array($value)){
                $object->$variable = (object) self::arrayToObject($value);
            }
        }
        return((object) $object);
    }

    public static function objectToArray($object){
        $array = [];
        foreach ($object as $variable => $value){
            if (is_object($value)){
                $array[$variable] = (array) self::objectToArray($value);
            }
        }
        return((array) $array);
    }

    public function setData($data){
        //parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
        parent::__construct($data);
        $this->_data = self::arrayToObject($data);
    }

    public function data(){
        return $this->_data;
    }
    
    public function serialize(): string
    {
        return serialize($this->_data);
    }

    public function unserialize($serialized): void
    {
        $this->setData(unserialize($serialized));
    }

    public function __get($name)
    {
        if ($name[0] == "_") {
            return $this->$name;
        }
        if (is_array($this->_data)){
            if (array_key_exists($name, $this->_data)){
                if (is_array($this->_data[$name]) || is_object($this->_data[$name])){
                    return self::arrayToObject($this->_data[$name]);
                } else {
                    return $this->_data[$name];
                }
            } else {
                return "";
            }
        } else {
            if (isset($this->_data->$name)){
                if (is_array($this->_data->$name) || is_object($this->_data->$name)){
                    return self::arrayToObject($this->_data->$name);
                } else {
                    return $this->_data->$name;
                }
            } else {
                return "";
            }
        }
    }

    public function __set($name, $value)
    {
        if ($name[0] == "_") {
            $this->$name = $value;
        } else {
            if (is_object($this->_data)){
                $this->_data->$name = $value;
            } else {
                $this->_data[$name] = $value;
            }
            //$this[$name] = $value;
            $this->setData($this->_data);
            parent::offsetSet($name, $value);
            self::setEnv($name, $value);
        }
    }

    public function offsetSet($index, $newval): void
    {
        $this->__set($index, $newval);
        parent::offsetSet($index, $newval);
    }

    public function offsetGet($index)
    {
        return parent::offsetGet($index);
    }

    public function offsetExists($index): bool
    {
        return parent::offsetExists($index);
    }

}