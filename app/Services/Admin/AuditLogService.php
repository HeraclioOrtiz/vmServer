<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Services\Core\AuditService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Obtiene logs con filtros aplicados
     */
    public function getFilteredLogs(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        $query = AuditLog::with('user:id,name,dni');

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        $logs = $query->paginate($perPage);

        // Transformar datos para la respuesta
        $logs->getCollection()->transform(function ($log) {
            return $this->transformLogForList($log);
        });

        return $logs;
    }

    /**
     * Aplica filtros a la consulta de logs
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['resource_type'])) {
            $query->where('resource_type', $filters['resource_type']);
        }

        if (!empty($filters['resource_id'])) {
            $query->where('resource_id', $filters['resource_id']);
        }

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        // Filtros de fecha
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        // Búsqueda de texto
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
    }

    /**
     * Aplica ordenamiento a la consulta
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        $allowedSorts = ['created_at', 'action', 'severity', 'user_id'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    /**
     * Transforma un log para la lista
     */
    private function transformLogForList(AuditLog $log): array
    {
        return [
            'id' => $log->id,
            'user' => [
                'id' => $log->user?->id,
                'name' => $log->getUserName(),
                'dni' => $log->user?->dni,
            ],
            'action' => $log->action,
            'action_description' => $log->getActionDescription(),
            'action_icon' => $log->getActionIcon(),
            'resource_type' => $log->resource_type,
            'resource_id' => $log->resource_id,
            'severity' => $log->severity,
            'severity_color' => $log->getSeverityColor(),
            'category' => $log->category,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'created_at' => $log->created_at,
            'details' => $log->details,
            'has_changes' => !empty($log->old_values) || !empty($log->new_values),
        ];
    }

    /**
     * Obtiene un log con detalles completos
     */
    public function getLogWithDetails(AuditLog $auditLog): array
    {
        $auditLog->load('user:id,name,dni,email');

        return [
            'log' => [
                'id' => $auditLog->id,
                'user' => [
                    'id' => $auditLog->user?->id,
                    'name' => $auditLog->getUserName(),
                    'dni' => $auditLog->user?->dni,
                    'email' => $auditLog->user?->email,
                ],
                'action' => $auditLog->action,
                'action_description' => $auditLog->getActionDescription(),
                'resource_type' => $auditLog->resource_type,
                'resource_id' => $auditLog->resource_id,
                'severity' => $auditLog->severity,
                'category' => $auditLog->category,
                'ip_address' => $auditLog->ip_address,
                'user_agent' => $auditLog->user_agent,
                'created_at' => $auditLog->created_at,
                'details' => $auditLog->details,
                'old_values' => $auditLog->old_values,
                'new_values' => $auditLog->new_values,
                'changes' => $this->formatChanges($auditLog->old_values, $auditLog->new_values),
            ]
        ];
    }

    /**
     * Obtiene opciones de filtros disponibles
     */
    public function getFilterOptions(): array
    {
        $actions = AuditLog::distinct('action')
            ->orderBy('action')
            ->pluck('action')
            ->map(function ($action) {
                return [
                    'value' => $action,
                    'label' => ucfirst(str_replace('_', ' ', $action)),
                ];
            });

        $resourceTypes = AuditLog::distinct('resource_type')
            ->orderBy('resource_type')
            ->pluck('resource_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => ucfirst(str_replace('_', ' ', $type)),
                ];
            });

        $severities = [
            ['value' => 'low', 'label' => 'Baja', 'color' => 'green'],
            ['value' => 'medium', 'label' => 'Media', 'color' => 'yellow'],
            ['value' => 'high', 'label' => 'Alta', 'color' => 'orange'],
            ['value' => 'critical', 'label' => 'Crítica', 'color' => 'red'],
        ];

        $categories = [
            ['value' => 'auth', 'label' => 'Autenticación'],
            ['value' => 'user_management', 'label' => 'Gestión de usuarios'],
            ['value' => 'gym', 'label' => 'Gimnasio'],
            ['value' => 'system', 'label' => 'Sistema'],
            ['value' => 'general', 'label' => 'General'],
        ];

        return [
            'actions' => $actions,
            'resource_types' => $resourceTypes,
            'severities' => $severities,
            'categories' => $categories,
        ];
    }

    /**
     * Exporta logs en el formato especificado
     */
    public function exportLogs(array $filters, string $format): array
    {
        $query = AuditLog::with('user:id,name,dni');

        // Aplicar filtros
        if (!empty($filters['filters'])) {
            foreach ($filters['filters'] as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, $value);
                }
            }
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')->get();
        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.' . $format;

        if ($format === 'csv') {
            $data = $this->generateCsv($logs);
            $mimeType = 'text/csv';
        } else {
            $data = $this->generateJson($logs);
            $mimeType = 'application/json';
        }

        return [
            'filename' => $filename,
            'data' => base64_encode($data),
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Formatea cambios para mostrar diferencias
     */
    private function formatChanges(?array $oldValues, ?array $newValues): array
    {
        if (!$oldValues && !$newValues) {
            return [];
        }

        $changes = [];
        $allKeys = array_unique(array_merge(
            array_keys($oldValues ?? []),
            array_keys($newValues ?? [])
        ));

        foreach ($allKeys as $key) {
            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $key,
                    'field_label' => ucfirst(str_replace('_', ' ', $key)),
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'change_type' => $this->getChangeType($oldValue, $newValue),
                ];
            }
        }

        return $changes;
    }

    /**
     * Determina el tipo de cambio
     */
    private function getChangeType($oldValue, $newValue): string
    {
        if ($oldValue === null) {
            return 'added';
        } elseif ($newValue === null) {
            return 'removed';
        } else {
            return 'modified';
        }
    }

    /**
     * Genera CSV de los logs
     */
    private function generateCsv($logs): string
    {
        $csv = "ID,Usuario,DNI,Acción,Tipo de Recurso,ID Recurso,Severidad,Categoría,IP,Fecha\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $log->id,
                '"' . str_replace('"', '""', $log->getUserName()) . '"',
                $log->user?->dni ?? '',
                $log->action,
                $log->resource_type,
                $log->resource_id ?? '',
                $log->severity,
                $log->category,
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s')
            );
        }

        return $csv;
    }

    /**
     * Genera JSON de los logs
     */
    private function generateJson($logs): string
    {
        $jsonData = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'user_name' => $log->getUserName(),
                'user_dni' => $log->user?->dni,
                'action' => $log->action,
                'resource_type' => $log->resource_type,
                'resource_id' => $log->resource_id,
                'severity' => $log->severity,
                'category' => $log->category,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->toISOString(),
                'details' => $log->details,
            ];
        });

        return json_encode($jsonData, JSON_PRETTY_PRINT);
    }
}
