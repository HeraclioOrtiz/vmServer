<?php

namespace App\Services\User;

use App\Models\User;
use App\Enums\UserType;
use App\Enums\PromotionStatus;
use App\Contracts\SociosApiInterface;
use App\Services\Core\CacheService;
use App\Services\External\SocioDataMappingService;
use App\Services\Core\AuditService;
use Illuminate\Support\Facades\Log;

class UserPromotionService
{
    public function __construct(
        private SociosApiInterface $sociosApi,
        private CacheService $cacheService,
        private SocioDataMappingService $mappingService,
        private AuditService $auditService
    ) {}

    /**
     * Verifica en la API y promueve el usuario si es elegible
     */
    public function checkApiAndPromoteIfEligible(User $user): bool
    {
        if (!$user->canPromote()) {
            return false;
        }

        try {
            $socio = $this->sociosApi->getSocioPorDni($user->dni);
            
            if ($socio) {
                $this->promoteToApi($user, $socio);
                return true;
            }
            
            Log::info('Usuario no encontrado en API durante verificación', ['dni' => $user->dni]);
            return false;
            
        } catch (\Exception $e) {
            Log::error('Error verificando usuario en API', [
                'dni' => $user->dni,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Promueve un usuario local a API con datos del socio
     */
    public function promoteToApi(User $user, array $socioData): User
    {
        if (!$user->canPromote()) {
            throw new \Exception('El usuario no puede ser promovido.');
        }

        $apiData = $this->mappingService->mapSocioToUserData($socioData, $user->dni, $user->password);
        
        // Preservar datos locales importantes
        $apiData['password'] = $user->password;
        $apiData['email'] = $user->email ?? $apiData['email'] ?? null;
        $apiData['phone'] = $user->phone ?? $apiData['telefono'] ?? null;

        $user->promoteToApi($apiData);
        
        // Actualizar cache
        $this->cacheService->putUser($user->fresh());
        
        // Log de auditoría
        $this->auditService->log(
            action: 'promote_user',
            resourceType: 'user',
            resourceId: $user->id,
            details: [
                'from_type' => 'local',
                'to_type' => 'api',
                'socio_id' => $apiData['socio_id'] ?? null,
            ],
            severity: 'medium',
            category: 'user_management'
        );

        $this->logSuccessfulPromotion($user);

        return $user->fresh();
    }

    /**
     * Marca un usuario para promoción pendiente
     */
    public function markForPromotion(User $user, array $adminData = []): User
    {
        if (!$user->canPromote()) {
            throw new \Exception('El usuario no puede ser marcado para promoción.');
        }

        $user->update([
            'promotion_status' => PromotionStatus::PENDING,
            'promoted_at' => now(),
            'admin_notes' => $adminData['notes'] ?? 'Marcado para promoción manual',
        ]);

        // Log de auditoría
        $this->auditService->log(
            action: 'mark_for_promotion',
            resourceType: 'user',
            resourceId: $user->id,
            details: $adminData,
            severity: 'medium',
            category: 'user_management'
        );

        $this->logPendingPromotion($user);

        return $user->fresh();
    }

    /**
     * Aprueba una promoción pendiente
     */
    public function approvePromotion(User $user, array $socioData): User
    {
        if ($user->promotion_status !== PromotionStatus::PENDING) {
            throw new \Exception('El usuario no tiene una promoción pendiente.');
        }

        return $this->promoteToApi($user, $socioData);
    }

    /**
     * Rechaza una promoción pendiente
     */
    public function rejectPromotion(User $user, string $reason = null): User
    {
        if ($user->promotion_status !== PromotionStatus::PENDING) {
            throw new \Exception('El usuario no tiene una promoción pendiente.');
        }

        $user->update([
            'promotion_status' => PromotionStatus::REJECTED,
            'admin_notes' => $reason ?? 'Promoción rechazada',
        ]);

        // Log de auditoría
        $this->auditService->log(
            action: 'reject_promotion',
            resourceType: 'user',
            resourceId: $user->id,
            details: ['reason' => $reason],
            severity: 'medium',
            category: 'user_management'
        );

        $this->logRejectedPromotion($user, $reason);

        return $user->fresh();
    }

    /**
     * Obtiene usuarios elegibles para promoción
     */
    public function getEligibleUsers(int $limit = 50): \Illuminate\Pagination\LengthAwarePaginator
    {
        return User::eligibleForPromotion()
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Obtiene usuarios con promoción pendiente
     */
    public function getPendingPromotions(int $limit = 50): \Illuminate\Pagination\LengthAwarePaginator
    {
        return User::where('promotion_status', PromotionStatus::PENDING)
            ->orderBy('promoted_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Obtiene estadísticas de promociones
     */
    public function getPromotionStats(): array
    {
        return [
            'eligible_users' => User::eligibleForPromotion()->count(),
            'pending_promotions' => User::where('promotion_status', PromotionStatus::PENDING)->count(),
            'approved_promotions' => User::where('promotion_status', PromotionStatus::APPROVED)->count(),
            'rejected_promotions' => User::where('promotion_status', PromotionStatus::REJECTED)->count(),
            'total_api_users' => User::where('user_type', UserType::API)->count(),
            'total_local_users' => User::where('user_type', UserType::LOCAL)->count(),
        ];
    }

    /**
     * Procesa promociones automáticas en lote
     */
    public function processAutomaticPromotions(int $limit = 20): array
    {
        $eligibleUsers = User::eligibleForPromotion()->limit($limit)->get();
        
        $results = [
            'processed' => 0,
            'promoted' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($eligibleUsers as $user) {
            $results['processed']++;
            
            try {
                if ($this->checkApiAndPromoteIfEligible($user)) {
                    $results['promoted']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error promoviendo usuario {$user->dni}: {$e->getMessage()}";
            }
        }

        Log::info('Procesamiento automático de promociones completado', $results);

        return $results;
    }

    /**
     * Log de promoción exitosa
     */
    private function logSuccessfulPromotion(User $user): void
    {
        Log::info('Usuario promovido exitosamente', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'from_type' => 'local',
            'to_type' => 'api',
            'socio_id' => $user->socio_id,
        ]);
    }

    /**
     * Log de promoción pendiente
     */
    private function logPendingPromotion(User $user): void
    {
        Log::info('Usuario marcado para promoción pendiente', [
            'user_id' => $user->id,
            'dni' => $user->dni,
        ]);
    }

    /**
     * Log de promoción rechazada
     */
    private function logRejectedPromotion(User $user, ?string $reason): void
    {
        Log::info('Promoción de usuario rechazada', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'reason' => $reason,
        ]);
    }
}
