<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\UserType;
use App\Services\Core\AuditService;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PasswordResetService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Solicita un reset de contraseña por email
     *
     * @param string $email
     * @return array
     * @throws \Exception si el usuario es de tipo API
     */
    public function requestReset(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Security: No revelar si el email existe o no
            return [
                'success' => true,
                'message' => 'Si el email existe, recibirás instrucciones para resetear tu contraseña.'
            ];
        }

        // Verificar tipo de usuario
        if ($user->user_type === UserType::API) {
            $this->auditService->log(
                action: 'password_reset.rejected_api_user',
                userId: $user->id,
                metadata: ['reason' => 'API users cannot reset password locally']
            );

            throw new \Exception(
                'Los usuarios sincronizados con el club no pueden cambiar su contraseña aquí. ' .
                'Por favor, contacta a la administración del club.'
            );
        }

        // Generar y enviar token
        $status = Password::sendResetLink(['email' => $email]);

        $this->auditService->log(
            action: 'password_reset.requested',
            userId: $user->id,
            metadata: [
                'email' => $email,
                'status' => $status
            ]
        );

        return [
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => 'Si el email existe, recibirás instrucciones para resetear tu contraseña.'
        ];
    }

    /**
     * Solicita reset por DNI (busca el email asociado)
     *
     * @param string $dni
     * @return array
     */
    public function requestResetByDni(string $dni): array
    {
        $user = User::where('dni', $dni)->first();

        if (!$user || !$user->email) {
            return [
                'success' => true,
                'message' => 'Si el DNI está registrado, recibirás instrucciones en tu email.'
            ];
        }

        return $this->requestReset($user->email);
    }

    /**
     * Valida un token de reset (sin resetear la contraseña)
     *
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function validateToken(string $email, string $token): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenData) {
            return false;
        }

        // Verificar expiración (60 minutos por defecto)
        $expirationMinutes = config('auth.passwords.users.expire', 60);
        $tokenAge = now()->diffInMinutes($tokenData->created_at);

        if ($tokenAge > $expirationMinutes) {
            $this->auditService->log(
                action: 'password_reset.token_expired',
                userId: $user->id,
                metadata: ['age_minutes' => $tokenAge]
            );
            return false;
        }

        // Verificar hash del token
        return Hash::check($token, $tokenData->token);
    }

    /**
     * Resetea la contraseña usando el token
     *
     * @param string $email
     * @param string $token
     * @param string $newPassword
     * @return array
     * @throws \Exception si el usuario es de tipo API
     */
    public function resetPassword(string $email, string $token, string $newPassword): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ];
        }

        // Verificar tipo de usuario (doble check)
        if ($user->user_type === UserType::API) {
            $this->auditService->log(
                action: 'password_reset.rejected_api_user',
                userId: $user->id,
                metadata: ['reason' => 'API users cannot reset password']
            );

            throw new \Exception('Los usuarios API no pueden cambiar su contraseña localmente.');
        }

        // Intentar reset con Laravel Password Broker
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => $token
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revocar todos los tokens de sesión existentes
                $user->tokens()->delete();
            }
        );

        $success = $status === Password::PASSWORD_RESET;

        $this->auditService->log(
            action: $success ? 'password_reset.completed' : 'password_reset.failed',
            userId: $user->id,
            metadata: [
                'status' => $status,
                'ip' => request()->ip()
            ]
        );

        return [
            'success' => $success,
            'message' => $success
                ? 'Contraseña actualizada exitosamente.'
                : 'El token es inválido o ha expirado.'
        ];
    }

    /**
     * Verifica si un usuario puede resetear su contraseña
     *
     * @param string $identifier Email o DNI
     * @return array
     */
    public function canResetPassword(string $identifier): array
    {
        // Buscar por email o DNI
        $user = User::where('email', $identifier)
            ->orWhere('dni', $identifier)
            ->first();

        if (!$user) {
            return [
                'can_reset' => false,
                'reason' => 'user_not_found',
                'message' => 'Usuario no encontrado.'
            ];
        }

        if ($user->user_type === UserType::API) {
            return [
                'can_reset' => false,
                'reason' => 'api_user',
                'message' => 'Los usuarios sincronizados con el club deben contactar a la administración.',
                'contact_info' => [
                    'email' => config('mail.contact_email', 'contacto@villamitre.com'),
                    'phone' => config('gym.contact_phone', '+54 11 1234-5678')
                ]
            ];
        }

        if (!$user->email) {
            return [
                'can_reset' => false,
                'reason' => 'no_email',
                'message' => 'Este usuario no tiene un email registrado.'
            ];
        }

        return [
            'can_reset' => true,
            'email' => $user->email,
            'message' => 'Puedes resetear tu contraseña.'
        ];
    }
}
