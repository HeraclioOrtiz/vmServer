<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\UserType;
use App\DTOs\AuthResult;
use App\Services\Core\CacheService;
use App\Services\User\UserRefreshService;
use App\Services\User\UserPromotionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function __construct(
        private CacheService $cacheService,
        private UserRefreshService $userRefreshService,
        private UserPromotionService $userPromotionService,
        private PasswordValidationService $passwordValidationService
    ) {}

    /**
     * Autentica un usuario por DNI y password
     */
    public function authenticate(string $dni, string $password): AuthResult
    {
        // 1. Intentar desde cache primero
        $cached = $this->cacheService->getUser($dni);
        if ($cached) {
            return $this->validateCachedUser($cached, $password);
        }
        
        // 2. Buscar en base de datos
        $user = User::where('dni', $dni)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'dni' => ['Usuario no registrado. Debe registrarse primero.']
            ]);
        }
        
        // 3. Validar password
        $this->passwordValidationService->validate($user, $password);
        
        $refreshed = false;
        $promoted = false;
        
        // 4. Intentar promoción automática para usuarios LOCAL (si está habilitado)
        if ($this->shouldAttemptPromotion($user)) {
            $promoted = $this->attemptPromotion($user);
            if ($promoted) {
                $user->refresh(); // Recargar usuario después de promoción
            }
        }
        
        // 5. Auto-refresh para usuarios API si es necesario
        if ($user->user_type === UserType::API && $user->needsRefresh()) {
            $refreshed = $this->userRefreshService->refreshFromApi($user);
        }
        
        // 6. Actualizar cache
        $this->cacheService->putUser($user);
        
        return new AuthResult($user, false, $refreshed, $promoted);
    }

    /**
     * Valida un usuario desde cache
     */
    private function validateCachedUser(User $cached, string $password): AuthResult
    {
        $this->passwordValidationService->validate($cached, $password);
        
        $refreshed = false;
        $promoted = false;
        
        // Intentar promoción automática para usuarios LOCAL (si está habilitado)
        if ($this->shouldAttemptPromotion($cached)) {
            $promoted = $this->attemptPromotion($cached);
            if ($promoted) {
                $cached->refresh(); // Recargar usuario después de promoción
            }
        }
        
        // Verificar si necesita refresh (solo usuarios API)
        if ($cached->user_type === UserType::API && $cached->needsRefresh()) {
            $refreshed = $this->userRefreshService->refreshFromApi($cached);
        }
        
        return new AuthResult($cached, false, $refreshed, $promoted);
    }

    /**
     * Verifica si las credenciales son válidas sin autenticar
     */
    public function validateCredentials(string $dni, string $password): bool
    {
        try {
            $this->authenticate($dni, $password);
            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * Obtiene un usuario por DNI (sin autenticar)
     */
    public function getUserByDni(string $dni): ?User
    {
        // Intentar desde cache primero
        $cached = $this->cacheService->getUser($dni);
        if ($cached) {
            return $cached;
        }

        // Buscar en base de datos
        return User::where('dni', $dni)->first();
    }

    /**
     * Determina si se debe intentar promoción automática
     */
    private function shouldAttemptPromotion(User $user): bool
    {
        // Solo para usuarios LOCAL
        if (!$user->canPromote()) {
            return false;
        }

        // Verificar si está habilitado en configuración
        if (!config('socios.sync.auto_promote_on_login', true)) {
            return false;
        }

        // Respetar circuit breaker
        if ($this->cacheService->isCircuitBreakerOpen()) {
            Log::info('Circuit breaker abierto, saltando promoción en login', ['dni' => $user->dni]);
            return false;
        }

        return true;
    }

    /**
     * Intenta promover un usuario LOCAL a API
     */
    private function attemptPromotion(User $user): bool
    {
        try {
            $wasPromoted = $this->userPromotionService->checkApiAndPromoteIfEligible($user);
            
            if ($wasPromoted) {
                Log::info('Usuario promovido durante login', [
                    'user_id' => $user->id,
                    'dni' => $user->dni,
                ]);
            }
            
            return $wasPromoted;
            
        } catch (\Exception $e) {
            Log::warning('Error intentando promoción durante login', [
                'dni' => $user->dni,
                'error' => $e->getMessage(),
            ]);
            
            // No lanzar excepción para no bloquear el login
            return false;
        }
    }
}
