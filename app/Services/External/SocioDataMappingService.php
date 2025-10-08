<?php

namespace App\Services\External;

use App\Enums\UserType;
use App\Enums\PromotionStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SocioDataMappingService
{
    /**
     * Mapea datos de socio de la API externa a formato de usuario
     */
    public function mapSocioToUserData(array $socio, string $dni, string $password): array
    {
        try {
            return [
                'dni' => $dni,
                'user_type' => UserType::API,
                'promotion_status' => PromotionStatus::APPROVED,
                'promoted_at' => now(),
                'password' => $password,
                
                // Información personal
                'nombre' => $this->sanitizeString($socio['nombre'] ?? ''),
                'apellido' => $this->sanitizeString($socio['apellido'] ?? ''),
                'name' => $this->buildFullName($socio),
                'email' => $this->sanitizeEmail($socio['email'] ?? null),
                'nacionalidad' => $this->sanitizeString($socio['nacionalidad'] ?? ''),
                'nacimiento' => $this->parseDate($socio['nacimiento'] ?? null),
                
                // Información de contacto
                'domicilio' => $this->sanitizeString($socio['domicilio'] ?? ''),
                'localidad' => $this->sanitizeString($socio['localidad'] ?? ''),
                'telefono' => $this->sanitizePhone($socio['telefono'] ?? ''),
                'celular' => $this->sanitizePhone($socio['celular'] ?? ''),
                
                // Información del club
                'socio_id' => $this->parseInt($socio['socio_id'] ?? null),
                'socio_n' => $this->parseInt($socio['socio_n'] ?? null),
                'categoria' => $this->sanitizeString($socio['categoria'] ?? ''),
                'barcode' => $this->sanitizeString($socio['barcode'] ?? $dni),
                
                // Estado financiero
                'saldo' => $this->parseDecimal($socio['saldo'] ?? 0),
                'deuda' => $this->parseDecimal($socio['deuda'] ?? 0),
                'descuento' => $this->parseDecimal($socio['descuento'] ?? 0),
                'monto_descuento' => $this->parseDecimal($socio['monto_descuento'] ?? 0),
                
                // Estado del socio
                'estado_socio' => $this->sanitizeString($socio['estado_socio'] ?? 'ACTIVO'),
                'semaforo' => $this->parseInt($socio['semaforo'] ?? 1),
                'suspendido' => $this->parseBoolean($socio['suspendido'] ?? false),
                'facturado' => $this->parseBoolean($socio['facturado'] ?? false),
                
                // Fechas del sistema
                'alta' => $this->parseDate($socio['alta'] ?? null),
                'fecha_baja' => $this->parseDate($socio['fecha_baja'] ?? null),
                'update_ts' => $this->parseDateTime($socio['update_ts'] ?? null),
                
                // Información adicional
                'tipo_dni' => $this->sanitizeString($socio['tipo_dni'] ?? 'DNI'),
                'r1' => $this->sanitizeString($socio['r1'] ?? ''),
                'r2' => $this->sanitizeString($socio['r2'] ?? ''),
                'tutor' => $this->sanitizeString($socio['tutor'] ?? ''),
                'observaciones' => $this->sanitizeString($socio['observaciones'] ?? ''),
                
                // Email validation
                'validmail_st' => $this->parseBoolean($socio['validmail_st'] ?? false),
                'validmail_ts' => $this->parseDateTime($socio['validmail_ts'] ?? null),
                
                // Foto
                'foto_url' => $this->sanitizeUrl($socio['foto_url'] ?? null),
                
                // ⚠️ TEMPORAL: Acceso automático al gimnasio para todos los usuarios API
                // TODO: Remover cuando se implemente lógica de asignación manual
                'student_gym' => true,
                'student_gym_since' => now(),
                
                // Timestamp de actualización
                'api_updated_at' => now(),
            ];
        } catch (\Exception $e) {
            Log::error('Error mapeando datos de socio', [
                'dni' => $dni,
                'error' => $e->getMessage(),
                'socio_data' => $socio,
            ]);
            
            throw new \Exception('Error procesando datos del socio: ' . $e->getMessage());
        }
    }

    /**
     * Mapea datos mínimos para búsqueda rápida
     */
    public function mapSocioToMinimalData(array $socio, string $dni): array
    {
        return [
            'dni' => $dni,
            'nombre' => $this->sanitizeString($socio['nombre'] ?? ''),
            'apellido' => $this->sanitizeString($socio['apellido'] ?? ''),
            'socio_id' => $this->parseInt($socio['socio_id'] ?? null),
            'estado_socio' => $this->sanitizeString($socio['estado_socio'] ?? 'ACTIVO'),
            'semaforo' => $this->parseInt($socio['semaforo'] ?? 1),
        ];
    }

    /**
     * Construye el nombre completo
     */
    private function buildFullName(array $socio): string
    {
        $nombre = $this->sanitizeString($socio['nombre'] ?? '');
        $apellido = $this->sanitizeString($socio['apellido'] ?? '');
        
        if ($apellido && $nombre) {
            return "{$apellido}, {$nombre}";
        } elseif ($apellido) {
            return $apellido;
        } elseif ($nombre) {
            return $nombre;
        }
        
        return 'Usuario API';
    }

    /**
     * Sanitiza strings
     */
    private function sanitizeString(?string $value): string
    {
        if (empty($value)) {
            return '';
        }
        
        return trim(strip_tags($value));
    }

    /**
     * Sanitiza email
     */
    private function sanitizeEmail(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }
        
        $email = trim(strtolower($email));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        
        return $email;
    }

    /**
     * Sanitiza teléfono
     */
    private function sanitizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        
        // Remover todo excepto números, espacios, guiones y paréntesis
        return preg_replace('/[^\d\s\-\(\)]/', '', trim($phone));
    }

    /**
     * Sanitiza URL
     */
    private function sanitizeUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        
        $url = trim($url);
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        
        return $url;
    }

    /**
     * Parsea entero
     */
    private function parseInt($value): ?int
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        return null;
    }

    /**
     * Parsea decimal
     */
    private function parseDecimal($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }
        
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        return 0.0;
    }

    /**
     * Parsea booleano
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'si', 'sí']);
        }
        
        if (is_numeric($value)) {
            return (int) $value === 1;
        }
        
        return false;
    }

    /**
     * Parsea fecha
     */
    private function parseDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Exception $e) {
            Log::warning('Error parseando fecha', [
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parsea fecha y hora
     */
    private function parseDateTime($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            Log::warning('Error parseando fecha y hora', [
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Valida que los datos mapeados sean consistentes
     */
    public function validateMappedData(array $data): array
    {
        $errors = [];

        // Validaciones críticas
        if (empty($data['dni'])) {
            $errors[] = 'DNI es requerido';
        }

        if (empty($data['nombre']) && empty($data['apellido'])) {
            $errors[] = 'Nombre o apellido es requerido';
        }

        if (!is_null($data['socio_id']) && $data['socio_id'] <= 0) {
            $errors[] = 'ID de socio debe ser positivo';
        }

        if (!in_array($data['estado_socio'], ['ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'BAJA'])) {
            $errors[] = 'Estado de socio inválido';
        }

        if (!in_array($data['semaforo'], [0, 1, 2])) {
            $errors[] = 'Valor de semáforo inválido';
        }

        return $errors;
    }
}
