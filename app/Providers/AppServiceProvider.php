<?php

namespace App\Providers;

use App\Http\Middleware\HandleCors;
use App\Interfaces\TokenStorageInterface;
use App\Services\TokenStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TokenStorageInterface::class, TokenStorage::class);
    }

    public function boot()
    {
        $this->app['router']->aliasMiddleware('handle_cors', HandleCors::class);
    }

}