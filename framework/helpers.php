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
        return \Footup\Env::get($key, $default);
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
        $html = app(\Footup\View::class)->render($template, $data);

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

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app()->basePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return app()->publicPath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
      return app()->storagePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('ui_path')) {
    function ui_path(string $path = ''): string
    {
      return app()->uiPath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('benchmark')) {
    /**
     * Time a block of code and get its own return value back — the
     * elapsed time is recorded under $label and shows up in the
     * Server-Timing header (see TimingMiddleware) and in
     * Footup\Benchmark::summary(). E.g.:
     *   $report = benchmark('report.generate', fn () => $this->buildReport());
     */
    function benchmark(string $label, \Closure $callback): mixed
    {
        return \Footup\Benchmark::measure($label, $callback);
    }
}

if (! function_exists('logger')) {
    /**
     * logger('User logged in', ['id' => 42]).
     */
    function logger(string $message, array $context = []): void
    {
        \Footup\Log::info($message, $context);
    }
}

if (! function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \Footup\Http\Csrf::token();
    }
}
 
if (! function_exists('validate')) {
    function validate(array $data, array $rules): \Footup\Utils\Validator\Validator
    {
        return new \Footup\Utils\Validator\Validator($data, $rules);
    }
}

if (! function_exists('cache')) {
    function cache(string $key, ?callable $callback = null, int $seconds = 3600): mixed
    {
        if ($callback === null) {
            return \Footup\Cache::get($key);
        }

        return \Footup\Cache::remember($key, $seconds, $callback);
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
    function trans(string $key, array $replace = []): string
    {
        return \Footup\i18n::trans($key, $replace);
    }
}

if (! function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return trans($key, $replace);
    }
}

if (! function_exists('auth')) {
    function auth()
    {
        return \Footup\Auth\Auth::class;
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
        \Footup\Queue::push($jobClass, $data);
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
		return \Footup\i18n::trans($indice, $params, $locale);
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

		return file_exists(public_path("/assets/css/" . $file)) ? Html::link(null, [
			"rel" => "stylesheet",
			"href" => asset("assets/css/" . $file),
			"type" => "text/css"
		]) : "";
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

		return file_exists(public_path("/assets/js/" . $file)) ? Html::script(null, [
			"src" => asset("assets/js/" . $file),
			"type" => "text/javascript"
		]) : "";
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
    function asset(string $path): string
    {
        return url(ltrim($path, '/'));
    }
}
 
if (! function_exists('storage_url')) {
    /**
     * Generate a URL to a storage file
     * e.g. storage_url('uploads/avatar.jpg') → /storage/uploads/avatar.jpg
     */
    function storage_url(string $path): string
    {
        return url('storage/' . ltrim($path, '/'));
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
			$q = request()->query();
			$query = !empty($q) ? "?" . http_build_query($q, "_key", "&") : $query;
		}

    return $base_url . $query;
	}
}