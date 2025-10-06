<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'details',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'severity',
        'category',
    ];

    protected $casts = [
        'details' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relación con el usuario que realizó la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para filtrar por tipo de recurso
     */
    public function scopeResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope para filtrar por severidad
     */
    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope para búsqueda de texto en detalles
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('action', 'like', "%{$search}%")
              ->orWhere('resource_type', 'like', "%{$search}%")
              ->orWhereJsonContains('details', $search)
              ->orWhereHas('user', function ($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('dni', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Obtiene el nombre del usuario que realizó la acción
     */
    public function getUserName(): string
    {
        return $this->user ? $this->user->display_name : 'Usuario eliminado';
    }

    /**
     * Obtiene una descripción legible de la acción
     */
    public function getActionDescription(): string
    {
        return match($this->action) {
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'create' => 'Creación',
            'update' => 'Actualización',
            'delete' => 'Eliminación',
            'assign_role' => 'Asignación de rol',
            'remove_role' => 'Remoción de rol',
            'suspend' => 'Suspensión',
            'activate' => 'Activación',
            'assign_professor' => 'Asignación de profesor',
            'create_template' => 'Creación de plantilla',
            'create_assignment' => 'Creación de asignación',
            default => ucfirst($this->action),
        };
    }

    /**
     * Obtiene el color de la severidad para UI
     */
    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtiene el icono de la acción para UI
     */
    public function getActionIcon(): string
    {
        return match($this->action) {
            'login' => 'login',
            'logout' => 'logout',
            'create' => 'plus',
            'update' => 'edit',
            'delete' => 'trash',
            'assign_role' => 'user-plus',
            'remove_role' => 'user-minus',
            'suspend' => 'ban',
            'activate' => 'check',
            default => 'activity',
        };
    }
}
