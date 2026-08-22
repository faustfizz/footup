#!/usr/bin/env php
<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Console
|--------------------------------------------------------------------------
| A minimal command runner — the seed of a bigger `artisan`-style CLI.
| Add new commands to the match() block below as you need them
| (queue workers, scheduled tasks, etc.).
|
| Usage:
|   php bin/console.php serve         # run the built-in dev server
|   php bin/console.php route:list    # list all registered routes
|   php bin/console.php migrate       # run pending database/migrations/*.sql
|   php bin/console.php bench /path 50 GET   # in-process render-time benchmark
*/

$basePath = dirname(__DIR__);

require $basePath . '/framework/autoload.php';

use Footup\Env;

Env::load($basePath . '/.env');

/** @var \Footup\Engine\Application $app */
$app = require $basePath . '/app/app.php';

$command = $argv[1] ?? 'list';

match ($command) {
    'serve' => (function () use ($basePath): void {
        $host = '127.0.0.1';
        $port = 8000;

        fwrite(STDOUT, "Starting dev server at http://{$host}:{$port} (Ctrl+C to stop)\n");
        passthru(sprintf('php -S %s:%d -t %s', $host, $port, escapeshellarg($basePath . '/public')));
    })(),

    'route:list' => (function () use ($app): void {
        fwrite(STDOUT, str_pad('METHOD', 8) . "URI\n");
        fwrite(STDOUT, str_repeat('-', 40) . "\n");

        foreach ($app->router()->all() as $route) {
            fwrite(STDOUT, str_pad($route->method, 8) . $route->uri . "\n");
        }
    })(),

    'migrate' => (function () use ($basePath): void {
        $migrator = new \Footup\Database\Migrator(\Footup\Database\DB::connection(), $basePath . '/database/migrations');
        $ran = $migrator->run();

        if (! $ran) {
            fwrite(STDOUT, "Nothing to migrate.\n");

            return;
        }

        foreach ($ran as $name) {
            fwrite(STDOUT, "Migrated: {$name}\n");
        }
    })(),

    'seed' => (function () use ($basePath): void {
        $seedPath = $basePath . '/database/seeds';

        if (! is_dir($seedPath)) {
            fwrite(STDOUT, "No seeders found. Create files in database/seeds/\n");

            return;
        }

        foreach (glob($seedPath . '/*.php') as $file) {
            require $file;
            $className = 'DatabaseSeeder\\' . basename($file, '.php');

            if (class_exists($className)) {
                (new $className())->run();
                fwrite(STDOUT, "Seeded: " . basename($file, '.php') . "\n");
            }
        }
    })(),

    'queue:work' => (function (): void {
        fwrite(STDOUT, "Processing queue...\n");
        \Footup\Queue::work();
        fwrite(STDOUT, "Queue processed.\n");
    })(),

    'bench' => (function () use ($app, $argv): void {
        $path = $argv[2] ?? '/';
        $requests = max(1, (int) ($argv[3] ?? 50));
        $method = strtoupper($argv[4] ?? 'GET');

        fwrite(STDOUT, "Benchmarking {$method} {$path} ({$requests} requests, in-process — no network/server overhead)...\n\n");

        $durations = [];

        for ($i = 0; $i < $requests; $i++) {
            $request = new \Footup\Http\Request(method: $method, uri: $path);

            $start = microtime(true);
            $response = $app->handle($request);
            $durations[] = (microtime(true) - $start) * 1000;

            if ($response->status >= 500) {
                fwrite(STDERR, "  Warning: request #{$i} returned HTTP {$response->status}\n");
            }
        }

        sort($durations);
        $count = count($durations);
        $percentile = fn (float $p) => $durations[(int) min($count - 1, floor($count * $p))];

        fwrite(STDOUT, sprintf("Requests: %d\n", $count));
        fwrite(STDOUT, sprintf("Min:      %.2f ms\n", $durations[0]));
        fwrite(STDOUT, sprintf("Avg:      %.2f ms\n", array_sum($durations) / $count));
        fwrite(STDOUT, sprintf("Median:   %.2f ms\n", $percentile(0.50)));
        fwrite(STDOUT, sprintf("P95:      %.2f ms\n", $percentile(0.95)));
        fwrite(STDOUT, sprintf("Max:      %.2f ms\n", $durations[$count - 1]));
    })(),

    'list', 'help' => (function (): void {
        fwrite(STDOUT, "Available commands:\n\n");
        fwrite(STDOUT, "  serve              Start development server on localhost:8000\n");
        fwrite(STDOUT, "  route:list         Display all registered routes\n");
        fwrite(STDOUT, "  migrate            Run pending migrations\n");
        fwrite(STDOUT, "  seed               Run all seeders in database/seeds/\n");
        fwrite(STDOUT, "  queue:work         Process queued jobs\n");
        fwrite(STDOUT, "  bench <path> [n]   Benchmark a route (n=requests, default 50)\n");
    })(),

    default => fwrite(STDOUT, "Unknown command \"{$command}\". Run 'php bin/console.php list' for available commands.\n"),
};
