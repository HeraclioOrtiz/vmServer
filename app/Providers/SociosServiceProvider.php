<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SociosApi;


class SociosServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
   $this->app->singleton(SociosApi::class, function ($app) {
            return new SociosApi();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
