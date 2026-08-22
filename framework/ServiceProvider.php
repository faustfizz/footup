<?php

declare(strict_types=1);

namespace Footup;

/*
|--------------------------------------------------------------------------
| ServiceProvider — bootstrap and register application services
|
| Usage:
|   class AuthServiceProvider extends ServiceProvider {
|       public function register(): void {
|           $this->app->singleton(Guard::class, fn () => new Guard(...));
|       }
|       public function boot(): void {
|           Auth::setSecret(env('JWT_SECRET'));
|       }
|   }
|   // in bootstrap.php: $app->provider(AuthServiceProvider::class);
|--------------------------------------------------------------------------
*/

abstract class ServiceProvider
{
    public function __construct(protected Application $app)
    {
    }

    /**
     * Register services into the container. Called before boot().
     */
    public function register(): void
    {
    }

    /**
     * Boot services after all are registered. Called after all register() calls.
     */
    public function boot(): void
    {
    }
}
