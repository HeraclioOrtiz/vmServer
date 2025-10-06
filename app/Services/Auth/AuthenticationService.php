<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\UserType;
use App\DTOs\AuthResult;
use App\Services\Core\CacheService;
use App\Services\User\UserRefreshService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function __construct(
        private CacheService $cacheService,
        private UserRefreshService $userRefreshService,
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
        
        // 4. Auto-refresh para usuarios API si es necesario
        $refreshed = false;
        if ($user->user_type === UserType::API && $user->needsRefresh()) {
            $refreshed = $this->userRefreshService->refreshFromApi($user);
        }
        
        // 5. Actualizar cache
        $this->cacheService->putUser($user);
        
        return new AuthResult($user, false, $refreshed);
    }

    /**
     * Valida un usuario desde cache
     */
    private function validateCachedUser(User $cached, string $password): AuthResult
    {
        $this->passwordValidationService->validate($cached, $password);
        
        // Verificar si necesita refresh (solo usuarios API)
        $refreshed = false;
        if ($cached->user_type === UserType::API && $cached->needsRefresh()) {
            $refreshed = $this->userRefreshService->refreshFromApi($cached);
        }
        
        return new AuthResult($cached, false, $refreshed);
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
}
