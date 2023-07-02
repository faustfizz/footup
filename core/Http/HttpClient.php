<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Http
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Http;

class HttpClient
{
    /**
     * GET constant value.
     */
    const TYPE_GET = 'GET';

    /**
     * POST constant value.
     */
    const TYPE_POST = 'POST';

    /**
     * Hold the requested.
     *
     * @var string
     */
    private $url;

    /**
     * Hold response raw body.
     *
     * @var mixed
     */
    private $body;

    /**
     * Hold response code
     *
     * @var int
     */
    private $status;

    /**
     * Hold response raw headers.
     *
     * @var string
     */
    private $headers;

    /**
     * Hold response parsed headers.
     *
     * @var array
     */
    private $parsedHeaders;

    /**
     * Common curl request method for GET and POST request
     *
     * @param string    $type          GET or POST.
     * @param string    $url           Request URL.
     * @param array     $data          Request data.
     * @param array     $headers       Headers data.
     * @param array     $curlOptions   curl options.
     * @return self
     */
    private function curlRequest($type, $url, $data = [], $headers = [], $curlOptions = [])
    {
        $curl = curl_init();
        $type = strtoupper($type);

        if (self::TYPE_GET === $type && count($data)) {
            $url = $url . '?' . http_build_query($data);
        }

        $this->url = $url;

        $default = array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_CUSTOMREQUEST   => $type,
            CURLOPT_HTTPHEADER      => $headers,
        );

        $finalCurlOptions = array_replace($default, $curlOptions);

        // Must required for get headers and body
        $finalCurlOptions[CURLOPT_HEADER] = true;

        if (self::TYPE_POST === $type && count($data)) {
            $finalCurlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $finalCurlOptions);

        $response = curl_exec($curl);
        
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $this->headers = substr($response, 0, $header_size);
        $this->setBody(substr($response, $header_size));
        
        curl_close($curl);
        
        return $this->parseHeaders();
    }

    /**
     * GET request
     *
     * @param   string    $url
     * @param   array     $data
     * @param   array     $headers
     * @param   array     $curlOptions
     * @return  HttpClient
     */
    public static function get($url, $data = [], $headers = [], $curlOptions = [])
    {
        $obj = new self;
        return $obj->curlRequest(self::TYPE_GET, $url, $data, $headers, $curlOptions);
    }

    /**
     * POST request
     *
     * @param   string  $url
     * @param   array   $data
     * @param   array   $headers
     * @param   array   $curlOptions
     * @return  HttpClient
     */
    public static function post($url, $data = [], $headers = [], $curlOptions = [])
    {
        $obj = new self;
        return $obj->curlRequest(self::TYPE_POST, $url, $data, $headers, $curlOptions);
    }

    /**
     * Get response status code
     *
     * @return int
     */
    public function getstatus()
    {
        return $this->status;
    }

    /**
     * Parse response headers
     *
     * @return self
     */
    protected function parseHeaders()
    {
        $headers = array();
        $arrRequests = explode("\r\n\r\n", $this->headers);
        for ($index = 0; $index < count($arrRequests) - 1; $index++) {

            foreach (explode("\r\n", $arrRequests[$index]) as $i => $line) {
                if ($i === 0)
                    $headers[$index]['http_code'] = $line;
                else {
                    list($key, $value) = explode(': ', $line);
                    $headers[$index][$key] = $value;
                }
            }
        }

        $this->parsedHeaders = isset($headers[0]) ? $headers[0] : [];

        return $this;
    }

    /**
     * Get response headers
     *
     * @param boolean $parsed
     * @return array
     */
    public function getHeaders($parsed = true)
    {
        return $parsed ? $this->parsedHeaders : $this->headers;
    }

    /**
     * Get response header's specific key's value
     *
     * @param string $key
     * @return mixed
     */
    public function getHeader($key)
    {
        if (!empty($this->parsedHeaders)) {
            return $this->parsedHeaders[$key] ?? null;
        }
        return null;
    }

    /**
     * Get response raw body
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get response body as JSON array.
     *
     * @return array|mixed
     */
    public function getJson()
    {
        return json_decode($this->body, true);
    }

    /**
     * Get response as standard PHP object
     *
     * @return object
     */
    public function getObject()
    {
        return json_decode($this->body);
    }

    /**
     * Set hold response raw body.
     *
     * @param  mixed  $body  Hold response raw body.
     *
     * @return  self
     */ 
    public function setBody($body)
    {
        $parseUrlHost = parse_url($this->url, PHP_URL_HOST);
        $parseUrlScheme = parse_url($this->url, PHP_URL_SCHEME);

        // Parse the body for style and scripts 
        $body = preg_replace(['/<(link.[^>]*href=")(\/?[^\/\/])(?!http)([^"]*)(".[^>]*)>/', '/<(link.[^>]*href=\')(\/?[^\/\/])(?!http)([^\']*)(\'.[^>]*)>/'], '<$1'.$parseUrlScheme.'://'.$parseUrlHost.'$2$3$4 >', $body);
        $body = preg_replace(['/<((script|img|iframe|video|source|audio).[^>]*src=")(\/?[^\/\/])(?!http)([^"]*)(".[^>]*)>/', '/<((script|img|iframe|video|source|audio).[^>]*src=\')(\/?[^\/\/])(?!http)([^\']*)(\'.[^>]*)>/'], '<$1'.$parseUrlScheme.'://'.$parseUrlHost.'$3$4$5 >', $body);

        $this->body = $body;

        return $this;
    }
}