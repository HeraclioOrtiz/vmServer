<?php

namespace App\Services\Auth;

use App\Models\User;
use App\DTOs\AuthResult;
use App\Services\Core\AuditService;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private UserRegistrationService $userRegistrationService,
        private AuditService $auditService
    ) {}

    /**
     * Autentica un usuario por DNI y password
     */
    public function authenticate(string $dni, string $password): AuthResult
    {
        try {
            $result = $this->authenticationService->authenticate($dni, $password);
            
            // Log de auditoría para login exitoso
            $this->auditService->logLogin($result->user->id, true);
            
            return $result;
            
        } catch (\Exception $e) {
            // Log de auditoría para login fallido
            $user = $this->authenticationService->getUserByDni($dni);
            if ($user) {
                $this->auditService->logLogin($user->id, false);
            }
            
            throw $e;
        }
    }

    /**
     * Alias para authenticate - mantiene compatibilidad
     */
    public function login(string $dni, string $password): AuthResult
    {
        return $this->authenticate($dni, $password);
    }

    /**
     * Registra un nuevo usuario local
     */
    public function registerLocal(array $data): User
    {
        $user = $this->userRegistrationService->registerLocal($data);
        
        // Log de auditoría
        $this->auditService->logCreate('user', $user->id, [
            'dni' => $user->dni,
            'user_type' => $user->user_type->value,
            'registration_method' => 'local',
        ]);
        
        return $user;
    }

    /**
     * Registra un usuario desde datos de API externa
     */
    public function registerFromApi(array $apiData, string $password): User
    {
        $user = $this->userRegistrationService->registerFromApi($apiData, $password);
        
        // Log de auditoría
        $this->auditService->logCreate('user', $user->id, [
            'dni' => $user->dni,
            'user_type' => $user->user_type->value,
            'registration_method' => 'api',
            'socio_id' => $user->socio_id,
        ]);
        
        return $user;
    }

    /**
     * Valida credenciales sin autenticar
     */
    public function validateCredentials(string $dni, string $password): bool
    {
        return $this->authenticationService->validateCredentials($dni, $password);
    }

    /**
     * Verifica si un DNI está disponible para registro
     */
    public function isDniAvailable(string $dni): bool
    {
        return $this->userRegistrationService->isDniAvailable($dni);
    }

    /**
     * Verifica si un email está disponible para registro
     */
    public function isEmailAvailable(string $email): bool
    {
        return $this->userRegistrationService->isEmailAvailable($email);
    }

    /**
     * Valida datos de registro
     */
    public function validateRegistrationData(array $data): array
    {
        return $this->userRegistrationService->validateRegistrationData($data);
    }

    /**
     * Obtiene un usuario por DNI
     */
    public function getUserByDni(string $dni): ?User
    {
        return $this->authenticationService->getUserByDni($dni);
    }
}
