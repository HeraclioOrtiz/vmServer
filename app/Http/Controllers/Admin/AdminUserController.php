<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function __construct(
        private UserManagementService $userManagementService
    ) {}

    /**
     * Lista de usuarios con filtros avanzados
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'user_type', 'is_professor', 'is_admin', 
            'estado_socio', 'semaforo', 'account_status',
            'date_from', 'date_to', 'has_gym_access',
            'sort_by', 'sort_direction'
        ]);

        $perPage = min($request->get('per_page', 20), 100);
        $users = $this->userManagementService->getFilteredUsers($filters, $perPage);
        $filtersSummary = $this->userManagementService->getFiltersSummary();

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'filters_summary' => $filtersSummary,
        ]);
    }

    /**
     * Mostrar un usuario específico con información completa
     */
    public function show(User $user): JsonResponse
    {
        $userData = [
            'user' => [
                'basic_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'display_name' => $user->display_name,
                    'dni' => $user->dni,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'celular' => $user->celular,
                    'avatar_url' => $user->avatar_url,
                    'user_type' => $user->user_type,
                    'type_label' => $user->type_label,
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login ?? null,
                ],
                'club_info' => $user->user_type === 'api' ? [
                    'socio_id' => $user->socio_id,
                    'socio_n' => $user->socio_n,
                    'categoria' => $user->categoria,
                    'estado_socio' => $user->estado_socio,
                    'semaforo' => $user->semaforo,
                    'saldo' => $user->saldo,
                    'barcode' => $user->barcode,
                    'api_updated_at' => $user->api_updated_at,
                    'domicilio' => $user->domicilio,
                    'localidad' => $user->localidad,
                    'nacimiento' => $user->nacimiento,
                ] : null,
                'system_roles' => [
                    'is_professor' => $user->is_professor,
                    'professor_since' => $user->professor_since,
                    'is_admin' => $user->is_admin,
                    'permissions' => $user->permissions ?? [],
                    'account_status' => $user->account_status,
                    'session_timeout' => $user->session_timeout,
                ],
                'gym_activity' => $user->is_professor ? $user->getProfessorStats() : null,
                'activity_log' => $user->getRecentActivity(20),
                'admin_notes' => $user->admin_notes,
            ]
        ];

        return response()->json($userData);
    }

    /**
     * Crear un nuevo usuario
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'dni' => 'required|string|unique:users,dni',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'user_type' => ['required', Rule::in(['local', 'api'])],
            'is_professor' => 'boolean',
            'is_admin' => 'boolean',
            'permissions' => 'nullable|array',
            'account_status' => ['nullable', Rule::in(['active', 'suspended', 'pending'])],
            'admin_notes' => 'nullable|string',
        ]);

        try {
            $user = $this->userManagementService->createUser($validated, $request->user());

            return response()->json([
                'message' => 'Usuario creado exitosamente.',
                'user' => $user->fresh(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Actualizar un usuario
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'is_professor' => 'sometimes|boolean',
            'is_admin' => 'sometimes|boolean',
            'permissions' => 'nullable|array',
            'account_status' => ['sometimes', Rule::in(['active', 'suspended', 'pending'])],
            'session_timeout' => 'sometimes|integer|min:30|max:1440',
            'admin_notes' => 'nullable|string',
        ]);

        try {
            $user = $this->userManagementService->updateUser($user, $validated, $request->user());

            return response()->json([
                'message' => 'Usuario actualizado exitosamente.',
                'user' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Eliminar un usuario (suspender)
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $this->userManagementService->suspendUser($user, $request->user(), 'Cuenta eliminada por administrador');

            return response()->json([
                'message' => 'Usuario suspendido exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Asignar rol de administrador
     */
    public function assignAdminRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:user_management,gym_admin,system_settings,reports_access,audit_logs,super_admin',
        ]);

        try {
            $user = $this->userManagementService->assignAdminRole(
                $user, 
                $validated['permissions'] ?? [], 
                $request->user()
            );

            return response()->json([
                'message' => 'Rol de administrador asignado exitosamente.',
                'user' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Remover rol de administrador
     */
    public function removeAdminRole(Request $request, User $user): JsonResponse
    {
        try {
            $user = $this->userManagementService->removeAdminRole($user, $request->user());

            return response()->json([
                'message' => 'Rol de administrador removido exitosamente.',
                'user' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Suspender usuario
     */
    public function suspend(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $user = $this->userManagementService->suspendUser(
                $user, 
                $request->user(), 
                $validated['reason'] ?? null
            );

            return response()->json([
                'message' => 'Usuario suspendido exitosamente.',
                'user' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Activar usuario
     */
    public function activate(User $user): JsonResponse
    {
        $user = $this->userManagementService->activateUser($user);

        return response()->json([
            'message' => 'Usuario activado exitosamente.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function stats(): JsonResponse
    {
        $stats = $this->userManagementService->getUserStats();

        return response()->json($stats);
    }
}
