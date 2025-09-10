<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserType;
use App\Enums\PromotionStatus;
use App\Contracts\SociosApiInterface;
use App\Exceptions\SocioNotFoundException;
use App\Exceptions\ApiConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public function __construct(
        private SociosApiInterface $sociosApi,
        private CacheService $cache
    ) {}

    /**
     * Promociona un usuario local a usuario API
     */
    public function promoteUser(User $user, string $clubPassword): PromotionResult
    {
        // Verificar elegibilidad
        if (!$user->canPromote()) {
            throw new ValidationException('Usuario no elegible para promoción');
        }

        // Verificar circuit breaker
        if ($this->cache->isCircuitBreakerOpen()) {
            throw new ApiConnectionException('Servicio temporalmente no disponible');
        }

        try {
            // Verificar en API externa
            $socio = $this->sociosApi->getSocioPorDni($user->dni);
            
            if (!$socio) {
                throw new SocioNotFoundException('DNI no encontrado en sistema del club');
            }

            // Validar password del club (simulado - en realidad la API ya valida)
            // En un escenario real, podrías tener un endpoint específico para validar credenciales
            if (!$this->validateClubCredentials($user->dni, $clubPassword)) {
                throw ValidationException::withMessages([
                    'club_password' => ['Password del club incorrecto']
                ]);
            }

            // Mapear datos del socio
            $apiData = $this->mapSocioToPromotionData($socio);

            // Promocionar usuario
            $user->promoteToApi($apiData);

            // Limpiar cache
            $this->cache->forgetUser($user->dni);

            // Construir URL directa de la foto
            if (!empty($socio['Id'])) {
                $fotoUrl = $this->sociosApi->buildFotoUrl($socio['Id']);
                if ($fotoUrl) {
                    $user->update(['foto_url' => $fotoUrl]);
                }
            }

            Log::info('Usuario promocionado exitosamente', [
                'user_id' => $user->id,
                'dni' => $user->dni,
                'socio_id' => $apiData['socio_id'] ?? null
            ]);

            return new PromotionResult($user->fresh(), true, 'Usuario promocionado exitosamente');

        } catch (ApiConnectionException $e) {
            $this->handleApiFailure();
            throw $e;
        }
    }

    /**
     * Verifica la elegibilidad de un usuario para promoción
     */
    public function checkEligibility(User $user): EligibilityResult
    {
        if ($user->user_type !== UserType::LOCAL) {
            return new EligibilityResult(
                false, 
                'Solo los usuarios locales pueden ser promocionados'
            );
        }

        if ($user->promotion_status !== PromotionStatus::NONE) {
            return new EligibilityResult(
                false,
                match($user->promotion_status) {
                    PromotionStatus::PENDING => 'Promoción ya está pendiente',
                    PromotionStatus::APPROVED => 'Usuario ya fue promocionado',
                    PromotionStatus::REJECTED => 'Promoción fue rechazada previamente',
                }
            );
        }

        return new EligibilityResult(true, null);
    }

    /**
     * Verifica si un DNI existe en el sistema del club
     */
    public function checkDniInClub(string $dni): ClubCheckResult
    {
        if ($this->cache->isCircuitBreakerOpen()) {
            return new ClubCheckResult(false, null, 'Servicio temporalmente no disponible');
        }

        try {
            $socio = $this->sociosApi->getSocioPorDni($dni);
            
            if (!$socio) {
                return new ClubCheckResult(false, null, 'DNI no encontrado en sistema del club');
            }

            $socioInfo = [
                'nombre' => $socio['nombre'] ?? '',
                'apellido' => $socio['apellido'] ?? '',
                'categoria' => $socio['categoria'] ?? '',
                'estado' => $socio['estado'] ?? 'ACTIVO',
                'socio_id' => $socio['Id'] ?? $socio['socio_n'] ?? ''
            ];

            return new ClubCheckResult(true, $socioInfo, 'DNI encontrado en sistema del club');

        } catch (ApiConnectionException $e) {
            $this->handleApiFailure();
            return new ClubCheckResult(false, null, 'Error al consultar sistema del club');
        }
    }

    /**
     * Solicita promoción (para flujos que requieren aprobación manual)
     */
    public function requestPromotion(User $user, string $clubPassword, ?string $notes = null): PromotionResult
    {
        if (!$user->canPromote()) {
            throw new ValidationException('Usuario no elegible para promoción');
        }

        // Verificar que existe en el club
        $clubCheck = $this->checkDniInClub($user->dni);
        if (!$clubCheck->exists) {
            throw new SocioNotFoundException($clubCheck->message);
        }

        // Marcar como pendiente
        $user->update([
            'promotion_status' => PromotionStatus::PENDING,
            'promoted_at' => now()
        ]);

        // Limpiar cache
        $this->cache->forgetUser($user->dni);

        Log::info('Promoción solicitada', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'notes' => $notes
        ]);

        return new PromotionResult($user->fresh(), false, 'Solicitud de promoción enviada');
    }

    /**
     * Aprueba una promoción pendiente (para administradores)
     */
    public function approvePromotion(User $user): PromotionResult
    {
        if ($user->promotion_status !== PromotionStatus::PENDING) {
            throw new ValidationException('No hay promoción pendiente para este usuario');
        }

        try {
            // Obtener datos actualizados del club
            $socio = $this->sociosApi->getSocioPorDni($user->dni);
            
            if (!$socio) {
                throw new SocioNotFoundException('DNI no encontrado en sistema del club');
            }

            // Mapear datos y promocionar
            $apiData = $this->mapSocioToPromotionData($socio);
            $user->promoteToApi($apiData);

            // Limpiar cache
            $this->cache->forgetUser($user->dni);

            // Construir URL directa de la foto
            if (!empty($socio['Id'])) {
                $fotoUrl = $this->sociosApi->buildFotoUrl($socio['Id']);
                if ($fotoUrl) {
                    $user->update(['foto_url' => $fotoUrl]);
                }
            }

            Log::info('Promoción aprobada por administrador', [
                'user_id' => $user->id,
                'dni' => $user->dni
            ]);

            return new PromotionResult($user->fresh(), true, 'Promoción aprobada exitosamente');

        } catch (ApiConnectionException $e) {
            $this->handleApiFailure();
            throw $e;
        }
    }

    /**
     * Rechaza una promoción pendiente
     */
    public function rejectPromotion(User $user, ?string $reason = null): PromotionResult
    {
        if ($user->promotion_status !== PromotionStatus::PENDING) {
            throw new ValidationException('No hay promoción pendiente para este usuario');
        }

        $user->update([
            'promotion_status' => PromotionStatus::REJECTED
        ]);

        // Limpiar cache
        $this->cache->forgetUser($user->dni);

        Log::info('Promoción rechazada', [
            'user_id' => $user->id,
            'dni' => $user->dni,
            'reason' => $reason
        ]);

        return new PromotionResult($user->fresh(), false, 'Promoción rechazada');
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
            'recent_promotions' => User::where('promotion_status', PromotionStatus::APPROVED)
                                      ->where('promoted_at', '>=', now()->subDays(30))
                                      ->count(),
        ];
    }

    /**
     * Valida credenciales del club (simulado)
     */
    private function validateClubCredentials(string $dni, string $password): bool
    {
        // En un escenario real, esto podría ser una llamada a un endpoint específico
        // Por ahora, simulamos que siempre es válido si el usuario existe en la API
        try {
            $socio = $this->sociosApi->getSocioPorDni($dni);
            return $socio !== null;
        } catch (ApiConnectionException $e) {
            return false;
        }
    }

    /**
     * Mapea datos del socio para promoción
     */
    private function mapSocioToPromotionData(array $socio): array
    {
        return [
            'name' => trim(($socio['apellido'] ?? '') . ', ' . ($socio['nombre'] ?? '')),
            'nombre' => $socio['nombre'] ?? '',
            'apellido' => $socio['apellido'] ?? '',
            'nacionalidad' => $socio['nacionalidad'] ?? null,
            'nacimiento' => $this->parseDate($socio['nacimiento'] ?? null),
            'domicilio' => $socio['domicilio'] ?? null,
            'localidad' => $socio['localidad'] ?? null,
            'telefono' => $socio['telefono'] ?? null,
            'celular' => $socio['celular'] ?? null,
            'categoria' => $socio['categoria'] ?? null,
            'socio_id' => (string)($socio['Id'] ?? $socio['socio_n'] ?? ''),
            'barcode' => (string)($socio['Id'] ?? $socio['socio_n'] ?? ''),
            'estado_socio' => $socio['estado'] ?? 'ACTIVO',
            'api_updated_at' => now(),
        ];
    }

    /**
     * Construye URL directa de la foto del socio
     * Ya no descargamos ni almacenamos localmente
     */
    private function buildAndSetFotoUrl(User $user, string $socioId): void
    {
        if (empty($socioId)) {
            return;
        }
        
        $fotoUrl = $this->sociosApi->buildFotoUrl($socioId);
        
        if ($fotoUrl) {
            $user->update(['foto_url' => $fotoUrl]);
            
            Log::info('✅ URL de foto construida en promoción', [
                'socio_id' => $socioId,
                'user_id' => $user->id,
                'dni' => $user->dni,
                'foto_url' => $fotoUrl
            ]);
        } else {
            Log::warning('❌ No se pudo construir URL de foto en promoción', [
                'socio_id' => $socioId,
                'user_id' => $user->id,
                'dni' => $user->dni
            ]);
        }
    }

    /**
     * Maneja fallos de la API
     */
    private function handleApiFailure(): void
    {
        $failures = $this->cache->incrementApiFailures();
        
        if ($failures >= 5) {
            $this->cache->openCircuitBreaker();
            Log::warning('Circuit breaker abierto por fallos en promoción', ['failures' => $failures]);
        }
    }

    /**
     * Parsea una fecha desde string
     */
    private function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }
        
        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning('Error al parsear fecha en promoción', ['date' => $dateString]);
            return null;
        }
    }
}

/**
 * Resultado de promoción
 */
class PromotionResult
{
    public function __construct(
        public User $user,
        public bool $success,
        public string $message
    ) {}
}

/**
 * Resultado de elegibilidad
 */
class EligibilityResult
{
    public function __construct(
        public bool $eligible,
        public ?string $reason
    ) {}
}

/**
 * Resultado de verificación en club
 */
class ClubCheckResult
{
    public function __construct(
        public bool $exists,
        public ?array $socioInfo,
        public string $message
    ) {}
}
