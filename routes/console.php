<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use Footup\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| These routes return HTML responses. Exceptions render as HTML error pages.
*/

Route::get('/', [HomeController::class, 'index']);
Route::get('/welcome', [HomeController::class, 'welcome']);
Route::get('/ping', fn () => json(['pong' => true]));