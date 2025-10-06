<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\UserType;
use App\Enums\PromotionStatus;
use App\Services\Core\CacheService;
use App\Services\User\UserPromotionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserRegistrationService
{
    public function __construct(
        private CacheService $cacheService,
        private UserPromotionService $userPromotionService,
        private PasswordValidationService $passwordValidationService
    ) {}

    /**
     * Registra un nuevo usuario local con validación automática de API
     */
    public function registerLocal(array $data): User
    {
        // 1. Verificar que el DNI no exista
        if (User::where('dni', $data['dni'])->exists()) {
            throw ValidationException::withMessages([
                'dni' => ['El DNI ya está registrado.']
            ]);
        }
        
        // 2. Validar fortaleza de password
        $passwordErrors = $this->passwordValidationService->validatePasswordStrength($data['password']);
        if (!empty($passwordErrors)) {
            throw ValidationException::withMessages([
                'password' => $passwordErrors
            ]);
        }
        
        // 3. Crear usuario inicialmente como LOCAL
        $userData = [
            'dni' => $data['dni'],
            'user_type' => UserType::LOCAL,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $this->passwordValidationService->hashPassword($data['password']),
            'phone' => $data['phone'] ?? null,
            'promotion_status' => PromotionStatus::NONE,
        ];
        
        $user = User::create($userData);
        
        // 4. Validación automática con API de terceros durante registro
        $this->userPromotionService->checkApiAndPromoteIfEligible($user);
        
        // 5. Cache el usuario (ya actualizado si fue promovido)
        $this->cacheService->putUser($user->fresh());
        
        $this->logSuccessfulRegistration($user);
        
        return $user->fresh();
    }

    /**
     * Registra un usuario desde datos de API externa
     */
    public function registerFromApi(array $apiData, string $password): User
    {
        // 1. Verificar que el DNI no exista
        if (User::where('dni', $apiData['dni'])->exists()) {
            throw ValidationException::withMessages([
                'dni' => ['El DNI ya está registrado.']
            ]);
        }

        // 2. Crear usuario directamente como API
        $userData = array_merge($apiData, [
            'user_type' => UserType::API,
            'password' => $this->passwordValidationService->hashPassword($password),
            'promotion_status' => PromotionStatus::APPROVED,
            'promoted_at' => now(),
            'api_updated_at' => now(),
        ]);

        $user = User::create($userData);

        // 3. Cache el usuario
        $this->cacheService->putUser($user);

        $this->logSuccessfulRegistration($user, 'api');

        return $user;
    }

    /**
     * Valida datos de registro
     */
    public function validateRegistrationData(array $data): array
    {
        $errors = [];

        // Validar DNI
        if (empty($data['dni'])) {
            $errors['dni'] = ['El DNI es requerido.'];
        } elseif (User::where('dni', $data['dni'])->exists()) {
            $errors['dni'] = ['El DNI ya está registrado.'];
        }

        // Validar email
        if (!empty($data['email']) && User::where('email', $data['email'])->exists()) {
            $errors['email'] = ['El email ya está registrado.'];
        }

        // Validar password
        if (!empty($data['password'])) {
            $passwordErrors = $this->passwordValidationService->validatePasswordStrength($data['password']);
            if (!empty($passwordErrors)) {
                $errors['password'] = $passwordErrors;
            }
        }

        return $errors;
    }

    /**
     * Verifica si un DNI está disponible para registro
     */
    public function isDniAvailable(string $dni): bool
    {
        return !User::where('dni', $dni)->exists();
    }

    /**
     * Verifica si un email está disponible para registro
     */
    public function isEmailAvailable(string $email): bool
    {
        return !User::where('email', $email)->exists();
    }

    /**
     * Log de registro exitoso
     */
    private function logSuccessfulRegistration(User $user, string $source = 'local'): void
    {
        Log::info('Usuario registrado exitosamente', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'email' => $user->email,
            'user_type' => $user->user_type->value,
            'source' => $source,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
