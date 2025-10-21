<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ValidateResetTokenRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {
        // Rate limiting
        $this->middleware('throttle:5,60')->only(['requestReset', 'resetPassword']);
    }

    /**
     * POST /api/auth/password/forgot
     *
     * Solicita un link de recuperación
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function requestReset(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // Soportar búsqueda por email o DNI
            if ($request->has('email')) {
                $result = $this->passwordResetService->requestReset($request->email);
            } else {
                $result = $this->passwordResetService->requestResetByDni($request->dni);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/auth/password/validate-token
     *
     * Valida un token sin resetear la contraseña
     *
     * @param ValidateResetTokenRequest $request
     * @return JsonResponse
     */
    public function validateToken(ValidateResetTokenRequest $request): JsonResponse
    {
        $isValid = $this->passwordResetService->validateToken(
            $request->email,
            $request->token
        );

        return response()->json([
            'valid' => $isValid,
            'message' => $isValid
                ? 'Token válido. Puedes proceder a resetear tu contraseña.'
                : 'El token es inválido o ha expirado.'
        ]);
    }

    /**
     * POST /api/auth/password/reset
     *
     * Resetea la contraseña usando el token
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->passwordResetService->resetPassword(
                $request->email,
                $request->token,
                $request->password
            );

            if ($result['success']) {
                // Autologin opcional: generar token Sanctum
                $user = \App\Models\User::where('email', $request->email)->first();
                $token = $user->createToken('auth')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'token' => $token,
                    'user' => $user
                ]);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/auth/password/can-reset
     *
     * Verifica si un usuario puede resetear su contraseña
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function canReset(ForgotPasswordRequest $request): JsonResponse
    {
        $identifier = $request->email ?? $request->dni;
        $result = $this->passwordResetService->canResetPassword($identifier);

        return response()->json($result);
    }
}
