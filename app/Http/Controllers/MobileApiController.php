<?php

namespace App\Http\Controllers;

use App\Contracts\MobileApiInterface;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\PromotionService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\DTOs\AuthResponseDTO;
use App\DTOs\UserDTO;
use App\DTOs\StatsDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controlador específico para la API móvil de Villamitre
 */
class MobileApiController extends Controller implements MobileApiInterface
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService,
        private PromotionService $promotionService
    ) {}

    /**
     * Autenticar usuario por DNI
     */
    public function login(string $dni, string $password): JsonResponse
    {
        $user = $this->authService->authenticateByDni($dni, $password);
        
        if (!$user) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $token = $user->createToken('mobile-auth')->plainTextToken;
        
        $authResponse = new AuthResponseDTO(
            token: $token,
            user: UserDTO::fromModel($user),
            fetchedFromApi: $user->user_type->value === 'api',
            refreshed: false
        );

        return response()->json([
            'data' => $authResponse->toArray()
        ]);
    }

    /**
     * Registrar nuevo usuario con promoción automática
     */
    public function register(array $userData): JsonResponse
    {
        $user = $this->authService->registerLocal($userData);
        $token = $user->createToken('mobile-auth')->plainTextToken;
        
        $wasPromoted = $user->user_type->value === 'api';
        
        $authResponse = new AuthResponseDTO(
            token: $token,
            user: UserDTO::fromModel($user),
            fetchedFromApi: $wasPromoted,
            refreshed: $wasPromoted,
            message: 'Usuario registrado exitosamente'
        );

        return response()->json([
            'data' => $authResponse->toArray()
        ], 201);
    }

    /**
     * Obtener datos del usuario autenticado
     */
    public function me(User $user): JsonResponse
    {
        return response()->json([
            'data' => UserDTO::fromModel($user)->toArray()
        ]);
    }

    /**
     * Cerrar sesión del usuario
     */
    public function logout(User $user): JsonResponse
    {
        $user->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Obtener datos de un usuario específico
     */
    public function getUser(int $userId): JsonResponse
    {
        $user = $this->userService->findById($userId);
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'data' => UserDTO::fromModel($user)->toArray()
        ]);
    }

    /**
     * Actualizar datos de usuario
     */
    public function updateUser(int $userId, array $data): JsonResponse
    {
        $user = $this->userService->findById($userId);
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $updatedUser = $this->userService->update($user, $data);

        return response()->json([
            'data' => UserDTO::fromModel($updatedUser)->toArray(),
            'message' => 'Usuario actualizado exitosamente'
        ]);
    }

    /**
     * Verificar si un DNI existe en la API del club
     */
    public function checkDniInClub(string $dni): JsonResponse
    {
        $result = $this->promotionService->checkDniInClub($dni);
        
        return response()->json([
            'data' => [
                'exists' => $result['exists'],
                'socio_data' => $result['socio_data']
            ]
        ]);
    }

    /**
     * Promover usuario local a API
     */
    public function promoteUser(int $userId): JsonResponse
    {
        $user = $this->userService->findById($userId);
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if ($user->user_type->value === 'api') {
            return response()->json([
                'message' => 'El usuario no es elegible para promoción',
                'errors' => [
                    'user' => ['Usuario ya es de tipo API']
                ]
            ], 422);
        }

        $promoted = $this->promotionService->promoteUser($user);
        
        if (!$promoted) {
            return response()->json([
                'message' => 'No se pudo promover el usuario',
                'errors' => [
                    'user' => ['Usuario no encontrado en la API del club']
                ]
            ], 422);
        }

        return response()->json([
            'data' => [
                'user' => UserDTO::fromModel($user->fresh())->toArray(),
                'promoted' => true,
                'message' => 'Usuario promovido exitosamente'
            ]
        ]);
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function getUserStats(): JsonResponse
    {
        $stats = $this->userService->getStats();
        
        $statsDTO = new StatsDTO(
            totalUsers: $stats['total_users'] ?? 0,
            localUsers: $stats['local_users'] ?? 0,
            apiUsers: $stats['api_users'] ?? 0,
            recentRegistrations: $stats['recent_registrations'] ?? 0,
            pendingPromotions: $stats['pending_promotions'] ?? 0,
            usersNeedingRefresh: $stats['users_needing_refresh'] ?? 0,
            cacheStats: $stats['cache_stats'] ?? []
        );

        return response()->json([
            'data' => $statsDTO->toArray()
        ]);
    }

    /**
     * Buscar usuarios
     */
    public function searchUsers(string $query): JsonResponse
    {
        $users = $this->userService->search($query);
        
        $userDTOs = $users->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'dni' => $user->dni,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type->value
            ];
        });

        return response()->json([
            'data' => $userDTOs,
            'meta' => [
                'total' => $users->total(),
                'query' => $query,
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'last_page' => $users->lastPage()
            ]
        ]);
    }
}
