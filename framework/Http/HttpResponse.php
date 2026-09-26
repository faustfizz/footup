<?php

namespace Footup\Http;

class HttpResponse
{
  // Body is raw
  protected string $body;
  protected int $status;
  protected array $headers;

  public function __construct(string $body, int $status, int $headerSize = 0)
  {
    $this->parseHeaders(substr($body, 0, $headerSize));

    $this->body = substr($body, $headerSize);
    $this->status = $status;
  }

  /**
   * Parse response headers
   * @param string $headers
   * @return self
   */
  protected function parseHeaders(string $headers)
  {
    $parsedHeaders = array();
    $arrRequests = explode("\r\n\r\n", $headers);
    for ($index = 0; $index < count($arrRequests) - 1; $index++) {

      foreach (explode("\r\n", $arrRequests[$index]) as $i => $line) {
        if ($i === 0)
          $parsedHeaders[$index]['http_code'] = $line;
        else {
          list($key, $value) = explode(': ', $line);
          $parsedHeaders[$index][$key] = $value;
        }
      }
    }

    $this->headers = isset($headers[0]) ? $headers[0] : [];

    return $this;
  }

  /**
   * Decode JSON response
   */
  public function json(): array
  {
    return json_decode($this->body, true) ?? [];
  }

  /**
   * Get response status code
   */
  public function status(): int
  {
    return $this->status;
  }

  /**
   * Get raw response body
   */
  public function body(): string
  {
    return $this->body;
  }

  /**
   * Check if successful (2xx)
   */
  public function successful(): bool
  {
    return $this->status >= 200 && $this->status < 300;
  }

  /**
   * Check if not found (404)
   */
  public function notFound(): bool
  {
    return $this->status === 404;
  }

  /**
   * Check if unauthorized (401)
   */
  public function unauthorized(): bool
  {
    return $this->status === 401;
  }

  /**
   * Check if server error (5xx)
   */
  public function serverError(): bool
  {
    return $this->status >= 500;
  }
}
