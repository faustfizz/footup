<?php

declare(strict_types=1);

namespace Footup\Http;

class HttpClient
{
  protected static array $defaultHeaders = [];
  protected array $headers = [];
  protected ?int $timeout = 30;

  public static function get(string $url, array $query = []): HttpResponse
  {
    return (new static())->request('GET', $url, query: $query);
  }

  public static function post(string $url, array $data = []): HttpResponse
  {
    return (new static())->request('POST', $url, $data);
  }

  public static function put(string $url, array $data = []): HttpResponse
  {
    return (new static())->request('PUT', $url, $data);
  }

  public static function patch(string $url, array $data = []): HttpResponse
  {
    return (new static())->request('PATCH', $url, $data);
  }

  public static function delete(string $url): HttpResponse
  {
    return (new static())->request('DELETE', $url);
  }

  public static function withHeaders(array $headers): self
  {
    $instance = new static();
    $instance->headers = array_merge(self::$defaultHeaders, $headers);

    return $instance;
  }

  public static function withoutVerifying(): self
  {
    $instance = new static();
    $instance->verifySsl = false;

    return $instance;
  }

  public static function timeout(int $seconds): self
  {
    $instance = new static();
    $instance->timeout = $seconds;

    return $instance;
  }

  protected bool $verifySsl = true;

  protected function request(string $method, string $url, array $body = [], array $query = []): HttpResponse
  {
    if ($query) {
      $url .= '?' . http_build_query($query);
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
    ]);

    // Set headers
    $headers = array_merge(['Content-Type: application/json'], $this->headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Set body for POST/PUT/PATCH
    if (!empty($body)) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE) ?? 0;
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
      throw new \RuntimeException("HTTP request failed: {$error}");
    }

    return new HttpResponse((string) $response, $status, $headerSize);
  }
}
