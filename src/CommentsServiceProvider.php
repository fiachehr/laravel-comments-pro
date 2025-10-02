<?php

namespace Fiachehr\Comments;

use Illuminate\Support\ServiceProvider;
use Fiachehr\Comments\Services\RecaptchaVerifier;

class CommentsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/comments.php', 'comments');

        $this->app->singleton('comments.recaptcha', function ($app) {
            return new Services\RecaptchaVerifier(
                config('comments.recaptcha.secret'),
                config('comments.recaptcha.version'),
                config('comments.recaptcha.score')
            );
        });

        $this->app->singleton('comments.service', function ($app) {
            return new Services\CommentsService();
        });

        $this->app->singleton('comments.reactions.service', function ($app) {
            return new Services\ReactionService();
        });
    }

    public function boot()
    {
        // Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'comments-migrations');

        // Config
        $this->publishes([
            __DIR__ . '/../config/comments.php' => config_path('comments.php'),
        ], 'comments-config');

        // Optional stubs for controllers, requests and routes (if they exist)
        if (is_dir(__DIR__ . '/../stubs/Controllers/')) {
            $this->publishes([
                __DIR__ . '/../stubs/Controllers/' => app_path('Http/Controllers'),
            ], 'comments-controllers');
        }

        if (is_dir(__DIR__ . '/../stubs/routes/')) {
            $this->publishes([
                __DIR__ . '/../stubs/routes/' => base_path('routes'),
            ], 'comments-routes');
        }

        // Always publish requests
        $this->publishes([
            __DIR__ . '/../stubs/Requests/' => app_path('Http/Requests'),
        ], 'comments-requests');
    }
}
