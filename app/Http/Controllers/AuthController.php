<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Autentica un usuario (arquitectura dual)
     */
    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->authenticate(
                $request->dni,
                $request->password
            );

            $token = $result->user->createToken('auth')->plainTextToken;

            return AuthResource::make([
                'token' => $token,
                'user' => $result->user,
                'fetched_from_api' => $result->fetchedFromApi,
                'refreshed' => $result->refreshed ?? false,
                'promoted' => $result->promoted ?? false
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions (handled by Laravel)
            throw $e;
            
        } catch (\Exception $e) {
            // Log the critical error for debugging
            \Log::error('Critical error in login', [
                'dni' => $request->dni,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor durante el login',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Registra un nuevo usuario local
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->registerLocal($request->validated());
            
            $token = $user->createToken('auth')->plainTextToken;

            // Verificar si el usuario fue promovido durante el registro
            $wasPromoted = $user->user_type->value === 'api';
            
            return AuthResource::make([
                'token' => $token,
                'user' => $user,
                'fetched_from_api' => $wasPromoted,
                'refreshed' => $wasPromoted,
                'message' => 'Usuario registrado exitosamente'
            ])->response()->setStatusCode(201);
            
        } catch (\Exception $e) {
            \Log::error('Error en registro de usuario', [
                'dni' => $request->dni,
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error interno del servidor durante el registro',
                'error' => $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Obtiene los datos del usuario autenticado
     */
    public function me(Request $request)
    {
        return AuthResource::make([
            'token' => null, // No necesitamos devolver token en /me
            'user' => $request->user(),
            'fetched_from_api' => false,
            'refreshed' => false
        ]);
    }
}
