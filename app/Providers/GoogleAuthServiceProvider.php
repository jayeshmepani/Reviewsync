<?php

namespace App\Providers;

use App\Services\GoogleAuthService;
use Illuminate\Support\ServiceProvider;

class GoogleAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(GoogleAuthService::class, function ($app) {
            // Get the authenticated user's ID
            $userId = auth()->id();

            // Return a new instance of GoogleAuthService with the $userId
            return new GoogleAuthService($userId);
        });
    }

    public function boot()
    {
        //
    }
}
