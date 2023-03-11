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

use App\Config\Config;
use ArrayObject;
use DateTime;
use Exception;
use JsonSerializable;

class Response
{

    /**
     * @var array;
     */
    protected $header = [];

    /**
     * @var string
     */
    protected $body;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var array
     */
    protected $message = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Unused',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m A Teapot',
        419 => 'Authentication Timeout',
        420 => 'Enhance Your Calm',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        424 => 'Method Failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'No Response',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Unavailable For Legal Reasons',
        494 => 'Request Header Too Large',
        495 => 'Certificate Error',
        496 => 'No Certificate',
        497 => 'HTTP to HTTPS',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        598 => 'Network Read Timeout Error',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * @param mixed $data
     * @param int   $status
     * @param array $header
     */
    public function __construct($data = '', $status = 200, $header = [])
    {

        $this->header = $header;

        foreach ($header as $key => $value) {
            $this->header($key, $value);
        }

        if (! isset($this->header['Content-Type'])) {
            $this->header('Content-Type', 'text/html; charset=UTF-8');
        }

        if (! isset($this->header['Cache-Control'])) {
            $this->header('Cache-Control', 'no-store, max-age=0, no-cache');
        }

        if (! isset($this->header['Date'])) {
            $this->header('Date', (new DateTime())->format('D, d M Y H:i:s') . ' GMT');
        }

        $this->status = $status;

        try {
            if(empty($data))
            {
                $data = $this->message[$status];
            }
            $this->body($data);
        } catch (Exception $exception) {
            throw new Exception(text("Http.invalidBodyType", [gettype($data)]));
        }
    }

    public function __call($name, $arguments)
    {
        $status = (int)substr($name, 4, 3);
        $method = substr($name, 0, 4);

        if($method === 'call' && in_array($status, array_keys($this->message)))
        {
            if(isset($arguments[1]))
            {
                $this->header($arguments[1]);
            }
            return $this->status($status)->body($arguments[0] ?? $this->message[$status]);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->send();
    }

    /**
     * @param  string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->$property ?? null;
    }

    /**
     * @param  mixed $data
     * @param  int   $status
     * @param  array $header
     * @return Response
     */
    public function make($data, $status = 200, array $header = [])
    {
        if (! is_int($status)) {
            $status = 500;
        }

        if ($data instanceof Response) {
            return $data;
        }

        if (is_array($data) || $data instanceof ArrayObject || $data instanceof JsonSerializable) {
            return $this->json($data, false, $status, $header);
        }

        foreach ($header as $key => $value) {
            $this->header($key, $value);
        }

        return $this->status($status)->body($data);
    }

    /**
     * @param  string $body
     * @return $this
     */
    public function body($body)
    {
        $this->body = $this->check($body);

        return $this;
    }

    /**
     * @param  string $body
     * @return $this
     */
    public function prepend($body)
    {
        $this->body = $this->check($body) . $this->body;

        return $this;
    }

    /**
     * @param  string $body
     * @return $this
     */
    public function append($body)
    {
        $this->body .= $this->check($body);

        return $this;
    }

    /**
     * @param  string $body
     * @return string
     */
    private function check($body)
    {
        if (! is_null($body) && ! is_string($body) && ! is_numeric($body) && ! is_callable([$body, '__toString'])) {
            throw new Exception(text("Http.invalidBodyType", [gettype($body)]));
        }

        if (is_object($body) && is_callable([$body, '__toString'])) {
            $body = call_user_func($body);
        }

        return $body;
    }
    
    /**
     * @param array $data
     * @param integer $status
     * @param array $header
     * @param integer $option
     * @param boolean $echo
     * @return Response|void
     */
    public function json(array $data = [], $echo = false, int $status = 200, array $header = [], int $option = 0)
    {
        $this->status($status);
        $this->header($header);

        $data = json_encode($data, $option);
        if ($this->body($data)) {
            $this->header('Content-Type', 'application/json; charset=UTF-8');
        }
        
        return $echo ? $this->send(true) : $this;
    }

    /**
     * @param  string $filepath
     * @param  string $filename
     * @param  string $disposition
     * @return $this
     */
    public function download(string $filepath, string $filename = null, string $disposition = 'attachment')
    {
        $content = null;
        if (file_exists($filepath)) {
            try {
                $content = file_get_contents($filepath);
            } catch (Exception $exception) {
                throw new Exception(text("File.unreadable", [$filepath]));
            }
        }else{
            throw new Exception(text("File.fileNotFound", [$filepath]));
        }

        if (! $filename) {
            $filename = end(explode('/', $filepath));
        }

        $this->body($content);
        $this->status(200);
        $this->header([
            'Cache-Control'       => 'must-revalidate',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => sprintf('%s; filename="%s"', $disposition, $filename),
            'Content-Length'      => filesize($filepath),
            'Content-Type'        => mime_content_type($filepath),
            'Expires'             => 0,
            'Pragma'              => 'public',
        ]);
        
        return $this;
    }

    /**
     * @param  string $data
     * @param  int    $status
     * @param  array  $header
     * @return void
     */
    public function redirect(string $location = '/', int $status = 302, array $header = [])
    {
        if(!empty($header))
        {
            foreach($header as $key => $value)
            {
                header("$key: $value", true);
            }
        }
        header("Location: $location", true, $status);
    }

    /**
     * @return void
     */
    public function back()
    {
        return $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    /**
     * @param  string $name
     * @param  array  $parameter
     * @return void
     */
    public function to(string $url)
    {
        return $this->redirect($url);
    }

    /**
     * @param  int    $status
     * @param  string $reason
     * @return $this
     */
    public function status(int $status, string $reason = null)
    {
        $this->status = $status;
        $this->reason = $reason ?? $this->reason($status);

        return $this;
    }

    /**
     * @param  int $status
     * @return string
     */
    public function reason(int $status) : string
    {
        if ($status < 100 || $status > 599) {
            $status = 500;
        }

        return $this->message[$status] ?? 'Unknown';
    }

    /**
     * @param  array $header
     * @return $this
     */
    public function header(...$header)
    {
        if (isset($header[0]) && is_array($header[0])) {
            foreach ($header[0] as $key => $value) {
                $this->header($key, $value);
            }

            return $this;
        }

        $this->header[$header[0]] = $header[1];

        return $this;
    }

    /**
     * @return array
     */
    public function headers()
    {
        return $this->header;
    }

    /**
     * @return void
     */
    public function send($echo = false)
    {
        if (! headers_sent()) {
            $status = $this->status;
            $server = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';

            foreach ($this->headers() as $name => $values) {
                if(is_array($values))
                {
                    foreach ($values as $value) {
                        header($name . ':' . $value, strcasecmp($name, 'Content-Type') === 0, $status);
                    }
                }else{
                    header($name . ':' . $values, strcasecmp($name, 'Content-Type') === 0, $status);
                }
            }

            header(sprintf('%s %s %s', $server, $status, $this->reason), true, $status);
        }

        if($echo)
        {
            echo $this->body;
        }else{
            return $this->body;
        }
    }

    /**
     * @param string $status
     * @param string|int $message
     * @return $this
     */
    public function die($status = '404', $title = "404: Not Found !", $message = "")
    {
        $status = is_int($status) ? strval($status) : $status;
        $short = substr($status, 0, 1);
        $message = empty($message) ? $this->message[(int)$status] : $message;

        $config = new Config();
        if(isset($config->page_error[$status."s"]))
        {
            $content = file_exists($config->page_error[$status."s"]) ? file_get_contents($config->page_error[$status."s"]) : $message;
            $content = strtr($content, ["{status}" => $status, "{title}" => $title, "{message}" => $message, "{link}" =>  '/']);
            return $this->{"call{$status}"}($content, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        if(isset($config->page_error[$short."s"]))
        {
            $content = file_exists($config->page_error[$short."s"]) ? file_get_contents($config->page_error[$short."s"]) : $message;
            $content = strtr($content, ["{status}" => $status, "{title}" => $title, "{message}" => $message, "{link}" =>  '/']);
            return $this->{"call{$status}"}($content, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
    }

}