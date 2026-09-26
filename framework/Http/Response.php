<?php

declare(strict_types=1);

namespace Footup\Http;

use Footup\Utils\Arrays\Arrayable;
use Footup\Utils\Macro;

/*
|--------------------------------------------------------------------------
| HTTP response — status, headers, body, and send() to emit it.
|--------------------------------------------------------------------------
*/

class Response
{
  use Macro;

  public function __construct(
    public mixed $content = '',
    public int $status = 200,
    public array $headers = [],
  ) {}

  public function body()
  {
    return $this->content;
  }

  public function withBody(string $content)
  {
    $this->content = $content;
    return $this;
  }

  public static function make(mixed $content = '', int $status = 200, array $headers = []): self
  {
    if (is_array($content) || $content instanceof Arrayable) {
      return static::json($content, $status, $headers);
    }
    return new self($content, $status, $headers);
  }

  public static function json(mixed $data, int $status = 200, array $headers = []): self
  {
    $headers['Content-Type'] = 'application/json';

    return new self(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $status, $headers);
  }

  public function withHeader(string $key, string $value): self
  {
    $this->headers[$key] = $value;

    return $this;
  }

  public function withStatus(int $status): self
  {
    $this->status = $status;

    return $this;
  }

  public function send(): void
  {
    if (! headers_sent()) {
      http_response_code($this->status);

      foreach ($this->headers as $name => $value) {
        header("{$name}: {$value}");
      }
    }

    echo is_string($this->content) ? $this->content : (string) $this->content;
  }
}
