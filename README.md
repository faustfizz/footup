# Micro Framework

A tiny, Laravel-flavoured PHP framework: a service container, HTTP
request/response, a router with grouping and `{params}`, a middleware
pipeline, a view layer, an exception handler, `.env` config, a DB
layer with migrations, a logger, built-in request timing/benchmarking,
and Composer support — one class per file, PSR-4 throughout.

## Layout

```
bin/console.php       Tiny CLI (serve, route:list, migrate, bench)
config/app.php         App config (reads from .env)
config/database.php    DB connection config (sqlite by default)
core/autoload.php      Composer if present, else a tiny built-in autoloader
core/*.php             One class/interface per file — see "Core classes" below
core/Facades/Route.php Route::get()/post()/group() static facade
core/helpers.php       Global sugar: app(), env(), config(), view(), abort(), benchmark()...
app/bootstrap.php      YOUR app: middleware, controllers, models, routes
database/migrations/   Plain .sql migrations, run via bin/console.php migrate
public/index.php       Front controller — the single HTTP entry point
public/.htaccess       Apache rewrite rules (routes everything to index.php)
views/home.php         Example plain-PHP template
storage/logs/          app.log lands here
composer.json          PSR-4 autoloads core/ + app/, plus any packages you require
.env.example            Copy to .env and adjust
```

### Core classes (`core/*.php`)

| File | What it is |
|---|---|
| `Container.php` | Service container — `bind()`/`singleton()`/`make()`, reflection-based autowiring |
| `Application.php` | The Kernel — boots everything, turns a `Request` into a `Response` |
| `Request.php` / `Response.php` | HTTP request/response |
| `Route.php` / `Router.php` | A registered route, and the router that matches against them |
| `Pipeline.php` / `MiddlewareInterface.php` | Middleware execution ("onion" style) |
| `Controller.php` | Optional base controller — `json()`/`view()`/`response()` helpers |
| `View.php` | Plain-PHP template renderer |
| `Env.php` | `.env` loader |
| `Log.php` | Tiny file logger |
| `Benchmark.php` | Labeled stopwatch — powers request timing/benchmarking |
| `Connection.php` / `QueryBuilder.php` / `DB.php` / `Model.php` / `Migrator.php` | The DB layer |
| `HttpException.php` / `NotFoundHttpException.php` / `MethodNotAllowedHttpException.php` | HTTP error types |
| `ExceptionHandlerInterface.php` / `ExceptionHandler.php` | Turns uncaught errors into a `Response` |
| `Facades/Route.php` | `Core\Facades\Route` — static `Route::get()`/`post()`/`group()` |

## Quick start

```bash
cp .env.example .env

# Optional — only needed once you want Composer packages. Without this,
# core/autoload.php falls back to a small built-in PSR-4 autoloader.
composer install

php bin/console.php migrate
php bin/console.php serve
# or: php -S localhost:8000 -t public
```

Then visit `http://localhost:8000/`, `/welcome`, `/ping`, `/api/users`,
`/api/users/1`, or `POST /api/users` with `Authorization: Bearer secret-token`.

## Using Composer packages

`composer require` anything and it's available everywhere — no extra
wiring. Both entry points (`public/index.php`, `bin/console.php`) load
`core/autoload.php`, which loads `vendor/autoload.php` if present.
`composer.json` maps the `Core\` namespace to `core/` (PSR-4) and
`App\` to `app/`, so Composer becomes the single autoloader for
everything: your packages, the framework's own classes, and any
classes you add under `app/`.

Without Composer installed, `core/autoload.php` falls back to a ~15-line
hand-rolled autoloader that resolves `Core\Foo\Bar` to
`core/Foo/Bar.php` — the same convention PSR-4 uses, so nothing has to
change if you add Composer later.

The `/uuid` route in `app/bootstrap.php` demonstrates this: it's a dead
route until you run `composer require ramsey/uuid`, then it works with
zero code changes.

## Adding a route

```php
// in app/bootstrap.php, inside $app->routes(function () { ... })
use Footup\Facades\Route;

Route::get('/hello/{name}', fn (Request $request, string $name) => json([
    'hello' => $name,
]));
```

`Route::get()`/`post()`/`group()`/etc. are a static facade
(`Core\Facades\Route`) over the booted `Router` — Laravel-style syntax
with no `$router` instance to pass around. It's a separate class/
namespace from `Core\Route` (the object representing one registered
route: method, uri, action, middleware) to avoid a naming collision,
same as Laravel keeps its facade and its route model apart.

## Adding middleware

```php
final class RateLimitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        // ... check/update a counter ...
        return $next($request);
    }
}

// global (every request):
$app->middleware([...$existing, RateLimitMiddleware::class]);

// or per-route/group:
Route::get('/expensive', $action)->middleware = [RateLimitMiddleware::class];
```

## Adding config

Drop a new file in `config/`, e.g. `config/mail.php` returning an
array — it's auto-loaded and available as `config('mail.driver')`.

## Custom error pages

Bind your own handler once and every uncaught exception renders
through it:

```php
$app->singleton(ExceptionHandlerInterface::class, MyExceptionHandler::class);
```

`app/bootstrap.php` ships a commented-out `HtmlExceptionHandler`
example.

## Logging

```php
logger('User logged in', ['id' => $user->id]);
```

Writes to `storage/logs/app.log`. Uncaught exceptions are logged there
automatically.

## Database

SQLite by default — zero setup. Run migrations, then hit the API:

```bash
php bin/console.php migrate
php bin/console.php serve
curl -X POST localhost:8000/api/users -d name="Ada Lovelace" -H "Authorization: Bearer secret-token"
curl localhost:8000/api/users
```

Swap to MySQL/Postgres by setting `DB_CONNECTION=mysql` (or `pgsql`) and
the `DB_HOST`/`DB_DATABASE`/etc. vars in `.env` — `config/database.php`
already has both ready to go.

**Query builder**, for one-off queries:

```php
use Footup\DB;

DB::table('users')->where('name', 'like', '%Ada%')->get();
DB::table('users')->insert(['name' => 'Grace Hopper']);
```

**Models**, for anything you'll reuse:

```php
final class User extends \Core\Model
{
    protected static string $table = 'users'; // optional — guessed from class name otherwise
}

User::all();                       // User[] — hydrated for you
User::find(1);                     // ?User  — hydrated for you
User::where('name', 'Ada Lovelace')->get(); // array[] — raw rows, not models (see note)

$user = (new User(['name' => 'Grace Hopper']))->save();
$user->name = 'Grace Brewster Hopper';
$user->save();
$user->delete();
```

> `Model::where()` returns the `QueryBuilder` directly, so `->get()`/`->first()`
> give you plain arrays, not model instances — only `all()`/`find()` hydrate.
> Wrap a row yourself if you need a model back: `new User($row)`.

**Migrations** live in `database/migrations/` as plain `.sql` files
(statements separated by `;`), applied in filename order and tracked
in a `migrations` table by `Core\Migrator`. `php bin/console.php migrate`
runs whatever hasn't run yet. There's no rollback ("down") support —
this is deliberately the simplest thing that works; add one if you need it.

Outgrew this? `composer require illuminate/database` and bind a
Capsule-backed connection instead of `Core\Connection` — nothing else
in the framework is coupled to these specific classes.

## Benchmarking / timing

Every response already comes back with timing info, no setup needed:

```bash
curl -I localhost:8000/welcome
# X-Response-Time: 3.42ms
# Server-Timing: view_home;dur=1.87, total;dur=3.42
```

That's `TimingMiddleware` (in `app/bootstrap.php`) reading from
`Core\Benchmark` — a small labeled stopwatch. `View::render()` and
every `Connection::statement()` call already report into it
automatically, so view-render and per-query time show up for free.
Open the Network tab in your browser's devtools and you'll see the
`Server-Timing` breakdown right there with zero extra tooling.

Time anything else the same way:

```php
$report = benchmark('report.generate', fn () => $this->buildReport());
```

For repeated sampling — e.g. "how fast does this route render on
average, p95, worst case" — use the CLI. It calls the route directly
through `Application::handle()` in-process, so the numbers measure
pure framework + controller + view time, with no PHP built-in server
or network round-trip in the way:

```bash
php bin/console.php bench /welcome 100
# Requests: 100
# Min:      1.12 ms
# Avg:      1.64 ms
# Median:   1.53 ms
# P95:      2.20 ms
# Max:      3.87 ms
```

## Where to go from here

- **Validation**: a `Core\Validator` class, or `composer require`
  something like `rakit/validation`.
- **Events**: a simple `Core\Event` dispatcher (`listen()`/`dispatch()`)
  is a natural next addition — drop it in as `core/Event.php`.
- **Named routes / URL generation**: give `Route` a `->name('users.show')`
  and add a `route('users.show', ['id' => 1])` helper.
- **Caching**: bind a `Core\Cache` interface with a file/array driver,
  swap for Redis later without touching call sites.
