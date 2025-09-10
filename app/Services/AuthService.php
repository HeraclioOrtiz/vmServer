<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserType;
use App\Enums\PromotionStatus;
use App\Contracts\SociosApiInterface;
use App\Exceptions\SocioNotFoundException;
use App\Exceptions\ApiConnectionException;
use App\DTOs\AuthResult;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthService
{
    public function __construct(
        private SociosApiInterface $sociosApi,
        private CacheService $cache
    ) {}

    /**
     * Autentica un usuario por DNI y password (arquitectura dual)
     */
    public function login(string $dni, string $password): AuthResult
    {
        // 1. Cache lookup primero
        $cached = $this->cache->getUser($dni);
        if ($cached) {
            return $this->validateCachedUser($cached, $password);
        }
        
        // 2. Database lookup - OBLIGATORIO: usuario debe estar registrado
        $user = User::where('dni', $dni)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'dni' => ['Usuario no registrado. Debe registrarse primero.']
            ]);
        }
        
        $this->validatePassword($user, $password);
        
        // Auto-refresh para usuarios API si es necesario
        $refreshed = false;
        if ($user->user_type === UserType::API && $user->needsRefresh()) {
            $refreshed = $this->refreshFromApi($user);
        }
        
        return new AuthResult($user, false, $refreshed);
    }

    /**
     * Alias para login - mantiene compatibilidad con AuthController
     */
    public function authenticate(string $dni, string $password): AuthResult
    {
        return $this->login($dni, $password);
    }

    /**
     * Registra un nuevo usuario con validación automática de API
     */
    public function registerLocal(array $data): User
    {
        // Verificar que el DNI no exista
        if (User::where('dni', $data['dni'])->exists()) {
            throw ValidationException::withMessages([
                'dni' => ['El DNI ya está registrado.']
            ]);
        }
        
        // Crear usuario inicialmente como LOCAL
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
        
        // NUEVO: Validación automática con API de terceros durante registro
        $this->checkApiAndPromoteIfEligible($user);
        
        // Cache el usuario (ya actualizado si fue promovido)
        $this->cache->putUser($user->fresh());
        
        Log::info('Usuario registrado', [
            'dni' => $user->dni, 
            'email' => $user->email,
            'final_type' => $user->user_type->value
        ]);
        
        return $user->fresh();
    }

    /**
     * Valida un usuario desde cache
     */
    private function validateCachedUser(User $cached, string $password): AuthResult
    {
        $this->validatePassword($cached, $password);
        
        // Verificar si necesita refresh (solo usuarios API)
        $refreshed = false;
        if ($cached->user_type === UserType::API && $cached->needsRefresh()) {
            $refreshed = $this->refreshFromApi($cached);
        }
        
        return new AuthResult($cached, false, $refreshed);
    }

    /**
     * Valida la password del usuario
     */
    private function validatePassword(User $user, string $password): void
    {
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Credenciales inválidas.']
            ]);
        }
    }

    /**
     * Refresca datos de usuario desde la API
     */
    private function refreshFromApi(User $user): bool
    {
        if ($this->cache->isCircuitBreakerOpen()) {
            return false;
        }
        
        try {
            $socio = $this->sociosApi->getSocioPorDni($user->dni);
            
            if (!$socio) {
                Log::warning('No se pudo refrescar usuario desde API', ['dni' => $user->dni]);
                return false;
            }
            
            $updateData = $this->mapSocioToUserData($socio, $user->dni, $user->password);
            unset($updateData['password']); // Preservar password actual
            unset($updateData['user_type']); // Preservar tipo
            
            $user->update($updateData);
            $user->markAsRefreshed();
            
            // NUEVO: Actualizar URL de imagen durante refresh/login
            if (!empty($socio['Id'])) {
                $fotoUrl = $this->sociosApi->buildFotoUrl($socio['Id']);
                if ($fotoUrl) {
                    $user->update(['foto_url' => $fotoUrl]);
                }
            }
            
            // Actualizar cache
            $this->cache->putUser($user->fresh());
            
            Log::info('Usuario refrescado desde API con imagen', ['dni' => $user->dni]);
            
            return true;
            
        } catch (ApiConnectionException $e) {
            $this->handleApiFailure();
            Log::error('Error al refrescar usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Verifica API y promociona automáticamente durante registro si hay datos
     */
    private function checkApiAndPromoteIfEligible(User $user): void
    {
        try {
            // Verificar si existe en la API de terceros
            $socio = $this->sociosApi->getSocioPorDni($user->dni);
            
            if ($socio && !empty($socio)) {
                // Promocionar automáticamente a usuario API con datos completos
                $this->updateUserWithApiData($user, $socio);
                
                Log::info('Usuario promovido automáticamente a API durante registro', [
                    'dni' => $user->dni,
                    'socio_id' => $socio['Id'] ?? null,
                    'type' => 'API'
                ]);
            } else {
                // Usuario queda como LOCAL (API no tiene datos o devolvió null)
                Log::info('Usuario registrado como LOCAL - API sin datos', [
                    'dni' => $user->dni,
                    'type' => 'LOCAL',
                    'reason' => 'API devolvió null o datos vacíos'
                ]);
            }
            
        } catch (ApiConnectionException $e) {
            // Si hay error de API, el usuario queda como LOCAL
            Log::warning('Error consultando API durante registro - usuario queda como LOCAL', [
                'dni' => $user->dni,
                'type' => 'LOCAL',
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Capturar cualquier otro error para evitar que falle el registro
            Log::error('Error inesperado durante verificación API en registro - usuario queda como LOCAL', [
                'dni' => $user->dni,
                'type' => 'LOCAL',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Actualiza usuario con datos de la API y lo promociona
     */
    private function updateUserWithApiData(User $user, array $socio): void
    {
        $updateData = [
            'user_type' => UserType::API,
            'name' => trim(($socio['apellido'] ?? '') . ', ' . ($socio['nombre'] ?? '')),
            'nombre' => $socio['nombre'] ?? '',
            'apellido' => $socio['apellido'] ?? '',
            'nacionalidad' => $socio['nacionalidad'] ?? null,
            'nacimiento' => $this->parseDate($socio['nacimiento'] ?? null),
            'socio_id' => $socio['Id'] ?? null,
            'barcode' => $socio['barcode'] ?? null,
            'promotion_status' => PromotionStatus::APPROVED,
            'api_last_sync' => now(),
        ];

        // Construir URL de foto si hay socio_id
        if (!empty($socio['Id'])) {
            $fotoUrl = $this->sociosApi->buildFotoUrl($socio['Id']);
            if ($fotoUrl) {
                $updateData['foto_url'] = $fotoUrl;
            }
        }

        $user->update($updateData);
        
        Log::info('✅ Usuario actualizado con datos de API', [
            'dni' => $user->dni,
            'socio_id' => $socio['Id'] ?? null,
            'foto_url' => $updateData['foto_url'] ?? null
        ]);
    }

    /**
     * Mapea datos del socio a atributos del usuario
     */
    private function mapSocioToUserData(array $socio, string $dni, string $password): array
    {
        return [
            'dni' => $dni,
            'password' => Hash::make($password),
            'name' => trim(($socio['apellido'] ?? '') . ', ' . ($socio['nombre'] ?? '')),
            'nombre' => $socio['nombre'] ?? '',
            'apellido' => $socio['apellido'] ?? '',
            'nacionalidad' => $socio['nacionalidad'] ?? null,
            'nacimiento' => $this->parseDate($socio['nacimiento'] ?? null),
            'domicilio' => $socio['domicilio'] ?? null,
            'localidad' => $socio['localidad'] ?? null,
            'telefono' => $socio['telefono'] ?? null,
            'celular' => $socio['celular'] ?? null,
            'email' => $socio['mail'] ?? $socio['email'] ?? null, // API usa 'mail' no 'email'
            'categoria' => $socio['categoria'] ?? null,
            'socio_id' => (string)($socio['Id'] ?? ''),
            'socio_n' => (string)($socio['socio_n'] ?? ''),
            'barcode' => (string)($socio['barcode'] ?? ''), // Nuevo campo de la API
            'saldo' => (float)($socio['saldo'] ?? 0.00), // Nuevo campo: saldo cuenta corriente
            'semaforo' => (int)($socio['semaforo'] ?? 1), // Nuevo campo: estado de deuda
            'estado_socio' => $socio['estado'] ?? 'ACTIVO',
            // Campos adicionales de la API completa
            'tipo_dni' => $socio['tipo_dni'] ?? null,
            'r1' => $socio['r1'] ?? null,
            'r2' => $socio['r2'] ?? null,
            'tutor' => $socio['tutor'] ?? null,
            'observaciones' => $socio['observaciones'] ?? null,
            'deuda' => (float)($socio['deuda'] ?? 0.00),
            'descuento' => (float)($socio['descuento'] ?? 0.00),
            'alta' => $this->parseDate($socio['alta'] ?? null),
            'suspendido' => (bool)($socio['suspendido'] ?? false),
            'facturado' => (bool)($socio['facturado'] ?? true),
            'fecha_baja' => $this->parseDate($socio['fecha_baja'] ?? null),
            'monto_descuento' => isset($socio['monto_descuento']) ? (float)$socio['monto_descuento'] : null,
            'update_ts' => $this->parseDateTime($socio['update_ts'] ?? null),
            'validmail_st' => (bool)($socio['validmail_st'] ?? false),
            'validmail_ts' => $this->parseDateTime($socio['validmail_ts'] ?? null),
            'api_updated_at' => now(),
        ];
    }

    /**
     * Construye la URL directa de la foto del socio
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
            
            Log::info('✅ URL de foto construida', [
                'socio_id' => $socioId,
                'user_id' => $user->id,
                'dni' => $user->dni,
                'foto_url' => $fotoUrl
            ]);
        } else {
            Log::warning('❌ No se pudo construir URL de foto', [
                'socio_id' => $socioId,
                'user_id' => $user->id,
                'dni' => $user->dni
            ]);
        }
    }

    /**
     * Descarga avatar de forma síncrona para incluir en respuesta inicial
     */
    private function downloadAvatarSync(User $user, string $socioId): void
    {
        if (empty($socioId)) {
            return;
        }
        
        try {
            // Timeout corto para no bloquear la respuesta (3 segundos max)
            $imageData = $this->sociosApi->fetchFotoSocio($socioId);
            
            if ($imageData) {
                $filename = "avatars/{$socioId}.jpg";
                $path = storage_path("app/public/{$filename}");
                
                // Crear directorio si no existe
                $directory = dirname($path);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                file_put_contents($path, $imageData);
                
                // Actualizar usuario con path del avatar INMEDIATAMENTE
                $user->update(['avatar_path' => $filename]);
                $user->refresh(); // Recargar para tener el avatar_path actualizado
                
                Log::info('✅ Avatar descargado síncronamente', [
                    'socio_id' => $socioId, 
                    'path' => $filename,
                    'user_id' => $user->id,
                    'dni' => $user->dni,
                    'file_size' => strlen($imageData) . ' bytes',
                    'full_url' => asset("storage/{$filename}")
                ]);
            } else {
                Log::warning('❌ No hay datos de imagen para descargar', [
                    'socio_id' => $socioId,
                    'user_id' => $user->id,
                    'dni' => $user->dni,
                    'message' => 'API no devolvió imagen o imagen vacía'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('💥 Error al descargar avatar síncronamente', [
                'socio_id' => $socioId,
                'user_id' => $user->id,
                'dni' => $user->dni,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // No fallar el registro por problemas de imagen
        }
    }

    /**
     * Descarga avatar de forma asíncrona (fallback para casos específicos)
     */
    private function downloadAvatarAsync(User $user, string $socioId): void
    {
        if (empty($socioId)) {
            return;
        }
        
        // En un entorno real, esto sería un Job en cola
        dispatch(function () use ($user, $socioId) {
            try {
                $imageData = $this->sociosApi->fetchFotoSocio($socioId);
                
                if ($imageData) {
                    $filename = "avatars/{$socioId}.jpg";
                    $path = storage_path("app/public/{$filename}");
                    
                    // Crear directorio si no existe
                    $directory = dirname($path);
                    if (!is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    
                    file_put_contents($path, $imageData);
                    
                    // Actualizar usuario con path del avatar
                    $user->update(['avatar_path' => $filename]);
                    
                    // Actualizar cache
                    $this->cache->forgetUser($user->dni);
                    
                    Log::info('Avatar descargado async', ['socio_id' => $socioId, 'path' => $filename]);
                }
                
            } catch (\Exception $e) {
                Log::error('Error al descargar avatar async', [
                    'socio_id' => $socioId,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * Maneja fallos de la API
     */
    private function handleApiFailure(): void
    {
        $failures = $this->cache->incrementApiFailures();
        
        // Abrir circuit breaker después de 5 fallos
        if ($failures >= 5) {
            $this->cache->openCircuitBreaker();
            Log::warning('Circuit breaker abierto por múltiples fallos de API', ['failures' => $failures]);
        }
    }

    /**
     * Parsea una fecha en formato Y-m-d
     */
    private function parseDate(?string $date): ?Carbon
    {
        if (!$date) return null;
        
        try {
            return Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            Log::warning('Error parseando fecha', ['date' => $date, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parsea un datetime en formato Y-m-d H:i:s
     */
    private function parseDateTime(?string $datetime): ?Carbon
    {
        if (!$datetime) return null;
        
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $datetime);
        } catch (\Exception $e) {
            Log::warning('Error parseando datetime', ['datetime' => $datetime, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Refresca los datos de un usuario desde la API (método público)
     */
    public function refreshUserFromApi(User $user): bool
    {
        return $this->refreshFromApi($user);
    }

    /**
     * Valida y promueve usuario si es elegible según API de terceros
     */
    private function validateAndPromoteIfEligible(User $user): void
    {
        Log::info('=== INICIO VALIDACIÓN API ===', ['dni' => $user->dni]);
        
        // Skip si circuit breaker está abierto
        if ($this->cache->isCircuitBreakerOpen()) {
            Log::warning('❌ Validación API omitida - circuit breaker abierto', ['dni' => $user->dni]);
            return;
        }

        // Skip si ya tenemos resultado negativo en cache
        if ($this->cache->hasNegativeResult($user->dni)) {
            Log::warning('❌ Validación API omitida - resultado negativo en cache', ['dni' => $user->dni]);
            return;
        }

        Log::info('🔄 Consultando API de terceros...', ['dni' => $user->dni]);

        try {
            $socio = $this->sociosApi->getSocioPorDni($user->dni);
            
            Log::info('📡 Respuesta de API recibida', [
                'dni' => $user->dni,
                'socio_found' => !empty($socio),
                'socio_data' => $socio ? array_keys($socio) : null
            ]);
            
            if ($socio) {
                // Usuario existe en API - promover a COMPLETO
                Log::info('✅ Usuario encontrado en API - iniciando promoción', [
                    'dni' => $user->dni,
                    'socio_id' => $socio['Id'] ?? 'N/A',
                    'nombre' => ($socio['apellido'] ?? '') . ', ' . ($socio['nombre'] ?? '')
                ]);
                
                $this->promoteUserToComplete($user, $socio);
                
                Log::info('🎉 Usuario promovido automáticamente a COMPLETO', [
                    'dni' => $user->dni,
                    'socio_id' => $socio['Id'] ?? 'N/A',
                    'new_type' => $user->fresh()->user_type->value
                ]);
            } else {
                // Usuario no existe en API - mantener como SIMPLE
                $this->cache->putNegativeResult($user->dni);
                Log::info('⚠️ Usuario NO encontrado en API - mantener como LOCAL', ['dni' => $user->dni]);
            }
            
            // Reset API failures en caso de éxito
            $this->cache->resetApiFailures();
            
        } catch (ApiConnectionException $e) {
            // Error de conectividad - mantener como LOCAL y registrar fallo
            $this->handleApiFailure();
            Log::error('💥 Error al validar usuario con API - mantenido como LOCAL', [
                'dni' => $user->dni,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Exception $e) {
            Log::error('💥 Error inesperado en validación API', [
                'dni' => $user->dni,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        Log::info('=== FIN VALIDACIÓN API ===', ['dni' => $user->dni]);
    }

    /**
     * Promueve un usuario LOCAL a COMPLETO con datos de la API
     */
    private function promoteUserToComplete(User $user, array $socio): void
    {
        $updateData = $this->mapSocioToUserData($socio, $user->dni, $user->password);
        
        // Preservar SOLO datos críticos del registro local
        unset($updateData['password']); // Mantener password local
        unset($updateData['dni']); // Mantener DNI
        
        // Los datos de la API REEMPLAZAN completamente los del registro
        // Incluyendo name, email, phone, etc.
        $updateData['user_type'] = UserType::API;
        $updateData['promotion_status'] = PromotionStatus::NONE;
        
        $user->update($updateData);
        
        // Construir URL directa de la foto
        if (!empty($socio['Id'])) {
            $fotoUrl = $this->sociosApi->buildFotoUrl($socio['Id']);
            if ($fotoUrl) {
                $user->update(['foto_url' => $fotoUrl]);
            }
        }
    }

    /**
     * Limpia el cache de un usuario
     */
    public function clearUserCache(string $dni): void
    {
        $this->cache->forgetUser($dni);
    }
}
