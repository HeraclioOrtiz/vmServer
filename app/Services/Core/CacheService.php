<?php

namespace App\Services\Core;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    // User cache TTLs
    private const USER_TTL = 3600; // 1 hora
    private const NEGATIVE_TTL = 900; // 15 minutos
    private const CIRCUIT_BREAKER_TTL = 300; // 5 minutos

    // Gym cache TTLs (centralized)
    private const STATS_TTL = 300; // 5 minutos - Statistics
    private const LIST_TTL = 600; // 10 minutos - List data (most used, etc)
    private const FILTER_TTL = 1800; // 30 minutos - Filter options (rarely change)

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

    // ==================== GENERIC CACHE METHODS ====================

    /**
     * Remember stats data (5 min TTL)
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberStats(string $key, callable $callback): mixed
    {
        return Cache::remember($key, self::STATS_TTL, $callback);
    }

    /**
     * Remember list data (10 min TTL)
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberList(string $key, callable $callback): mixed
    {
        return Cache::remember($key, self::LIST_TTL, $callback);
    }

    /**
     * Remember filter options (30 min TTL)
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberFilters(string $key, callable $callback): mixed
    {
        return Cache::remember($key, self::FILTER_TTL, $callback);
    }

    /**
     * Remember with custom TTL
     *
     * @param string $key
     * @param int $ttl Seconds
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Forget cache key(s)
     *
     * @param string|array $keys
     * @return void
     */
    public function forget(string|array $keys): void
    {
        if (is_array($keys)) {
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } else {
            Cache::forget($keys);
        }
    }

    /**
     * Clear cache by pattern (driver-agnostic)
     *
     * @param string $pattern Example: "templates_*"
     * @return int Number of keys cleared
     */
    public function clearByPattern(string $pattern): int
    {
        $driver = config('cache.default');

        // Redis-specific implementation
        if ($driver === 'redis') {
            try {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                $count = 0;

                foreach ($keys as $key) {
                    // Remove prefix from key
                    $cleanKey = str_replace($redis->getOptions()->prefix->getPrefix(), '', $key);
                    Cache::forget($cleanKey);
                    $count++;
                }

                Log::debug('Cache cleared by pattern', [
                    'pattern' => $pattern,
                    'count' => $count
                ]);

                return $count;
            } catch (\Exception $e) {
                Log::warning('Failed to clear cache by pattern', [
                    'pattern' => $pattern,
                    'error' => $e->getMessage()
                ]);
                return 0;
            }
        }

        // For non-Redis drivers, just flush all (less efficient but works)
        Log::warning('clearByPattern not supported for driver, using flush', [
            'driver' => $driver,
            'pattern' => $pattern
        ]);

        Cache::flush();
        return -1; // Unknown count
    }

    /**
     * Get cache TTL constants
     *
     * @return array
     */
    public function getTTLs(): array
    {
        return [
            'user' => self::USER_TTL,
            'stats' => self::STATS_TTL,
            'list' => self::LIST_TTL,
            'filters' => self::FILTER_TTL,
            'negative' => self::NEGATIVE_TTL,
            'circuit_breaker' => self::CIRCUIT_BREAKER_TTL,
        ];
    }
}
