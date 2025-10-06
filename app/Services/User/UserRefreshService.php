<?php

namespace App\Services\User;

use App\Models\User;
use App\Contracts\SociosApiInterface;
use App\Services\Core\CacheService;
use App\Services\External\SocioDataMappingService;
use Illuminate\Support\Facades\Log;

class UserRefreshService
{
    public function __construct(
        private SociosApiInterface $sociosApi,
        private CacheService $cacheService,
        private SocioDataMappingService $mappingService
    ) {}

    /**
     * Refresca datos de usuario desde la API externa
     */
    public function refreshFromApi(User $user): bool
    {
        if ($this->cacheService->isCircuitBreakerOpen()) {
            Log::warning('Circuit breaker abierto, saltando refresh', ['dni' => $user->dni]);
            return false;
        }
        
        try {
            $socio = $this->sociosApi->getSocioPorDni($user->dni);
            
            if (!$socio) {
                Log::warning('No se pudo obtener datos del socio desde API', ['dni' => $user->dni]);
                return false;
            }
            
            $updateData = $this->mappingService->mapSocioToUserData($socio, $user->dni, $user->password);
            
            // Preservar campos críticos
            unset($updateData['password']);
            unset($updateData['user_type']);
            unset($updateData['dni']);
            
            $user->update($updateData);
            $user->markAsRefreshed();
            
            // Actualizar cache
            $this->cacheService->putUser($user->fresh());
            
            $this->logSuccessfulRefresh($user);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logRefreshError($user, $e);
            $this->cacheService->incrementCircuitBreakerFailures();
            return false;
        }
    }

    /**
     * Refresca múltiples usuarios en lote
     */
    public function refreshMultipleUsers(array $userIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            
            if (!$user) {
                $results['skipped']++;
                $results['errors'][] = "Usuario ID {$userId} no encontrado";
                continue;
            }

            if (!$user->needsRefresh()) {
                $results['skipped']++;
                continue;
            }

            if ($this->refreshFromApi($user)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Error refrescando usuario {$user->dni}";
            }
        }

        return $results;
    }

    /**
     * Refresca usuarios que necesitan actualización
     */
    public function refreshStaleUsers(int $hours = 24, int $limit = 50): array
    {
        $staleUsers = User::needsRefresh($hours)->limit($limit)->get();
        
        Log::info('Iniciando refresh de usuarios obsoletos', [
            'count' => $staleUsers->count(),
            'hours_threshold' => $hours
        ]);

        return $this->refreshMultipleUsers($staleUsers->pluck('id')->toArray());
    }

    /**
     * Fuerza el refresh de un usuario específico
     */
    public function forceRefresh(User $user): bool
    {
        // Temporalmente deshabilitar circuit breaker para refresh forzado
        $originalState = $this->cacheService->isCircuitBreakerOpen();
        
        if ($originalState) {
            $this->cacheService->resetCircuitBreaker();
        }

        $result = $this->refreshFromApi($user);

        // Restaurar estado del circuit breaker si era necesario
        if ($originalState) {
            $this->cacheService->openCircuitBreaker();
        }

        return $result;
    }

    /**
     * Verifica si un usuario puede ser refrescado
     */
    public function canRefresh(User $user): bool
    {
        return $user->isApi() && 
               !$this->cacheService->isCircuitBreakerOpen() &&
               !$this->isRefreshInProgress($user);
    }

    /**
     * Verifica si hay un refresh en progreso para un usuario
     */
    private function isRefreshInProgress(User $user): bool
    {
        $lockKey = "user_refresh_lock:{$user->id}";
        return $this->cacheService->has($lockKey);
    }

    /**
     * Establece un lock de refresh para un usuario
     */
    private function setRefreshLock(User $user): void
    {
        $lockKey = "user_refresh_lock:{$user->id}";
        $this->cacheService->put($lockKey, true, 300); // 5 minutos
    }

    /**
     * Libera el lock de refresh para un usuario
     */
    private function releaseRefreshLock(User $user): void
    {
        $lockKey = "user_refresh_lock:{$user->id}";
        $this->cacheService->forget($lockKey);
    }

    /**
     * Log de refresh exitoso
     */
    private function logSuccessfulRefresh(User $user): void
    {
        Log::info('Usuario refrescado exitosamente desde API', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'previous_update' => $user->api_updated_at?->toISOString(),
            'new_update' => now()->toISOString(),
        ]);
    }

    /**
     * Log de error en refresh
     */
    private function logRefreshError(User $user, \Exception $e): void
    {
        Log::error('Error refrescando usuario desde API', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}
