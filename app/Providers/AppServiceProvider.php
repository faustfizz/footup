<?php

declare(strict_types=1);

namespace App\Providers;

use Footup\Providers\ServiceProvider;

/*
|--------------------------------------------------------------------------
| AppServiceProvider
|
| The main service provider for your application. This is where you:
| - Register model observers
| - Register authorization policies
| - Bind services into the container
| - Boot application-wide configurations
|
| Create additional providers for organization:
| - AuthServiceProvider (auth, guards, policies)
| - DatabaseServiceProvider (migrations, seeds)
| - QueueServiceProvider (jobs, queue config)
| - etc.
|--------------------------------------------------------------------------
*/

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services into the container.
     * This runs BEFORE boot() and before routes are loaded.
     */
    public function register(): void
    {
        // Example: Bind custom services
        // $this->app->singleton('pdf-generator', fn() => new PdfGenerator());
        // $this->app->singleton('slack-notifier', fn() => new SlackNotifier());
    }

    /**
     * Boot application services.
     * This runs AFTER all register() calls.
     * Use this to register observers, policies, event listeners, etc.
     */
    public function boot(): void
    {
        // Register model observers
        // User::observe(\App\Observers\UserObserver::class);
        // Post::observe(\App\Observers\PostObserver::class);

        // Register authorization policies
        // \Footup\Policy::register(\App\Post::class, \App\Policies\PostPolicy::class);
        // \Footup\Policy::register(\App\Comment::class, \App\Policies\CommentPolicy::class);

        // Listen to application events
        // \Footup\Support\Event::listen('user.created', function($user) {
        //     Mail::to($user->email)->send(new WelcomeNotification());
        // });
    }
}
