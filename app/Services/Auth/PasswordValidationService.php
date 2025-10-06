<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PasswordValidationService
{
    /**
     * Valida la password del usuario
     */
    public function validate(User $user, string $password): void
    {
        try {
            if (!Hash::check($password, $user->password)) {
                $this->logFailedAttempt($user, 'invalid_password');
                
                throw ValidationException::withMessages([
                    'password' => ['Credenciales inválidas.']
                ]);
            }
            
            $this->logSuccessfulValidation($user);
            
        } catch (\Exception $e) {
            // Capturar cualquier error crítico durante la validación
            $this->logCriticalError($user, $password, $e);
            
            // Re-lanzar ValidationException, pero convertir otros errores
            if ($e instanceof ValidationException) {
                throw $e;
            }
            
            throw ValidationException::withMessages([
                'password' => ['Error interno durante la validación.']
            ]);
        }
    }

    /**
     * Verifica si una password es válida sin lanzar excepciones
     */
    public function isValid(User $user, string $password): bool
    {
        try {
            $this->validate($user, $password);
            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * Valida la fortaleza de una nueva password
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra mayúscula.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra minúscula.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un número.';
        }
        
        return $errors;
    }

    /**
     * Genera un hash seguro de password
     */
    public function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Verifica si una password necesita ser re-hasheada
     */
    public function needsRehash(string $hashedPassword): bool
    {
        return Hash::needsRehash($hashedPassword);
    }

    /**
     * Log de intento fallido
     */
    private function logFailedAttempt(User $user, string $reason): void
    {
        Log::warning('Failed password validation', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log de validación exitosa
     */
    private function logSuccessfulValidation(User $user): void
    {
        Log::info('Successful password validation', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log de error crítico durante validación
     */
    private function logCriticalError(User $user, string $password, \Exception $e): void
    {
        Log::critical('Critical error during password validation', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'password_length' => strlen($password),
            'password_hash_length' => strlen($user->password ?? ''),
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
