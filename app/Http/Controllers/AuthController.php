<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\AuthService;
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
        $result = $this->authService->authenticate(
            $request->dni,
            $request->password
        );

        $token = $result->user->createToken('auth')->plainTextToken;

        return AuthResource::make([
            'token' => $token,
            'user' => $result->user,
            'fetched_from_api' => $result->fetchedFromApi,
            'refreshed' => $result->refreshed ?? false
        ]);
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
            'user' => $request->user()
        ]);
    }
}
