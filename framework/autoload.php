<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Autoloading
|--------------------------------------------------------------------------
| Included by both public/index.php and bin/console.php so the loading
| logic exists in exactly one place.
|
| Loads Composer's autoloader if present — this is what makes any
| `composer require ...` package available everywhere, and autoloads
| this framework's own classes + helpers per composer.json's PSR-4 +
| files mappings.
|
| Falls back to a tiny hand-rolled PSR-4 autoloader for the Core\
| namespace so the framework still runs with zero Composer setup: every
| class lives at framework/<relative class path>.php (Footup\Application ->
| framework/Application.php, Footup\Facades\Route -> framework/Facades/Route.php),
| loaded lazily the first time it's referenced.
*/

$basePath = dirname(__DIR__);
$frameworkPath = dirname(__FILE__);

if (is_file($basePath . '/vendor/autoload.php')) {
    require $basePath . '/vendor/autoload.php';

    return;
}

spl_autoload_register(function (string $class) use ($basePath, $frameworkPath): void {
    $path = null;

    if (! str_starts_with($class, 'Footup\\')) {
        if (str_contains($class, '\\')) {
            $parts = explode('\\', $class);
            $parts[0] = strtolower($parts[0]);
            $class = implode('\\', $parts);
        }
        
        $file = str_replace('\\', '/', $class);
        $fullPath = $basePath . '/' . $file . ".php";

        if (file_exists($fullPath)) {
            $path = $fullPath;
        }
    } else {
        $relative = str_replace('\\', '/', substr($class, strlen('Footup\\')));
        $path = "$frameworkPath/{$relative}.php";
    }

    // 2. Vérification stricte que $path est défini et qu'il s'agit d'un fichier valide
    if ($path !== null && is_file($path)) {
        require $path;
    }
});

require  "{$frameworkPath}/helpers.php";
