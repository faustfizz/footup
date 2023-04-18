<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Files
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Files;

use Exception;
use SplFileInfo;

class File extends SplFileInfo
{
    /**
     * @var object
     */
    protected $file;

    /**
     * @var boolean
     */
    protected $moved = false;

    /**
     * @var \SplFileInfo
     */
    protected $moved_file = null;

    /**
     * @param array $file
     */
    public function __construct(array $file)
    {
        $this->file = $file;
        parent::__construct($file['tmp_name']);
    }

    /**
     * Alias of rename
     *
     * @param string $name
     * @return File
     */
    public function setName(string $name)
    {
        return $this->rename($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if(method_exists($this, 'get'.ucfirst($name)))
        {
            return $this->{'get'.ucfirst($name)}(...$arguments);
        }
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function __get($name)
    {
        if(property_exists($this, $name))
        {
            return $this->{$name};
        }

        if(isset($this->file[$name]))
        {
            return $this->file[$name];
        }

        if($name === "random_name")
        {
            return $this->random_name()->name();
        }

        return null;
    }

    public function __set($name, $value)
    {
        if(isset($this->file[$name]))
        {
            return $this->file[$name] = $value;
        }

        if(property_exists($this, $name))
        {
            return $this->{$name} = $value;
        }
    }

    /**
    * @return bool
    */
    public function isImage() : bool
    {
        return in_array($this->ext(), ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "webp", "png"]);
    }

    /**
    * @return string
    */
    public function name() : string
    {
        return $this->file['name'];
    }

    /**
    * @return string
    */
    public function tmp_name() : string
    {
        return $this->file['tmp_name'];
    }

    /**
     * @return string
     */
    public function type() : string
    {
        return $this->file['type'];
    }

    /**
     * @return int
     */
    public function size() : int
    {
        return $this->file['size'];
    }

    /**
     * @param string $format
     * @param bool $return_string true to return as string representation
     * @return int|string
     */
    public function counted_size($format = 'MB', $return_string = false) : int
    {
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $flip = array_flip($sizes);
        $i = isset($flip[$format]) ? $flip[$format] : floor(log($this->size()) / log(1024));
        $size = $this->size() / pow(1024, $i);
        return $return_string ? $this->file_size($format) : $size;
    }

    /**
     * Alias of getExtension()
     *
     * @return string
     */
    public function ext()
    {
        return $this->getExtension();
    }

    /**
     * Retrouve l'extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return pathinfo(mb_strtolower( $this->name() ), PATHINFO_EXTENSION);
    }
    
    public function rename(string $name)
    {
        $ext = $this->ext();
        $name = strtr($name, [".$ext" => ""]);
        $this->name = (preg_replace('/[^\00-\255]+/u', '', $name).'.'.$ext);
        return $this;
    }
    
    /**
     * set a random name to the file
     *
     * @param integer $len
     * @return File
     */
    public function random_name($len = 20)
    {
        $this->name = substr(uniqid(date("His"), true), 0, $len).'.'.$this->ext();
        return $this;
    }

    /**
     * Format Bytes
     *
     * @param string $format
     * @return string formated bytes
     */
    public function file_size($format = 'MB')
    {
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $flip = array_flip($sizes);
        $i = isset($flip[$format]) ? $flip[$format] : floor(log($this->size()) / log(1024));
        return number_format($this->size() / pow(1024, $i), 2, '.', ' '). ' ' . @$sizes[$i];
    }

    /**
     * @return int
     */
    public function error() : int
    {
        return $this->file['error'];
    }

    /**
     * @param  string $destination
     * @return bool
     */
    public function save(string $destination = null, bool $replace = true) : bool
    {
        return $this->move($destination, $replace);
    }

    /**
     * @param  string $destination
     * @return bool
     */
    public function move(string $destination = null, bool $replace = true) : bool
    {
        if ($this->error() > 0) {
            return false;
        }

        if (is_null($destination)) {
            $this->name = $this->random_name()->name();
            $destination = STORE_DIR.$this->name();
        }

        if(stripos($destination, '/') === false)
        {
            $destination = STORE_DIR.$this->rename($destination);
        }

        if (! $replace && file_exists($destination)) {
            $this->moved_file = new SplFileInfo($destination);
            return $this->moved = true;
        }
        
        try {
            $this->moved = move_uploaded_file($this->file['tmp_name'], $destination);
            $this->moved == true && $this->moved_file = new SplFileInfo($destination);
        } catch (Exception $exception) {
            throw new Exception(text("File.cannotMove", [$this->name, $destination, $exception->getMessage()]));
        }

        return $this->moved;
    }
}
