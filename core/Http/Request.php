<?php

/**
 * FOOTUP - 0.1.5 - 03.2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Http
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Http;

use Footup\Config\Config;
use Footup\Files\File;
use Footup\Utils\Arrays\ArrDots;
use Footup\Utils\Shared;
use Footup\Utils\Validator\Validator;

class Request
{
    /**
     * Locale
     *
     * @var string
     */
    public $lang;

    /**
     * Current Controller
     *
     * @var string
     */
    public $controllerName;

    /**
     * Current called controller method
     *
     * @var string
     */
    public $controllerMethod;

    /**
     * Validator
     *
     * @var \Footup\Utils\Validator\Validator
     */
    public $validator;

    /**
     * Data to validate
     *
     * @var array
     */
    protected $data = [];

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

        $this->setLang($this->env("lang"));

        $this->validator = Shared::loadValidator();
    }

    /**
     * Get the Validator class
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Choose the GET data to validate
     *
     * @param mixed $key
     * @param mixed $default
     * @return Request
     */
    public function withGetInput()
    {
        $this->data = array_merge($this->get(), $this->json(null, false));

        return $this;
    }

    /**
     * Choose the POST data to validate
     * 
     * @return Request
     */
    public function withPostInput()
    {
        $this->data = array_merge($this->post(), $this->json(null, false));

        return $this;
    }

    /**
     * Grab the GET, POST data to validate
     * 
     * @return Request
     */
    public function withInput()
    {
        $this->data = array_merge($this->request(), $this->json(null, false));

        return $this;
    }

    /**
     * $data as data to validate
     *
     * @param array $data
     * 
     * @return Request
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Choose the GET data to validate
     *
     * @param mixed $key
     * @param mixed $default
     * @return bool true if everything is ok
     */
    public function validate(array $ruleSet, array|null|object $values = [], string $prefix = null)
    {
        if(empty($values) && !empty($this->data))
        {
            $values = $this->data;
        }

        return $this->validator->validate($values, $ruleSet, $prefix);
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
     * @param $key
     * @return mixed|null
     */
    public function server($key, $default = null)
    {
        $kname = is_null($key) ? null : str_replace('-', '_', mb_strtoupper($key));

        if (is_null($kname)) {
            return $default;
        }

        if (isset($this->server[$kname]) || isset($this->server[$key])) {
            return $this->server[$kname] ?? $this->server[$key];
        }

        return $this->header($key, $default);
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
        $url = parse_url($this->url(false, true));

        $uri = $this->server('REQUEST_URI');

        if(isset($url['path']))
        {
            $uri = strtr($this->server('REQUEST_URI'), [rtrim($url['path'], '/') => ""]);
        }

        return rtrim(preg_replace('/\/+/', '/', $uri), '/') ?: '/';
    }

    /**
     * @param bool $withQuery
     * @param bool $base to return just base_url
     * @return string
     */
    public function url($withQuery = true, $base = false): string
    {
        $base_url = $this->env("base_url") ?? (new Config())->base_url;
        $base_url = trim((string) $base_url, " \n\r\t\v\x00\/");

        if($base === true)
        {
            return $base_url;
        }

		return $withQuery && !empty($this->query()) ? $base_url. $this->path() ."?".http_build_query($this->query(), "_key", "&") : $base_url. $this->path();
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return rtrim(preg_replace('/\?.*/', '', $this->uri()), '/') ?: '/';
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
        return $this->server('HTTPS') === 'on' || $this->server("http_port") === "443";
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
    public function header(string $keyname, $default = null)
    {
        $kname = is_null($keyname) ? null : 'HTTP_' . mb_strtoupper($keyname);

        if (empty($kname)) {
            return $default;
        }

        $kname = str_replace('-', '_', $kname);
        
        return $this->server[$kname] ?? $default;
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
     * @param  string|array $patterns
     * @return bool
     */
    public function is(string|array $patterns)
    {
        $path = rawurldecode($this->path());
        $pattern = strtr( (is_array($patterns) ? implode("/", $patterns) : $patterns), [$this->url(false, true) => ""]);

        return trim($pattern, " \n\r\t\v\x00/") === trim($path, " \n\r\t\v\x00/");
    }
    
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function env($key, $default = null)
    {
        $key = is_null($key) ? null : str_replace('-', '_', mb_strtolower($key));
        $kname = is_null($key) ? null : mb_strtoupper($key);

        if (empty($kname)) {
            return $default;
        }

        if (isset($this->env[$kname]) || isset($this->env[$key])) {
            return $this->env[$kname] ?? $this->env[$key];
        }

        if(strpos($key, ".") && $value = ArrDots::get($this->env, $key, null))
        {
            return $value;
        }

        return $this->server($key, $default);
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


    /**
     * Get the value of lang
     */ 
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set the value of lang
     *
     * @return  self
     */ 
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }
}
