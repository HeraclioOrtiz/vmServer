<?php

namespace App\Providers;

use App\Contracts\SociosApiInterface;
use App\Services\External\SociosApi;
use App\Services\Core\CacheService;
use App\Services\Core\AuditService;
use App\Services\Auth\AuthService;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\UserRegistrationService;
use App\Services\Auth\PasswordValidationService;
use App\Services\User\UserService;
use App\Services\User\PromotionService;
use App\Services\User\UserRefreshService;
use App\Services\User\UserPromotionService;
use App\Services\External\SocioDataMappingService;
use Illuminate\Support\ServiceProvider;

class SociosServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // ==================== CORE SERVICES ====================
        
        // Cache Service (singleton para compartir estado)
        $this->app->singleton(CacheService::class);
        
        // Audit Service (singleton)
        $this->app->singleton(AuditService::class);
        
        // ==================== EXTERNAL SERVICES ====================
        
        // API Interface
        $this->app->bind(SociosApiInterface::class, SociosApi::class);
        
        // Data Mapping Service
        $this->app->singleton(SocioDataMappingService::class);
        
        // ==================== AUTH SERVICES ====================
        
        // Password Validation Service
        $this->app->singleton(PasswordValidationService::class);
        
        // Authentication Service
        $this->app->bind(AuthenticationService::class, function ($app) {
            return new AuthenticationService(
                $app->make(CacheService::class),
                $app->make(UserRefreshService::class),
                $app->make(PasswordValidationService::class)
            );
        });
        
        // User Registration Service
        $this->app->bind(UserRegistrationService::class, function ($app) {
            return new UserRegistrationService(
                $app->make(CacheService::class),
                $app->make(UserPromotionService::class),
                $app->make(PasswordValidationService::class)
            );
        });
        
        // Main Auth Service (orchestrator)
        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(AuthenticationService::class),
                $app->make(UserRegistrationService::class),
                $app->make(AuditService::class)
            );
        });
        
        // ==================== USER SERVICES ====================
        
        // User Refresh Service
        $this->app->bind(UserRefreshService::class, function ($app) {
            return new UserRefreshService(
                $app->make(SociosApiInterface::class),
                $app->make(CacheService::class),
                $app->make(SocioDataMappingService::class)
            );
        });
        
        // User Promotion Service
        $this->app->bind(UserPromotionService::class, function ($app) {
            return new UserPromotionService(
                $app->make(SociosApiInterface::class),
                $app->make(CacheService::class),
                $app->make(SocioDataMappingService::class),
                $app->make(AuditService::class)
            );
        });
        
        // User Service (legacy compatibility)
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(CacheService::class)
            );
        });
        
        // Promotion Service (legacy compatibility)
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
