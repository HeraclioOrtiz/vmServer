<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserType;
use App\Enums\PromotionStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private CacheService $cache
    ) {}

    /**
     * Obtiene todos los usuarios con filtros y paginación
     */
    public function getAllUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::query();
        
        // Filtro por tipo de usuario
        if ($type = $filters['type'] ?? null) {
            if (in_array($type, ['local', 'api'])) {
                $query->where('user_type', $type);
            }
        }
        
        // Filtro por estado de promoción
        if ($promotionStatus = $filters['promotion_status'] ?? null) {
            if (in_array($promotionStatus, ['none', 'pending', 'approved', 'rejected'])) {
                $query->where('promotion_status', $promotionStatus);
            }
        }
        
        // Búsqueda por texto
        if ($search = $filters['search'] ?? null) {
            $search = trim($search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('socio_id', 'like', "%{$search}%");
            });
        }
        
        // Filtro por estado activo
        if (isset($filters['active'])) {
            // Asumiendo que tenemos un campo is_active o similar
            // Por ahora filtramos por usuarios no eliminados
            $query->whereNull('deleted_at');
        }
        
        // Ordenamiento
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        $allowedSorts = ['created_at', 'name', 'dni', 'user_type', 'promoted_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $perPage = min($filters['per_page'] ?? 15, 50); // Máximo 50 por página
        
        return $query->paginate($perPage);
    }

    /**
     * Crea un nuevo usuario local
     */
    public function createLocalUser(array $data): User
    {
        // Verificar que el DNI no exista
        if (User::where('dni', $data['dni'])->exists()) {
            throw ValidationException::withMessages([
                'dni' => ['El DNI ya está registrado.']
            ]);
        }
        
        // Verificar que el email no exista (solo para usuarios locales)
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['El email ya está registrado.']
            ]);
        }
        
        $userData = [
            'dni' => $data['dni'],
            'user_type' => UserType::LOCAL,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'promotion_status' => PromotionStatus::NONE,
        ];
        
        $user = User::create($userData);
        
        // Cache el nuevo usuario
        $this->cache->putUser($user);
        
        Log::info('Usuario local creado por admin', [
            'dni' => $user->dni, 
            'email' => $user->email,
            'created_by' => 'admin'
        ]);
        
        return $user;
    }

    /**
     * Actualiza un usuario existente
     */
    public function updateUser(User $user, array $data): User
    {
        // Obtener campos editables según el tipo de usuario
        $editableFields = $user->getEditableFields();
        
        // Filtrar solo los campos permitidos
        $allowedData = array_intersect_key($data, array_flip($editableFields));
        
        // Validaciones específicas
        if (isset($allowedData['email']) && $allowedData['email'] !== $user->email) {
            if (User::where('email', $allowedData['email'])->where('id', '!=', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['El email ya está en uso.']
                ]);
            }
        }
        
        // Hash password si se está actualizando
        if (isset($allowedData['password'])) {
            $allowedData['password'] = Hash::make($allowedData['password']);
        }
        
        // Actualizar usuario
        $user->update($allowedData);
        
        // Limpiar cache
        $this->cache->forgetUser($user->dni);
        
        Log::info('Usuario actualizado', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'fields_updated' => array_keys($allowedData)
        ]);
        
        return $user->fresh();
    }

    /**
     * Elimina un usuario (soft delete)
     */
    public function deleteUser(User $user): bool
    {
        // Solo permitir eliminar usuarios locales o en casos específicos
        if ($user->user_type === UserType::API && $user->socio_id) {
            throw ValidationException::withMessages([
                'user' => ['No se puede eliminar un usuario API con datos del club.']
            ]);
        }
        
        $dni = $user->dni;
        $deleted = $user->delete();
        
        if ($deleted) {
            // Limpiar cache
            $this->cache->forgetUser($dni);
            
            Log::info('Usuario eliminado', [
                'user_id' => $user->id,
                'dni' => $dni,
                'type' => $user->user_type->value
            ]);
        }
        
        return $deleted;
    }

    /**
     * Restaura un usuario eliminado
     */
    public function restoreUser(User $user): bool
    {
        $restored = $user->restore();
        
        if ($restored) {
            // Limpiar cache para forzar refresh
            $this->cache->forgetUser($user->dni);
            
            Log::info('Usuario restaurado', [
                'user_id' => $user->id,
                'dni' => $user->dni
            ]);
        }
        
        return $restored;
    }

    /**
     * Obtiene estadísticas de usuarios
     */
    public function getUserStats(): array
    {
        return [
            'total_users' => User::count(),
            'local_users' => User::local()->count(),
            'api_users' => User::api()->count(),
            'users_eligible_for_promotion' => User::eligibleForPromotion()->count(),
            'promoted_users' => User::where('promotion_status', PromotionStatus::APPROVED)->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'users_needing_refresh' => User::needsRefresh()->count(),
        ];
    }

    /**
     * Busca usuarios por diferentes criterios
     */
    public function searchUsers(string $query, int $limit = 10): array
    {
        $query = trim($query);
        
        if (empty($query)) {
            return [];
        }
        
        return User::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('dni', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('nombre', 'like', "%{$query}%")
                  ->orWhere('apellido', 'like', "%{$query}%")
                  ->orWhere('socio_id', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Obtiene usuarios que necesitan refresh desde la API
     */
    public function getUsersNeedingRefresh(int $hours = 24): array
    {
        return User::needsRefresh($hours)->get()->toArray();
    }

    /**
     * Cambia el tipo de usuario (solo para casos administrativos)
     */
    public function changeUserType(User $user, UserType $newType): User
    {
        if ($user->user_type === $newType) {
            throw ValidationException::withMessages([
                'type' => ['El usuario ya es de este tipo.']
            ]);
        }
        
        // Validaciones específicas para el cambio
        if ($newType === UserType::LOCAL && $user->socio_id) {
            throw ValidationException::withMessages([
                'type' => ['No se puede convertir a local un usuario con datos del club.']
            ]);
        }
        
        $oldType = $user->user_type;
        
        $user->update([
            'user_type' => $newType,
            'promotion_status' => $newType === UserType::LOCAL 
                ? PromotionStatus::NONE 
                : PromotionStatus::APPROVED
        ]);
        
        // Limpiar cache
        $this->cache->forgetUser($user->dni);
        
        Log::warning('Tipo de usuario cambiado administrativamente', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'old_type' => $oldType->value,
            'new_type' => $newType->value
        ]);
        
        return $user->fresh();
    }

    /**
     * Limpia el cache de un usuario específico
     */
    public function clearUserCache(string $dni): void
    {
        $this->cache->forgetUser($dni);
        
        Log::debug('Cache de usuario limpiado', ['dni' => $dni]);
    }

    /**
     * Limpia el cache de todos los usuarios
     */
    public function clearAllUsersCache(): void
    {
        $this->cache->clearAllUserCache();
        
        Log::info('Cache de todos los usuarios limpiado');
    }
}
