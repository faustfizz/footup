<?php

declare(strict_types=1);

namespace Footup\Http;

use Footup\Exceptions\ValidationException;
use Footup\Validation\Validator;
use Footup\Utils\Macro;

/*
|--------------------------------------------------------------------------
| Request — HTTP request with validation
|
| Extend this class for form requests with centralized validation
|
| Usage:
|   class StorePostRequest extends Request
|   {
|       public function authorize(): bool { return true; }
|       public function rules(): array { return [...]; }
|   }
|
|   public function store(StorePostRequest $request)
|   {
|       Post::create($request->validated());
|   }
|--------------------------------------------------------------------------
*/

class Request
{
  use Macro;

  protected array $input = [];
  private array $validatedData = [];
  public array $attributes = [];

  public function __construct(
    public string $method,
    public string $uri,
    public array $query = [],
    public array $body = [],
    public array $headers = [],
    public array $server = [],
    public array $cookies = [],
    public array $files = [],
  ) {
    $this->input = array_merge($this->query, $this->body);
  }

  public static function capture(): self
  {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    $headers = [];
    foreach ($_SERVER as $key => $value) {
      if (str_starts_with($key, 'HTTP_')) {
        $name = str_replace('_', '-', substr($key, 5));
        $headers[$name] = $value;
      }
    }

    $rawBody = file_get_contents('php://input') ?: '';
    $body = $_POST;

    if (str_contains($headers['CONTENT-TYPE'] ?? '', 'application/json') && $rawBody !== '') {
      $decoded = json_decode($rawBody, true);
      if (is_array($decoded)) {
        $body = $decoded;
      }
    }

    return new self(
      method: $method,
      uri: $uri,
      query: $_GET,
      body: $body,
      headers: $headers,
      server: $_SERVER,
      cookies: $_COOKIE,
      files: $_FILES,
    );
  }

  public function input(?string $key = null, mixed $default = null): mixed
  {
    $all = array_merge($this->query, $this->body);

    if ($key === null) {
      return $all;
    }

    return $all[$key] ?? $default;
  }

  public function header(string $key, mixed $default = null): mixed
  {
    return $this->headers[strtoupper($key)] ?? $this->headers[$key] ?? $default;
  }

  public function isJson(): bool
  {
    return str_contains($this->header('CONTENT-TYPE', ''), 'application/json');
  }

  public function setAttribute(string $key, mixed $value): void
  {
    $this->attributes[$key] = $value;
  }

  public function attribute(string $key, mixed $default = null): mixed
  {
    return $this->attributes[$key] ?? $default;
  }

  /**
   * Get the current request URL (scheme + host + path)
   * e.g. http://localhost:8000/api/users
   */
  public function url(): string
  {
    $scheme = $this->isSecure() ? 'https' : 'http';
    $host = $this->header('HOST', 'localhost');

    return "{$scheme}://{$host}{$this->uri}";
  }

  /**
   * Get the full URL with query string
   * e.g. http://localhost:8000/api/users?page=1&limit=10
   */
  public function fullUrl(): string
  {
    $url = $this->url();
    $query = http_build_query($this->query);

    return $query ? "{$url}?{$query}" : $url;
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
   * Get just the request path (no domain)
   * e.g. /api/users
   */
  public function path(): string
  {
    return $this->uri;
  }

  /**
   * Check if the request accepts JSON
   * (looks at Accept header)
   */
  public function acceptsJson(): bool
  {
    $accept = $this->header('Accept', '');

    return str_contains($accept, 'application/json') || $this->isJson();
  }

  /**
   * Alias for acceptsJson()
   */
  public function expectJson(): bool
  {
    return $this->acceptsJson();
  }

  /**
   * Check if this is a secure (HTTPS) request
   */
  public function isSecure(): bool
  {
    return ($this->server['HTTPS'] ?? 'off') !== 'off' || ($this->header('X-Forwarded-Proto') === 'https');
  }

  /**
   * Get the request's IP address
   */
  public function ip(): string
  {
    return $this->header('Client-Ip') ?? $this->header('X-Forwarded-For') ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
  }

  /**
   * Check if request method matches
   */
  public function isMethod(string $method): bool
  {
    return strtoupper($this->method) === strtoupper($method);
  }

  /**
   * @return bool
   */
  public function ajax(): bool
  {
    return mb_strtoupper($this->header('X-REQUESTED-WITH')) === 'XMLHTTPREQUEST';
  }
  /**
   * Check if request method matches
   */
  /**
   * Get an uploaded file by name
   * Returns UploadedFile or null
   */
  public function file(string $name): ?UploadedFile
  {
    if (!isset($_FILES[$name])) {
      return null;
    }

    return new UploadedFile($_FILES[$name]);
  }

  /**
   * Get all uploaded files
   * @return array<string, UploadedFile>
   */
  public function files(): array
  {
    $files = [];

    foreach ($_FILES as $name => $file) {
      $files[$name] = new UploadedFile($file);
    }

    return $files;
  }

  /**
   * Check if a file was uploaded
   */
  public function hasFile(string $name): bool
  {
    return isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK;
  }

  /**
   * Determine if the user is authorized to make this request
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get validation rules
   */
  public function rules(): array
  {
    return [];
  }

  /**
   * Get custom error messages
   */
  public function messages(): array
  {
    return [];
  }

  /**
   * Validate with custom rules and messages
   * 
   * Usage:
   *   $validated = $request->validate(['email' => 'required|email']);
   *   $validated = $request->validate(['email' => 'required|email'], ['email' => 'Invalid email']);
   *   $validated = $request->validate(
   *       ['email' => 'required|email', 'password' => 'required|min:8'],
   *       ['email' => 'Invalid email', 'password' => 'Password must be at least 8 characters']
   *   );
   */
  public function validate(array $rules): array
  {
    $validator = new Validator($this->input);

    if (!$validator->validate($rules)) {
      throw new ValidationException($validator->errors());
    }

    return $validator->validated();
  }

  /**
   * Validate the request and return validated data (using class rules/messages)
   */
  public function validated(): array
  {
    if (!empty($this->validatedData)) {
      return $this->validatedData;
    }

    if (!$this->authorize()) {
      throw new \Exception('This action is unauthorized');
    }

    $validator = new Validator($this->input);

    if (!$validator->validate($this->rules())) {
      throw new ValidationException($validator->errors());
    }

    return $this->validatedData = $validator->validated();
  }

  /**
   * Get all input
   */
  public function all(): array
  {
    return array_merge($this->query, $this->body);
  }

  /**
   * Check if input exists
   */
  public function has(string $key): bool
  {
    return isset($this->input[$key]);
  }

  /**
   * Get only specified keys
   */
  public function only(...$keys): array
  {
    $result = [];
    foreach ($keys as $key) {
      if (isset($this->input[$key])) {
        $result[$key] = $this->input[$key];
      }
    }
    return $result;
  }

  /**
   * Get all except specified keys
   */
  public function except(...$keys): array
  {
    return array_diff_key(
      array_merge($this->query, $this->body),
      array_flip($keys)
    );
  }

  /**
   * Get current user (if authenticated)
   */
  public function user()
  {
    return auth()->user() ?? null;
  }

  /**
   * Check if user is authenticated
   */
  public function isAuthenticated(): bool
  {
    return auth()->check();
  }

  /**
   * Get HTTP method
   */
  public function method(): string
  {
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
  }

  /**
   * Check if request is POST
   */
  public function isPost(): bool
  {
    return $this->method() === 'POST';
  }

  /**
   * Check if request is GET
   */
  public function isGet(): bool
  {
    return $this->method() === 'GET';
  }

  /**
   * Check if request is PUT
   */
  public function isPut(): bool
  {
    return $this->method() === 'PUT';
  }

  /**
   * Check if request is DELETE
   */
  public function isDelete(): bool
  {
    return $this->method() === 'DELETE';
  }

  /**
   * Check if request is AJAX
   */
  public function isAjax(): bool
  {
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
  }

  /**
   * Get bearer token from Authorization header
   */
  public function bearerToken(): ?string
  {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/', $header, $matches)) {
      return $matches[1];
    }
    return null;
  }

  /**
   * Get all headers
   */
  public function headers(): array
  {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
      if (str_starts_with($key, 'HTTP_')) {
        $headers[str_replace('HTTP_', '', $key)] = $value;
      }
    }
    return $headers;
  }

  /**
   * Get content type
   */
  public function contentType(): ?string
  {
    return $this->header('content-type');
  }

  /**
   * Get user agent
   */
  public function userAgent(): string
  {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
  }

  /**
   * Get device information
   * 
   * Usage:
   *   $device = $request->device();
   *   $device->type();           // 'mobile', 'tablet', 'desktop'
   *   $device->browser();        // 'Chrome', 'Firefox', etc
   *   $device->os();             // 'Windows', 'iOS', 'Android', etc
   *   $device->isMobile();
   *   $device->isTablet();
   *   $device->isDesktop();
   *   $device->toArray();
   */
  public function device(): \Footup\Http\DeviceDetector
  {
    return \Footup\Http\DeviceDetector::detect($this->userAgent());
  }

  /**
   * Get geographic information from IP
   * 
   * Usage:
   *   $geo = $request->geo();
   *   $geo->ip();                // IP address
   *   $geo->country();           // 'United States'
   *   $geo->countryCode();       // 'US'
   *   $geo->city();              // 'San Francisco'
   *   $geo->region();            // 'California'
   *   $geo->location();          // Full location string
   *   $geo->isLocal();           // true if localhost
   *   $geo->toArray();
   */
  public function geo(): \Footup\Http\GeoIP
  {
    return \Footup\Http\GeoIP::detect($this->ip());
  }

  /**
   * Get all request metadata
   * Perfect for logging in sessions/connections records
   * 
   * Usage:
   *   $metadata = $request->metadata();
   *   // Returns: [
   *   //   'ip' => '203.0.113.42',
   *   //   'device' => [...],
   *   //   'geo' => [...],
   *   //   'user_agent' => '...',
   *   //   'timestamp' => 1234567890,
   *   // ]
   */
  public function metadata(): array
  {
    return [
      'ip' => $this->ip(),
      'user_agent' => $this->userAgent(),
      'device' => $this->device()->toArray(),
      'geo' => $this->geo()->toArray(),
      'timestamp' => time(),
    ];
  }
}
