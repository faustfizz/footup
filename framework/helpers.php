<?php

declare(strict_types=1);

use Footup\Engine\Application;
use Footup\Files\Mime;
use Footup\Html\Html;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Routing\Router;

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
| Small, optional sugar over the container/config/view/error primitives
| in core/*.php (one class per file — see Application.php, Container.php,
| etc). Nothing in the core depends on these — use them
| in your controllers and routes because they're convenient, not because
| anything requires it. Loaded via composer.json's "files" autoload (or
| a plain require in public/index.php if Composer isn't installed).
*/

if (! function_exists('app')) {
    /**
     * Resolve the Application (no args) or anything bound in its
     * container (with $abstract) — e.g. app(Footup\View::class).
     * 
     * @template T
     *
     * @param class-string<T>|null $abstract
     * @return ($abstract is null ? \Footup\Engine\Application : T)
     * 
     */
    function app(?string $abstract = null): mixed
    {
        $app = \Footup\Engine\Application::current();

        return $abstract === null ? $app : $app->make($abstract);
    }
}

if (! function_exists('env')) {
    /**
     * Read a value from .env / the real environment, e.g. env('APP_ENV', 'production').
     */
    function env(string $key, mixed $default = null): mixed
    {
        return \Footup\Engine\Env::get($key, $default);
    }
}

if (! function_exists('config')) {
    /**
     * Read from the merged config/*.php files with dot notation, e.g.
     * config('app.name'), or config() for the whole array.
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = app('config');

        if ($key === null) {
            return $config;
        }

        $value = $config;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (! function_exists('response')) {
    function response(mixed $content = '', int $status = 200, array $headers = []): \Footup\Http\Response
    {
        return \Footup\Http\Response::make($content, $status, $headers);
    }
}

if (! function_exists('json')) {
    function json(mixed $data, int $status = 200): \Footup\Http\Response
    {
        return \Footup\Http\Response::json($data, $status);
    }
}

if (! function_exists('view')) {
    function view(string $template, array $data = [], int $status = 200): \Footup\Http\Response
    {
        $html = app(\Footup\View\View::class)->render($template, $data);

        return \Footup\Http\Response::make($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}

if (! function_exists('abort')) {
    /**
     * Stop the request with an HTTP error, e.g. abort(404, 'Not found').
     * Caught by Application::handle() and rendered by the bound
     * ExceptionHandlerInterface just like any other HttpException.
     */
    function abort(int $status, string $message = ''): never
    {
        throw new \Footup\Exceptions\HttpException($status, $message ?: 'Error');
    }
}

if (! function_exists('abort_if')) {
    function abort_if($condition, int $status, string $message = '')
    {
      if ($condition) {
        throw new \Footup\Exceptions\HttpException($status, $message ?: 'Error');
      }
      return;
    }
}

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app()->basePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return app()->getAppPath() . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return app()->getPublicPath() . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('asset_path')) {
    function asset_path(string $path = ''): string
    {
        return app()->getAssetPath() . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return app()->getConfigPath() . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
      return app()->getStoragePath() . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('ui_path')) {
    function ui_path(string $path = ''): string
    {
      return app()->getUiPath() . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('benchmark')) {
    /**
     * Time a block of code and get its own return value back — the
     * elapsed time is recorded under $label and shows up in the
     * Server-Timing header (see TimingMiddleware) and in
     * Footup\Monitoring\Benchmark::summary(). E.g.:
     *   $report = benchmark('report.generate', fn () => $this->buildReport());
     */
    function benchmark(string $label, \Closure $callback): mixed
    {
        return \Footup\Monitoring\Benchmark::measure($label, $callback);
    }
}

if (! function_exists('logger')) {
    /**
     * logger('User logged in', ['id' => 42]).
     */
    function logger(string $message, array $context = []): void
    {
        \Footup\Monitoring\Log::info($message, $context);
    }
}

if (! function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \Footup\Http\Csrf::token();
    }
}
 
if (! function_exists('validate')) {
    function validate(array $data, array $rules): \Footup\Validation\Validator
    {
        return new \Footup\Validation\Validator($data, $rules);
    }
}

if (! function_exists('cache')) {
    function cache(string $key, ?callable $callback = null, int $seconds = 3600): mixed
    {
        if ($callback === null) {
            return \Footup\Caching\Cache::get($key);
        }

        return \Footup\Caching\Cache::remember($key, $seconds, $callback);
    }
}

if (! function_exists('uploaded_file')) {
    function uploaded_file(string $name): ?\Footup\Http\UploadedFile
    {
        if (! isset($_FILES[$name])) {
            return null;
        }

        return new \Footup\Http\UploadedFile($_FILES[$name]);
    }
}

if (! function_exists('trans')) {
    function trans(string $key, array $replace = [], $locale = null): string
    {
        return \Footup\Utils\i18n::trans($key, $replace, $locale);
    }
}

if (! function_exists('__')) {
    function __(string $key, array $replace = [], $locale = null): string
    {
        return trans($key, $replace, $locale);
    }
}

if (! function_exists('auth')) {
    function auth()
    {
        return new \Footup\Auth\Auth();
    }
}

if (! function_exists('auth_user')) {
    function auth_user()
    {
        return \Footup\Auth\Auth::user();
    }
}

if (! function_exists('queue')) {
    function queue(string $jobClass, array $data = []): void
    {
        \Footup\Queuing\Queue::push($jobClass, $data);
    }
}

if (!function_exists('text')) {
	/**
	 * Translation
	 * get language entry
	 *
	 * @param string $indice
	 * @param array $params
	 * @param string|null $locale
	 * @return string
	 */
	function text(string $indice, array $params = [], string $locale = null): string
	{
		return \Footup\Utils\i18n::trans($indice, $params, $locale);
	}
}

if (!function_exists("frameworkName")) {
	/**
	 * Exposition du nom du framework
	 *
	 * @return string
	 */
	function frameworkName()
	{
		return app()->name();
	}
}

if (!function_exists("frameworkVersion")) {
	/**
	 * Exposition de la version
	 *
	 * @return string
	 */
	function frameworkVersion()
	{
		return app()->version();
	}
}

if (!function_exists("request")) {
	/**
	 * Une fonction pour exposer l'objet Request
	 *
   * 
   * @template T
   * 
   * @param string|null $methodOrIndex
   * @param mixed $arg
   * @return ($methodOrIndex is null ? \Footup\Http\Request : T)
	 */
	function request(?string $methodOrIndex = null, mixed $arg = null)
	{
    $request = app(Request::class);
    
    if (!$methodOrIndex) {
        return $request;
    }
    
    if (method_exists($request, $methodOrIndex)) {
        return $arg !== null ? $request->$methodOrIndex($arg) : $request->$methodOrIndex();
    }

    if (method_exists($request, 'input')) {
        return $request->input($methodOrIndex, $arg);
    }

    return null;
	}
}

if (!function_exists("router")) {
	/**
   * @return Router
	 */
	function router()
	{
    return app(Router::class);
	}
}


if (!function_exists("calledController")) {
	/**
	 * Retrouve le controlleur en cours d'utilisation
	 *
	 * @param boolean $withNamespace
	 * @return string
	 */
	function calledController($withNamespace = true)
	{
    $controller = router()->current()->getController();
    
		return $withNamespace ? $controller : basename($controller);
	}
}

if (!function_exists("calledMethod")) {
	/**
	 * Retrouve la méthode couremment utilisée
	 *
	 * @return string
	 */
	function calledMethod()
	{
		return router()->current()->getControllerMethod();
	}
}

if (!function_exists("getMimeType")) {
	/**
	 * getMimeType
	 *
	 * @param string $file
	 * @return string
	 */
	function getMimeType($file)
	{
		return Mime::getMime(pathinfo($file, PATHINFO_EXTENSION));
	}
}

if (!function_exists("favicon")) {
	/**
	 * Link Favicon
	 *
	 * @param string $file
	 * @return string
	 */
	function favicon($file)
	{
		$mime = Mime::getMime(pathinfo($file, PATHINFO_EXTENSION));

		return file_exists(app()->basePath .'/'. $file) ? Html::link(null, [
			"rel" => "icon",
			"href" => asset($file),
			"type" => $mime
		]) : "";
	}
}

if (!function_exists("assets")) {
	/**
	 * Link assets
	 *
	 * @param string $file
	 * @return string
	 */
	function assets($file)
	{
		switch (pathinfo($file, PATHINFO_EXTENSION)) {
			case "css":
				return css($file);
			case "png":
			case "jpg":
			case "jpeg":
			case "gif":
				return img($file);
			case "js":
				return js($file);
			default:
				return "";
		}
	}
}

if (!function_exists("img")) {
	/**
	 * Link image
	 *
	 * @param string $file
	 * @param string $class
	 * @param int|string $width
	 * @param int|string $height
	 * @return string
	 */
	function img($file, $class = "img-fluid", $width = null, $height = null)
	{
    foreach (['img', 'images', 'uploads'] as $key => $folder) {
      # code...
      if (file_exists(public_path("/assets/$folder/" . $file))) {
        return Html::img(null, array_filter([
          "rel" => "image",
          "src" => asset("assets/$folder/" . $file),
          "width" => $width,
          "height" => $height,
          "class" => $class
        ]));
      }
    }

		return "";
	}
}

if (!function_exists("css")) {
	/**
	 * Link stylesheet
	 *
	 * @param string $file
	 * @return string
	 */
	function css($file)
	{
		$file = strtr($file, [".css" => ""]) . ".css";
    return Html::link(null, [
			"rel" => "stylesheet",
			"href" => asset("css/" . $file),
			"type" => "text/css"
		]);
	}
}

if (!function_exists("js")) {
	/**
	 * Link javaScript
	 *
	 * @param string $file
	 * @return string
	 */
	function js($file)
	{
		$file = strtr($file, [".js" => ""]) . ".js";

		return Html::script(null, [
			"src" => asset("js/" . $file),
			"type" => "text/javascript"
		]);
	}
}

 
if (! function_exists('url')) {
    /**
     * Generate a URL relative to the app base, e.g. url('/about') → http://localhost:8000/about
     * or url('about') → /about
     */
    function url(string $path = ''): string
    {
      if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
          return $path;
      }

      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 
          ? 'https://' 
          : 'http://';

      $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

      $baseUrl = $protocol . $host;

      return $baseUrl . '/' . ltrim($path, '/');
    }
}
 
if (! function_exists('route')) {
    /**
     * Generate a URL for a named route
     * e.g. route('users.show', ['id' => 1]) → /users/1
     */
    function route(string $name, array $params = []): string
    {
        $route = app(Router::class)->namedRoute($name);
 
        if (!$route) {
            throw new \RuntimeException("Route [{$name}] not found.");
        }
 
        $uri = $route->uri;
 
        // Replace route parameters with values
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string)$value, $uri);
        }
 
        return url($uri);
    }
}
 
if (! function_exists('current_url')) {
    /**
     * Get the current request URL
     */
    function current_url(): string
    {
        $request = app(Request::class);
 
        return $request->fullUrl();
    }
}
 
if (! function_exists('asset')) {
    /**
     * Generate a URL to a public asset
     * e.g. asset('css/style.css') → /public/css/style.css
     */
    function asset(string $file): string
    {
        return app()->pathToUrl(
          asset_path($file)
        );
    }
}
 
if (! function_exists('storage_url')) {
    /**
     * Generate a URL to a storage file
     * e.g. storage_url('uploads/avatar.jpg') → /storage/uploads/avatar.jpg
     */
    function storage_url(string $publicFilePath): string
    {
        return app()->pathToUrl(
          storage_path($publicFilePath)
        );
    }
}


if (!function_exists('base_url')) {
	/**
	 * Génére des urls 
	 *
	 * @param array|string $uri
	 * @param boolean $withQuery
	 * @param string|null $scheme
	 * @return string
	 */
	function base_url($uri = '', bool $withQuery = false, string $scheme = null): string
	{
		$base_url = url($uri);
		$query = "";

		if ($scheme) {
			$scheme = strtr($scheme, ["://" => ""]);
			$url = parse_url($base_url);
			$base_url = strtr($base_url, [$url["scheme"] => $scheme]);
		}

		if ($withQuery) {
			$q = request()->query;
			$query = !empty($q) ? "?" . http_build_query($q, "_key", "&") : $query;
		}

    return $base_url . $query;
	}
}

if (! function_exists('mail_to')) {
    /**
     * Create a Mail instance to send an email
     * e.g. mail_to('user@example.com')->subject('Hello')->body('...')->send()
     */
    function mail_to(string|array $email): \Footup\Messaging\Mailer
    {
        return \Footup\Facades\Mail::to($email);
    }
}

if (! function_exists('redirect')) {
    /**
     * Create a redirect response
     * e.g. redirect('/dashboard')
     *      redirect()->route('users.show', ['id' => 1])
     *      redirect()->back()->with('message', 'Saved!')
     */
    function redirect(?string $location = null): \Footup\Http\RedirectResponse
    {
        return new \Footup\Http\RedirectResponse($location);
    }
}

if (! function_exists('back')) {
    /**
     * Redirect to the previous page
     */
    function back(): \Footup\Http\RedirectResponse
    {
        return redirect()->back();
    }
}

if (! function_exists('flash')) {
    /**
     * Get a flash message from the session
     * Flash messages are deleted after being read once
     *
     * Usage:
     *   $msg = flash('success');      // Get and delete
     *   $errors = flash('errors');    // Get validation errors
     *
     * @param string|null $key
     * @return mixed
     */
    function flash(string $key, $default = null): mixed
    {
        return \Footup\Sessions\Session::getFlash($key, $default);
    }
}

if (! function_exists('hasFlash')) {
    /**
     * Check if a flash message exists
     *
     * Usage:
     *   @if(hasFlash('errors'))
     *       // Display errors
     *   @endif
     *
     * @param string $key
     * @return bool
     */
    function hasFlash(string $key): bool
    {
        return \Footup\Sessions\Session::hasFlash($key);
    }
}

if (! function_exists('setFlash')) {
    /**
     * Set a flash message
     *
     * Usage:
     *   setFlash('success', 'User created!');
     *   setFlash('errors', ['email' => 'Required']);
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    function setFlash(string $key, mixed $value): void
    {
        \Footup\Sessions\Session::flash($key, $value);
    }
}

if (! function_exists('keepFlash')) {
    /**
     * Keep flash messages for another request
     *
     * Usage:
     *   keepFlash(['errors', 'old_input']);
     *   keepFlash('errors');
     *
     * @param string|array $keys
     * @return void
     */
    function keepFlash(string|array $keys): void
    {
        $keys = is_array($keys) ? $keys : [$keys];
        \Footup\Sessions\Session::keepFlash($keys);
    }
}

if (! function_exists('reflash')) {
    /**
     * Keep all flash messages for another request
     *
     * Usage:
     *   reflash();
     *
     * @return void
     */
    function reflash(): void
    {
        \Footup\Sessions\Session::reflash();
    }
}

if (! function_exists('old')) {
    /**
     * Get an old input value (from previous form submission)
     * Usually repopulated via redirect()->back()->withInput($request->all())
     * e.g. old('email') in forms to repopulate
     */
    function old(string $key, mixed $default = null): mixed
    {
        $oldInput = flash('old_input') ?? [];

        return $oldInput[$key] ?? $default;
    }
}

if (! function_exists('download')) {
    /**
     * Download a file
     * e.g. download('storage/invoices/invoice.pdf', 'invoice.pdf')
     */
    function download(string $path, ?string $name = null): \Footup\Http\DownloadResponse
    {
        return \Footup\Http\DownloadResponse::file($path, $name);
    }
}

if (! function_exists('hash_make')) {
    /**
     * Hash a password using bcrypt
     * e.g. hash_make('my-password')
     */
    function hash_make(string $password): string
    {
        return \Footup\Hashing\Hash::make($password);
    }
}

if (! function_exists('hash_check')) {
    /**
     * Verify a password against a hash
     * e.g. hash_check($input, $user->password)
     */
    function hash_check(string $password, string $hash): bool
    {
        return \Footup\Hashing\Hash::check($password, $hash);
    }
}

if (! function_exists('session')) {
    /**
     * Get or set session data
     * e.g. session('user_id') → get
     *      session('user_id', 123) → set
     */
    function session(string $key, mixed $value = null): mixed
    {
        if ($value === null) {
            return \Footup\Sessions\Session::get($key);
        }

        \Footup\Sessions\Session::put($key, $value);

        return $value;
    }
}

if (! function_exists('e')) {
    /**
     * HTML escape a string for safe display in templates
     * Use for ANY user input: $user->name, comments, posts, etc.
     * e.g. <p><?= e($user->bio) ?></p>
     */
    function e(?string $string): string
    {
        return \Footup\Utils\Str::e($string);
    }
}

if (! function_exists('encrypt')) {
    /**
     * Encrypt a string using the app key
     */
    function encrypt(string $value): string
    {
        return \Footup\Hashing\Crypt::encrypt($value);
    }
}

if (! function_exists('decrypt')) {
    /**
     * Decrypt an encrypted string
     */
    function decrypt(string $payload): string
    {
        return \Footup\Hashing\Crypt::decrypt($payload);
    }
}

if (! function_exists('collect')) {
    /**
     * Create a new Collection instance
     * e.g. collect($items)->map(...)->filter(...)->toArray()
     */
    function collect(array $items = []): \Footup\Utils\Collection
    {
        return new \Footup\Utils\Collection($items);
    }
}

if (! function_exists('http')) {
    /**
     * Access Http client
     * e.g. http()->get('https://api.example.com')
     *      http()->post('https://api.example.com', ['data'])
     */
    function http(): \Footup\Http\HttpClient
    {
        return new \Footup\Http\HttpClient();
    }
}

if (! function_exists('file_get')) {
    /**
     * Get file contents
     */
    function file_get(string $path): string
    {
        return \Footup\Files\File::get($path);
    }
}

if (! function_exists('file_put')) {
    /**
     * Write to file (overwrites)
     */
    function file_put(string $path, string $content): void
    {
        \Footup\Files\File::put($path, $content);
    }
}

if (! function_exists('file_append')) {
    /**
     * Append to file
     */
    function file_append(string $path, string $content): void
    {
        \Footup\Files\File::append($path, $content);
    }
}

if (! function_exists('file_delete')) {
    /**
     * Delete a file
     */
    function file_delete(string $path): void
    {
        \Footup\Files\File::delete($path);
    }
}

if (! function_exists('file_exists_check')) {
    /**
     * Check if file exists
     */
    function file_exists_check(string $path): bool
    {
        return \Footup\Files\File::exists($path);
    }
}

if (! function_exists('file_size')) {
    /**
     * Get file size in bytes
     */
    function file_size(string $path): int
    {
        return \Footup\Files\File::size($path);
    }
}

if (! function_exists('file_mime')) {
    /**
     * Get MIME type of file
     */
    function file_mime(string $path): string
    {
        return \Footup\Files\File::mime($path);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token
     * 
     * @return string Token
     */
    function csrf_token(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF token hidden field
     * 
     * @return string HTML hidden input
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Get old form input value
     * 
     * @param string $key Input name
     * @param mixed $default Default value
     * @return mixed Value
     */
    function old(string $key, $default = '')
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     * 
     * @param mixed ...$vars Variables to dump
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable
     * 
     * @param mixed ...$vars Variables to dump
     */
    function dump(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
    }
}
