<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Http
 * @version 0.4
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Http;

use Footup\Config\Config;
use ArrayObject;
use DateTime;
use Exception;
use Footup\Utils\Shared;
use JsonSerializable;

class Response implements JsonSerializable
{
    /**
     * header Content-Length.
     */
    public bool $content_length = false;

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
     * @var bool HTTP response sent
     */
    protected bool $sent = false;

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
    public function __construct($data = 'php://memory', $status = 200, $header = [])
    {
        $this->header($header);

        if (! isset($this->header['Content-Type'])) {
            $this->header('Content-Type', 'text/html; charset=UTF-8');
        }

        if (! isset($this->header['Cache-Control'])) {
            $this->header('Cache-Control', 'no-store, max-age=0, no-cache');
        }

        if (! isset($this->header['Date'])) {
            $this->header('Date', (new DateTime('now', new \DateTimeZone(date_default_timezone_get())))->format('D, d M Y H:i:s') . ' GMT');
        }

        if(empty($data))
        {
            $data = $this->message[$status];
        }

        $this->status($status)->body($data);
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
            return $this->status($status)->body($arguments[0] ?? $this->message[$status])->send(true);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->send();
    }

    public function jsonSerialize()
    {
        return empty($this->body) ? ['message' => $this->reason] : (array)$this->body;
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
        if (!is_null($body) && !is_string($body) && !is_numeric($body) && !is_callable([$body, '__toString'])) {
            throw new Exception(text('Http.invalidBodyType', [gettype($body)]));
        }

        if (is_object($body) && is_callable([$body, '__toString'])) {
            $body = call_user_func($body);
        }

        return $body ?? $this->body;
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
        $this->status($status)->header(array_merge($header, ['Content-Type' => 'application/json; charset=UTF-8']));

        $data = json_encode(
            empty($data) ? $this : $data,
            $option
        );

        $this->body($data);
        
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
                throw new Exception(text('File.unreadable', [$filepath]));
            }
        }else{
            throw new Exception(text('File.fileNotFound', [$filepath]));
        }

        if (! $filename) {
            $explode = explode('/', $filepath);
            $filename = end($explode);
        }

        $this->body($content);
        $this->status(200);
        $this->header([
            'Cache-Control'       => 'must-revalidate',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => sprintf("%s; filename='%s'", $disposition, $filename),
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
                header('$key: $value', true);
            }
        }
        header('Location: $location', true, $status);
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
        if (\array_key_exists($status, $this->message)) {
            $this->status = $status;
            $this->reason = $reason ?? $this->reason($status);
        }

        return $this;
    }

    /**
     * @param  int $status
     * @return string
     */
    public function reason(int $status) : string
    {
        return $this->message[$status] ?? 'Unknown';
    }

    /**
     * @param  array $header
     * @return $this
     */
    public function header(string|array $headerKey, $value = null)
    {
        if(empty($headerKey)) return $this;

        if (!empty($headerKey) && is_array($headerKey)) {
            foreach ($headerKey as $key => $val) {
                $this->header($key, $val);
            }

            return $this;
        }

        $this->header[$headerKey] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function headers($key = null)
    {
        return !is_null($key) && isset($this->header[$key]) ? $this->header[$key] : $this->header;
    }

    /**
     * @return mixed
     */
    public function send($echo = false)
    {
        if (!headers_sent()) {
            $this->sendHeaders();
        }

        if($echo)
        {
            echo $this->body;
            $this->sent = true;
        }else{
            return $this->body;
        }
    }

    /**
     * @param string $status
     * @param string|int $message
     * @return void
     */
    public function die($status = '404', $title = '404: Not Found !', $message = '')
    {
        $status = is_int($status) ? strval($status) : $status;
        $short = substr($status, 0, 1);
        $message = empty($message) ? $this->message[(int)$status] : $message;

        /**
         * @var Config
         */
        $config = Shared::loadConfig();
        
        $content = isset($config->page_error[$status.'s']) && file_exists($config->page_error[$status.'s']) ? file_get_contents($config->page_error[$status.'s']) : (isset($config->page_error[$short.'s']) && file_exists($config->page_error[$short.'s']) ? file_get_contents($config->page_error[$short.'s']) : $message);

        $content = strtr($content, ['{status}' => $status, '{title}' => $title, '{message}' => $message, '{link}' =>  '/']);
        $this->{"call{$status}"}($content, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Clears the response.
     *
     * @return Response Self reference
     */
    public function clear(): self
    {
        $this->status = 200;
        $this->header = [];
        $this->body = '';

        return $this;
    }

    /**
     * Sets caching headers for the response.
     *
     * @param int|string $expires Expiration time
     *
     * @return Response Self reference
     */
    public function cache($expires = false): self
    {
        if (false === $expires) {
            $this->header['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
            $this->header['Cache-Control'] = [
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
                'max-age=0',
            ];
            $this->header['Pragma'] = 'no-cache';
        } else {
            $expires = \is_int($expires) ? $expires : strtotime($expires);
            $this->header['Expires'] = gmdate('D, d M Y H:i:s', $expires) . ' GMT';
            $this->header['Cache-Control'] = 'max-age=' . ($expires - time());
            if (isset($this->header['Pragma']) && 'no-cache' == $this->header['Pragma']) {
                unset($this->header['Pragma']);
            }
        }

        return $this;
    }

    /**
     * Sends HTTP headers.
     *
     * @return Response Self reference
     */
    public function sendHeaders(): self
    {
        // Send status code header
        if (false !== strpos(\PHP_SAPI, 'cgi')) {
            header(
                sprintf(
                    'Status: %d %s',
                    $this->status,
                    $this->message[$this->status]
                ),
                true
            );
        } else {
            header(
                sprintf(
                    '%s %d %s',
                    $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1',
                    $this->status,
                    $this->message[$this->status]),
                true,
                $this->status
            );
        }

        // Send other headers
        http_response_code($this->status);

        foreach ($this->headers() as $name => $values) {
            if(\is_array($values))
            {
                foreach ($values as $value) {
                    header($name . ':' . $value, (strcasecmp($name, 'Content-Type') === 0 || strcasecmp($name, 'Date')), $this->status);
                }
            }else{
                header($name . ':' . $values, (strcasecmp($name, 'Content-Type') === 0 || strcasecmp($name, 'Date')), $this->status);
            }
        }

        if ($this->content_length) {
            // Send content length
            $length = $this->getContentLength();

            if ($length > 0) {
                header('Content-Length: ' . $length);
            }
        }

        return $this;
    }

    /**
     * Gets the content length.
     *
     * @return int Content length
     */
    public function getContentLength(): int
    {
        return \extension_loaded('mbstring') ?
            mb_strlen($this->body, 'latin1') :
            \strlen($this->body);
    }

    /**
     * Gets whether response was sent.
     */
    public function sent(): bool
    {
        return $this->sent;
    }

}