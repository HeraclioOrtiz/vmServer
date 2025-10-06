<?php

namespace App\Services\Core;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    private const USER_TTL = 3600; // 1 hora
    private const NEGATIVE_TTL = 900; // 15 minutos
    private const CIRCUIT_BREAKER_TTL = 300; // 5 minutos

    /**
     * Obtiene un usuario del cache por DNI
     */
    public function getUser(string $dni): ?User
    {
        $key = $this->getUserKey($dni);
        $user = Cache::get($key);
        
        if ($user) {
            Log::debug('Cache hit for user', ['dni' => $dni]);
        }
        
        return $user;
    }

    /**
     * Almacena un usuario en cache
     */
    public function putUser(User $user): void
    {
        $key = $this->getUserKey($user->dni);
        Cache::put($key, $user, self::USER_TTL);
        
        Log::debug('User cached', ['dni' => $user->dni, 'ttl' => self::USER_TTL]);
    }

    /**
     * Limpia el cache de un usuario
     */
    public function forgetUser(string $dni): void
    {
        Cache::forget($this->getUserKey($dni));
    }

    /**
     * Limpia resultado negativo de cache
     */
    public function clearNegativeResult(string $dni): void
    {
        Cache::forget($this->getNegativeKey($dni));
    }

    /**
     * Verifica si existe un resultado negativo en cache
     */
    public function hasNegativeResult(string $dni): bool
    {
        return Cache::has($this->getNegativeKey($dni));
    }

    /**
     * Almacena un resultado negativo en cache
     */
    public function putNegativeResult(string $dni): void
    {
        $key = $this->getNegativeKey($dni);
        Cache::put($key, true, self::NEGATIVE_TTL);
        
        Log::debug('Negative result cached', ['dni' => $dni, 'ttl' => self::NEGATIVE_TTL]);
    }

    /**
     * Elimina un resultado negativo del cache
     */
    public function forgetNegativeResult(string $dni): void
    {
        $key = $this->getNegativeKey($dni);
        Cache::forget($key);
    }

    /**
     * Verifica si el circuit breaker está activo para la API
     */
    public function isCircuitBreakerOpen(): bool
    {
        return Cache::has($this->getCircuitBreakerKey());
    }

    /**
     * Activa el circuit breaker
     */
    public function openCircuitBreaker(): void
    {
        $key = $this->getCircuitBreakerKey();
        Cache::put($key, true, self::CIRCUIT_BREAKER_TTL);
        
        Log::warning('Circuit breaker opened', ['ttl' => self::CIRCUIT_BREAKER_TTL]);
    }

    /**
     * Desactiva el circuit breaker
     */
    public function closeCircuitBreaker(): void
    {
        $key = $this->getCircuitBreakerKey();
        Cache::forget($key);
        
        Log::info('Circuit breaker closed');
    }

    /**
     * Incrementa el contador de fallos de API
     */
    public function incrementApiFailures(): int
    {
        $key = 'api_failures_count';
        $count = Cache::increment($key, 1);
        
        if ($count === 1) {
            // Establecer TTL en el primer fallo
            Cache::put($key, $count, self::CIRCUIT_BREAKER_TTL);
        }
        
        Log::debug('API failure count incremented', ['count' => $count]);
        
        return $count;
    }

    /**
     * Obtiene el contador de fallos de API
     */
    public function getApiFailuresCount(): int
    {
        return Cache::get('api_failures_count', 0);
    }

    /**
     * Resetea el contador de fallos de API
     */
    public function resetApiFailures(): void
    {
        Cache::forget('api_failures_count');
        Log::debug('API failure count reset');
    }

    /**
     * Limpia todo el cache relacionado con usuarios
     */
    public function clearAllUserCache(): void
    {
        // Laravel no tiene una forma nativa de limpiar por patrón
        // En producción se podría usar Redis con SCAN
        Cache::flush();
        
        Log::info('All user cache cleared');
    }

    /**
     * Obtiene estadísticas del cache
     */
    public function getStats(): array
    {
        return [
            'circuit_breaker_open' => $this->isCircuitBreakerOpen(),
            'api_failures_count' => $this->getApiFailuresCount(),
            'cache_driver' => config('cache.default'),
        ];
    }

    /**
     * Genera la clave de cache para un usuario
     */
    private function getUserKey(string $dni): string
    {
        return "user:dni:{$dni}";
    }

    /**
     * Genera la clave de cache para resultados negativos
     */
    private function getNegativeKey(string $dni): string
    {
        return "user:dni:{$dni}:not_found";
    }

    /**
     * Incrementa los fallos del circuit breaker
     */
    public function incrementCircuitBreakerFailures(): void
    {
        $key = 'api:circuit_breaker:failures';
        $failures = Cache::get($key, 0);
        Cache::put($key, $failures + 1, self::CIRCUIT_BREAKER_TTL);
        
        // Si hay más de 5 fallos, abrir el circuit breaker
        if ($failures >= 5) {
            Cache::put($this->getCircuitBreakerKey(), true, self::CIRCUIT_BREAKER_TTL);
        }
    }

    /**
     * Genera la clave de cache para el circuit breaker
     */
    private function getCircuitBreakerKey(): string
    {
        return 'api:circuit_breaker:open';
    }
}
