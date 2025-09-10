<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Lista todos los usuarios con filtros y paginación
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'type', 'promotion_status', 'search', 'active', 
            'sort_by', 'sort_direction', 'per_page'
        ]);

        $users = $this->userService->getAllUsers($filters);

        return UserResource::collection($users);
    }

    /**
     * Crea un nuevo usuario local
     */
    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->createLocalUser($request->validated());

        return UserResource::make($user)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Muestra un usuario específico
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Actualiza un usuario existente
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated());

        return UserResource::make($updatedUser);
    }

    /**
     * Elimina un usuario (soft delete)
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Busca usuarios por texto
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:50',
            'limit' => 'integer|min:1|max:20'
        ]);

        $results = $this->userService->searchUsers(
            $request->query,
            $request->limit ?? 10
        );

        return response()->json([
            'results' => UserResource::collection(collect($results))
        ]);
    }

    /**
     * Obtiene estadísticas de usuarios
     */
    public function stats()
    {
        $stats = $this->userService->getUserStats();

        return response()->json($stats);
    }

    /**
     * Obtiene usuarios que necesitan refresh desde la API
     */
    public function needingRefresh(Request $request)
    {
        $hours = $request->integer('hours', 24);
        $users = $this->userService->getUsersNeedingRefresh($hours);

        return response()->json([
            'users' => UserResource::collection(collect($users)),
            'count' => count($users),
            'hours_threshold' => $hours
        ]);
    }

    /**
     * Cambia el tipo de usuario (solo administradores)
     */
    public function changeType(Request $request, User $user)
    {
        $request->validate([
            'type' => 'required|in:local,api'
        ]);

        $newType = \App\Enums\UserType::from($request->type);
        $updatedUser = $this->userService->changeUserType($user, $newType);

        return UserResource::make($updatedUser);
    }

    /**
     * Limpia el cache de un usuario
     */
    public function clearCache(User $user)
    {
        $this->userService->clearUserCache($user->dni);

        return response()->json([
            'message' => 'Cache del usuario limpiado exitosamente'
        ]);
    }

    /**
     * Limpia el cache de todos los usuarios
     */
    public function clearAllCache()
    {
        $this->userService->clearAllUsersCache();

        return response()->json([
            'message' => 'Cache de todos los usuarios limpiado exitosamente'
        ]);
    }

    /**
     * Restaura un usuario eliminado
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        
        return response()->json([
            'message' => 'Usuario restaurado exitosamente',
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Elimina un usuario por DNI y limpia cache
     */
    public function deleteByDni(string $dni): JsonResponse
    {
        $user = User::where('dni', $dni)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }
        
        // Eliminar tokens del usuario
        $user->tokens()->delete();
        
        // Limpiar cache
        $this->userService->clearUserCache($dni);
        
        // Eliminar usuario
        $user->delete();
        
        return response()->json([
            'message' => 'Usuario eliminado exitosamente',
            'dni' => $dni,
            'cache_cleared' => true
        ]);
    }
}
