<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\Admin\AuditLogService;
use App\Services\Core\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
        private AuditService $auditService
    ) {}

    /**
     * Lista de logs de auditoría con filtros avanzados
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'user_id', 'action', 'resource_type', 'resource_id',
            'severity', 'category', 'date_from', 'date_to', 
            'search', 'sort_by', 'sort_direction'
        ]);

        $perPage = min($request->get('per_page', 50), 200);
        $logs = $this->auditLogService->getFilteredLogs($filters, $perPage);

        return response()->json([
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ],
        ]);
    }

    /**
     * Mostrar un log específico con detalles completos
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $logDetails = $this->auditLogService->getLogWithDetails($auditLog);

        return response()->json($logDetails);
    }

    /**
     * Obtener estadísticas de auditoría
     */
    public function stats(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $stats = $this->auditService->getStats($days);

        // Estadísticas adicionales
        $additionalStats = [
            'recent_critical' => AuditLog::where('severity', 'critical')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'failed_logins_today' => AuditLog::where('action', 'login_failed')
                ->whereDate('created_at', today())
                ->count(),
            'admin_actions_today' => AuditLog::where('category', 'user_management')
                ->whereDate('created_at', today())
                ->count(),
            'system_changes_this_week' => AuditLog::where('category', 'system')
                ->where('created_at', '>=', now()->subWeek())
                ->count(),
        ];

        return response()->json([
            'period_days' => $days,
            'stats' => array_merge($stats, $additionalStats),
        ]);
    }

    /**
     * Obtener opciones de filtros disponibles
     */
    public function filterOptions(): JsonResponse
    {
        $options = $this->auditLogService->getFilterOptions();

        return response()->json($options);
    }

    /**
     * Exportar logs de auditoría
     */
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,json',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'filters' => 'nullable|array',
        ]);

        try {
            $exportData = $this->auditLogService->exportLogs($validated, $validated['format']);

            return response()->json([
                'message' => 'Export generated successfully.',
                'filename' => $exportData['filename'],
                'data' => $exportData['data'],
                'mime_type' => $exportData['mime_type'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating export: ' . $e->getMessage()
            ], 500);
        }
    }
}
