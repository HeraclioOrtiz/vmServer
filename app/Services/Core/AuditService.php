<?php

namespace App\Services\Core;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Registra una acción en el log de auditoría
     */
    public function log(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?array $details = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $severity = 'low',
        string $category = 'general'
    ): AuditLog {
        $request = request();
        
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $details,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => $severity,
            'category' => $category,
        ]);
    }

    /**
     * Registra un login
     */
    public function logLogin(int $userId, bool $successful = true): AuditLog
    {
        return $this->log(
            action: $successful ? 'login' : 'login_failed',
            resourceType: 'user',
            resourceId: $userId,
            details: [
                'successful' => $successful,
                'timestamp' => now()->toISOString(),
            ],
            severity: $successful ? 'low' : 'medium',
            category: 'auth'
        );
    }

    /**
     * Registra un logout
     */
    public function logLogout(int $userId): AuditLog
    {
        return $this->log(
            action: 'logout',
            resourceType: 'user',
            resourceId: $userId,
            category: 'auth'
        );
    }

    /**
     * Registra la creación de un recurso
     */
    public function logCreate(string $resourceType, int $resourceId, array $data = []): AuditLog
    {
        return $this->log(
            action: 'create',
            resourceType: $resourceType,
            resourceId: $resourceId,
            newValues: $data,
            severity: 'medium',
            category: $this->getCategoryByResourceType($resourceType)
        );
    }

    /**
     * Registra la actualización de un recurso
     */
    public function logUpdate(string $resourceType, int $resourceId, array $oldValues, array $newValues): AuditLog
    {
        return $this->log(
            action: 'update',
            resourceType: $resourceType,
            resourceId: $resourceId,
            oldValues: $oldValues,
            newValues: $newValues,
            severity: 'medium',
            category: $this->getCategoryByResourceType($resourceType)
        );
    }

    /**
     * Registra la eliminación de un recurso
     */
    public function logDelete(string $resourceType, int $resourceId, array $data = []): AuditLog
    {
        return $this->log(
            action: 'delete',
            resourceType: $resourceType,
            resourceId: $resourceId,
            oldValues: $data,
            severity: 'high',
            category: $this->getCategoryByResourceType($resourceType)
        );
    }

    /**
     * Registra la asignación de un rol
     */
    public function logRoleAssignment(int $userId, string $role, array $permissions = []): AuditLog
    {
        return $this->log(
            action: 'assign_role',
            resourceType: 'user',
            resourceId: $userId,
            details: [
                'role' => $role,
                'permissions' => $permissions,
            ],
            severity: 'high',
            category: 'user_management'
        );
    }

    /**
     * Registra la remoción de un rol
     */
    public function logRoleRemoval(int $userId, string $role): AuditLog
    {
        return $this->log(
            action: 'remove_role',
            resourceType: 'user',
            resourceId: $userId,
            details: [
                'role' => $role,
            ],
            severity: 'high',
            category: 'user_management'
        );
    }

    /**
     * Registra la suspensión de un usuario
     */
    public function logUserSuspension(int $userId, string $reason = null): AuditLog
    {
        return $this->log(
            action: 'suspend',
            resourceType: 'user',
            resourceId: $userId,
            details: [
                'reason' => $reason,
            ],
            severity: 'critical',
            category: 'user_management'
        );
    }

    /**
     * Registra la activación de un usuario
     */
    public function logUserActivation(int $userId): AuditLog
    {
        return $this->log(
            action: 'activate',
            resourceType: 'user',
            resourceId: $userId,
            severity: 'medium',
            category: 'user_management'
        );
    }

    /**
     * Registra la asignación de profesor
     */
    public function logProfessorAssignment(int $userId, array $qualifications = []): AuditLog
    {
        return $this->log(
            action: 'assign_professor',
            resourceType: 'user',
            resourceId: $userId,
            details: $qualifications,
            severity: 'high',
            category: 'user_management'
        );
    }

    /**
     * Registra acciones del gimnasio
     */
    public function logGymAction(string $action, string $resourceType, int $resourceId, array $details = []): AuditLog
    {
        return $this->log(
            action: $action,
            resourceType: $resourceType,
            resourceId: $resourceId,
            details: $details,
            severity: 'medium',
            category: 'gym'
        );
    }

    /**
     * Registra cambios en la configuración del sistema
     */
    public function logSystemConfigChange(string $section, array $oldValues, array $newValues): AuditLog
    {
        return $this->log(
            action: 'update_config',
            resourceType: 'system_config',
            details: [
                'section' => $section,
            ],
            oldValues: $oldValues,
            newValues: $newValues,
            severity: 'critical',
            category: 'system'
        );
    }

    /**
     * Obtiene la categoría basada en el tipo de recurso
     */
    private function getCategoryByResourceType(string $resourceType): string
    {
        return match($resourceType) {
            'user' => 'user_management',
            'exercise', 'daily_template', 'weekly_template', 'weekly_assignment' => 'gym',
            'system_config' => 'system',
            default => 'general',
        };
    }

    /**
     * Obtiene estadísticas de auditoría
     */
    public function getStats(int $days = 30): array
    {
        $from = now()->subDays($days);
        
        return [
            'total_actions' => AuditLog::where('created_at', '>=', $from)->count(),
            'by_severity' => AuditLog::where('created_at', '>=', $from)
                ->selectRaw('severity, count(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray(),
            'by_category' => AuditLog::where('created_at', '>=', $from)
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'top_users' => AuditLog::where('created_at', '>=', $from)
                ->with('user:id,name,dni')
                ->selectRaw('user_id, count(*) as count')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'user' => $log->user ? $log->user->display_name : 'Usuario eliminado',
                        'count' => $log->count,
                    ];
                })
                ->toArray(),
        ];
    }
}
