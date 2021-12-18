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

use Closure;
use Footup\Files\File;

class Request
{

    protected $server = [];

    protected $env = [];

    protected $cookie = [];

    protected $get = [];

    protected $post = [];

    protected $files = [];

    protected $request = [];

    public function __construct()
    {
        $this->server  = &$_SERVER;
        $this->env     = &$_ENV;
        $this->cookie  = &$_COOKIE;
        $this->get     = &$_GET;
        $this->post    = &$_POST;
        $this->files   = &$_FILES;
        $this->request = &$_REQUEST;
    }

    /**
     * @return string|null
     */
    public function ip($ks = ['REMOTE_ADDR'])
    {
        foreach ($ks as $k) {
            $ip = $this->server($k);
            if ($ip !== null) {
                return $ip;
            }
        }
        return null;
    }


    /**
     * @param $name
     * @return mixed|null
     */
    public function server($name = null, $default = null)
    {
        $kname = is_null($name) ? null : mb_strtoupper($name);

        if (is_null($kname)) {
            return $default;
        }

        if (isset($this->server[$kname]) || isset($this->server[$name])) {
            return $this->server[$kname] ?? $this->server[$name];
        }
        $kname = strtolower($kname);
        $name = strtolower($name);
        if (isset($this->server[$kname]) || isset($this->server[$name])) {
            return $this->server[$kname] ?? $this->server[$name];
        }
        $kname = str_replace('_', '-', $kname);
        $name = str_replace('_', '-', $name);
        if (isset($this->server[$kname]) || isset($this->server[$name])) {
            return $this->server[$kname] ?? $this->server[$name];
        }
        $kname = str_replace('-', '_', $kname);
        $name = str_replace('-', '_', $name);
        if (isset($this->server[$kname]) || isset($this->server[$name])) {
            return $this->server[$kname] ?? $this->server[$name];
        }

        return $this->header($name, $default);
    }

    /**
     * User Agent
     * 
     * @return string
     */
    public function ua()
    {
        return $this->header('user_agent');
    }

    protected function getFromArr($arr, $key, $default = null)
    {
        if ($key === null) {
            return $arr;
        }
        return isset($arr[$key]) ? $arr[$key] : $default;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed|null
     */
    public function get($key = null, $default = null)
    {
        return $this->getFromArr($this->get, $key, $default);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function post($key = null, $default = null)
    {
        return $this->getFromArr($this->post, $key, $default);
    }

    /**
     * @param int $i
     * @return mixed|null
     */
    public function arg($i = null, $default = null)
    {
        global $argv;
        return $this->getFromArr($argv, $i, $default);
    }


    /**
     * @param $key
     * @return mixed|null
     */
    public function request($key = null, $default = null)
    {
        return $this->getFromArr($this->request, $key, $default);
    }


    /**
     * @param $key
     * @return mixed|null
     */
    public function cookie($key = null, $default = null)
    {
        return $this->getFromArr($this->cookie, $key, $default);
    }

    /**
     * @return string
     */
    private function input()
    {
        return file_get_contents('php://input');
    }

    /**
     * Retrouve des données envoyées au format JSON
     *
     * @param string|null $key
     * @param boolean $return_object
     * @param integer $depth
     * @param integer $options
     * @return mixed
     */
    public function json($key = null, $return_object = true, $depth = 512, $options = 0)
    {
        $json = (object)json_decode($this->input(), true, $depth, $options);
        return is_null($key) ? ($return_object === true ? $json : (array)$json) : (isset($json->{$key}) ? $json->{$key} : false);
    }

    /**
     * @param string $field
     * @return File[]|false
     */
    public function files($field = null)
    {
        $files = [];
        if (!is_null($field) && isset($this->files[$field])) {
            foreach ($this->files[$field] as $name => $fs) {
                foreach ($fs as $i => $val) {
                    $files[$i][$name] = $val;
                }
            }

            return array_map(function ($item) {
                return new File($item);
            }, $files);
        } else {
            foreach ($this->files as $name => $fs) {
                $keys = array_keys($fs);
                foreach ($keys as $k => $v) {
                    foreach ($fs[$v] as $i => $val) {
                        $files[$name][$i][$v] = $val;
                    }
                }
            }

            return !empty($files) ? array_map(function ($item) {
                foreach ($item as $a => $e) {
                    $item[$a] = new File($e);
                };
                return $item;
            }, $files) : false;
        }

        return false;
    }

    /**
     * @param string $field
     * @return File|false
     */
    public function file($field = null)
    {
        if (!is_null($field) && isset($this->files[$field]['name'])) {
            return new File($this->files[$field]);
        } else {
            $k = array_keys($this->files);
            return isset($k[0]) ? new File($this->files[$k[0]]) : false;
        }
        return false;
    }

    /**
     * @param bool $upper
     * @return string
     */
    public function method($upper = false)
    {
        $m = $this->server('REQUEST_METHOD');
        return $upper === false ? strtolower($m) : strtoupper($m);
    }

    /**
     * @return bool
     */
    public function isJson()
    {
        return $this->ajax() || strpos($this->server('HTTP_ACCEPT'), '/json') !== false;
    }

    /**
     * @param  string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        if ($value = $this->request($key)) {
            return $value;
        }

        $value = !empty($this->file($key)) ? $this->file($key) : null;

        if(!is_null($value))
        {
            return $value;
        }

        $value = $this->json($key);

        if(isset($value) && !is_array($value) && !is_object($value))
        {
            return $value;
        }

        $value = $this->env($key);

        if(isset($value) && !is_array($value))
        {
            return $value;
        }

        return null;
    }

    /**
     * @return string
     */
    public function uri(): string
    {
        return preg_replace('/\+/', '/', $this->server('REQUEST_URI'));
    }

    /**
     * @param bool $withQuery
     * @return string
     */
    public function url($withQuery = true): string
    {
        return $this->scheme(true) . $this->domain() . ($withQuery === true ? $this->uri() : $this->path());
    }

    /**
     * @return string
     */
    public function path(): string
    {
        $root = explode('/', $this->server('PHP_SELF'));
        array_pop($root);
        $root = implode('/', $root);

        return rtrim(preg_replace('/\?.*/', '', preg_replace('/\/+/', '/', str_replace($root, '', $this->server('REQUEST_URI')))), '/') ?: '/';
    }

    /**
     * @param string $index
     * @return string|array|null
     */
    public function query($index = null)
    {
        $value = $this->get($index);
        return is_string($value) ? urldecode($value) : $value;
    }

    /**
     * @return string
     */
    public function domain(): string
    {
        return $this->server('SERVER_NAME');
    }

    /**
     * @return string
     */
    public function scheme(bool $suffix = false): string
    {
        return $suffix ? $this->server('REQUEST_SCHEME') . '://' : $this->server('REQUEST_SCHEME');
    }

    /**
     * User Agent
     * 
     * @return string
     */
    public function uagent(): string
    {
        return $this->ua();
    }

    /**
     * @return string
     */
    public function referer(): ?string
    {
        return $this->header('referer');
    }

    /**
     * @return bool
     */
    public function secure(): bool
    {
        return $this->server('HTTPS') === 'on';
    }

    /**
     * @return bool
     */
    public function ajax(): bool
    {
        return mb_strtoupper($this->server('X_REQUESTED_WITH')) === 'XMLHTTPREQUEST';
    }

    /**
     * @param  string|null $name
     * @return mixed
     */
    public function header(string $keyname = null, $default = null)
    {
        $kname = is_null($keyname) ? null : 'HTTP_' . mb_strtoupper($keyname);
        if (is_null($kname)) {
            return $default;
        }

        if (isset($this->server[$kname])) {
            return $this->server[$kname];
        }
        $kname = strtolower($kname);
        if (isset($this->server[$kname])) {
            return $this->server[$kname];
        }
        $kname = str_replace('-', '_', $kname);
        if (isset($this->server[$kname])) {
            return $this->server[$kname];
        }
        $kname = str_replace('_', '-', $kname);
        if (isset($this->server[$kname])) {
            return $this->server[$kname];
        }

        return $default;
    }

    /**
     * @param  bool $server
     * @return string
     */
    public function realIp(bool $server = false): string
    {
        if ($server) {
            return $this->server('server_addr') ?? '127.0.0.1';
        }

        return $this->server('client_ip') ?? $this->server('x_forwarded_for') ?? $this->server('remote_addr');
    }

    /**
     * @return int
     */
    public function port(): int
    {
        return $this->server('server_port');
    }

    /**
     * @param  mixed ...$patterns
     * @return bool
     */
    public function is(...$patterns)
    {
        $path = rawurldecode($this->path());

        foreach ($patterns as $pattern) {
            if ($pattern == $path) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function env($name = null, $default = null)
    {
        $kname = is_null($name) ? null : mb_strtoupper($name);

        if (is_null($kname)) {
            return $default;
        }

        if (isset($this->env[$kname]) || isset($this->env[$name])) {
            return $this->env[$kname] ?? $this->env[$name];
        }
        $kname = strtolower($kname);
        $name = strtolower($name);
        if (isset($this->env[$kname]) || isset($this->env[$name])) {
            return $this->env[$kname] ?? $this->env[$name];
        }
        $kname = str_replace('_', '-', $kname);
        $name = str_replace('_', '-', $name);
        if (isset($this->env[$kname]) || isset($this->env[$name])) {
            return $this->env[$kname] ?? $this->env[$name];
        }
        $kname = str_replace('-', '_', $kname);
        $name = str_replace('-', '_', $name);
        if (isset($this->env[$kname]) || isset($this->env[$name])) {
            return $this->env[$kname] ?? $this->env[$name];
        }

        return $this->server($name, $default);
    }

    /**
     * @param string $index
     * @param mixed $value
     * @return self
     */ 
    public function setEnv($index, $value)
    {
        $this->env[$index] = $value;

        return $this;
    }

    /**
     * Eviter les erreur de conversion d'Objet vers string
     *
     * @return string
     */
    public function __toString()
    {
        return "";
    }

}
