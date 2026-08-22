<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use Footup\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes return JSON responses. Exceptions render as JSON.
*/

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);

Route::group(['middleware' => ['AuthMiddleware']], function () {
    Route::post('/users', [UserController::class, 'store']);
});

Route::get('/uuid', function () {
    if (class_exists(\Ramsey\Uuid\Uuid::class)) {
        return json(['uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString()]);
    }

    return json(['note' => 'Run "composer require ramsey/uuid" to enable this route.']);
});