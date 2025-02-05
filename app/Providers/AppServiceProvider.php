<?php

namespace App\Providers;

use App\Http\Middleware\HandleCors;
use App\Interfaces\TokenStorageInterface;
use App\Services\TokenStorage;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TokenStorageInterface::class, TokenStorage::class);
    }

    public function boot(UrlGenerator $url)
    {
        // Force HTTPS in production or non-local environments
        if (env('APP_ENV') !== 'local') {
            $url->forceScheme('https');
        }

        // Register CORS Middleware
        $this->app['router']->aliasMiddleware('handle_cors', HandleCors::class);
    }
}
