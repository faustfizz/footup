<?php

/**
 * FOOTUP - 0.1.3 - 11.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Http
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Http;

class Session
{
    private $data = [];

    /**
     * Session constructor.
     * @param null $id session.id
     */
    public function __construct()
    {
        if(session_status() === PHP_SESSION_NONE){
            @session_start();
        }
        $this->data = &$_SESSION;
    }
    
    /**
     * Magic function
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return in_array($name, ["id", "session_id"]) ? $this->id() : $this->get($name);
    }

    public function __set($name, $val)
    {
        return $this->set($name, $val);
    }
    
    /**
	 * Push new value onto session value that is array.
	 *
	 * @param string $key  Identifier of the session property we are interested in.
	 * @param array  $data value to be pushed to existing session key.
	 *
	 * @return void
	 */
	public function push(string $key, array $data)
	{
		if ($this->has($key) && is_array($value = $this->get($key)))
		{
			$this->set($key, array_merge($value, $data));
		}
	}

    /**
     * Get session ID
     *
     * @return string|false
     */
    public function id()
    {
        return session_id();
    }

    /**
     * Set data
     *
     * @param mixed $key
     * @param mixed $val
     * @return $this
     */
    public function set($key, $val = null)
    {
        if(is_array($key) && !empty($key)){
            foreach($key as $k => $v){
                $this->data[$k] = $v;
            }
        }else{
            $this->data[$key] = $val;
        }
        return $this;
    }

    /**
     * Set flash data
     *
     * @param mixed $key
     * @param mixed $val
     * @return $this
     */
    public function setFlash($key, $val = null)
    {
        if(is_array($key) && !empty($val)){
            foreach($key as $k => $v){
                $this->data["flash_".$k] = $v;
            }
        }else{
            $this->data["flash_".$key] = $val;
        }
        return $this;
    }

    /**
     * Get flash data
     *
     * @param mixed $key
     * @param mixed $default
     * @param boolean $clear_after
     * @return mixed
     */
    public function flash($key, $default = null, $clear_after = true)
    {
        # code...
        if ($key && $this->has("flash_".$key)) {
            $flashdata = $this->data["flash_".$key];
            if($clear_after)
            {
                unset($_SESSION["flash_".$key]);
            }
            return $flashdata;
        }else{
            return $default;
        }
    }

    /**
     * Set Cookie
     *
     * @param string $name_key
     * @param mixed $value
     * @param int $expires_or_options
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @return boolean
     */
    public function cookie(string $name_key, $value, $expires_or_options = null, $path = "/", $domain = null, $secure = false, $httponly = false)
    {
        !is_null($expires_or_options) or $expires_or_options = time()+60*60*24*30;
        return @setcookie($name_key, $value, $expires_or_options, $path, $domain, $secure, $httponly);
    }

    /**
     * Get a value
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if ($key && $this->has($key)) {
            return $this->data[$key];
        }else{
            return $this->flash($key, $default);
        }
    }

    /**
     * Get all values
     * @param string $type
     * @return object
     */
    public function all($type = 'object')
    {
        return $type === 'object' ? (object)$this->data : $this->data;
    }

    /**
     * Delete key or keys
     *
     * @param string|array $key
     * @return void
     */
    public function del($key = null)
    {
        if(is_array($key)){
            foreach($key as $k){
                if($this->has($k)) unset($this->data[$k]);
            }
        }else{
            if ($this->has($key)) unset($this->data[$key]);
        }
    }

    /**
     * Delete keys
     *
     * @return void
     */
    public function delAll()
    {
        $_SESSION = $this->data = [];
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @param  bool $destroy
     * @return bool
     */
    public function regenerate(bool $destroy = false) : bool
    {
        return session_regenerate_id($destroy);
    }

    public function __toString()
    {
        return "";
    }

    /**
     * @return bool
     */
    public function destroy() : bool
    {
        return session_destroy();
    }
}