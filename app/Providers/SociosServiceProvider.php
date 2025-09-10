<?php

namespace App\Providers;

use App\Contracts\SociosApiInterface;
use App\Services\SociosApi;
use App\Services\CacheService;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\PromotionService;
use Illuminate\Support\ServiceProvider;

class SociosServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // API Interface
        $this->app->bind(SociosApiInterface::class, SociosApi::class);
        
        // Cache Service (singleton para compartir estado)
        $this->app->singleton(CacheService::class);
        
        // Auth Service
        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(SociosApiInterface::class),
                $app->make(CacheService::class)
            );
        });
        
        // User Service
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(CacheService::class)
            );
        });
        
        // Promotion Service
        $this->app->bind(PromotionService::class, function ($app) {
            return new PromotionService(
                $app->make(SociosApiInterface::class),
                $app->make(CacheService::class)
            );
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
