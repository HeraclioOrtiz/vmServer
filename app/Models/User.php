<?php

namespace App\Models;

use App\Enums\UserType;
use App\Enums\PromotionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dni',
        'user_type',
        'promotion_status',
        'promoted_at',
        'nombre',
        'apellido',
        'nacionalidad',
        'nacimiento',
        'domicilio',
        'localidad',
        'telefono',
        'celular',
        'categoria',
        'socio_id',
        'socio_n',
        'barcode',
        'saldo',
        'semaforo',
        'estado_socio',
        'avatar_path',
        'foto_url',
        'api_updated_at',
        // Nuevos campos de la API completa
        'tipo_dni',
        'r1',
        'r2',
        'tutor',
        'observaciones',
        'deuda',
        'descuento',
        'alta',
        'suspendido',
        'facturado',
        'fecha_baja',
        'monto_descuento',
        'update_ts',
        'validmail_st',
        'validmail_ts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'user_type' => UserType::class,
        'promotion_status' => PromotionStatus::class,
        'promoted_at' => 'datetime',
        'nacimiento' => 'date',
        'api_updated_at' => 'datetime',
        'alta' => 'date',
        'fecha_baja' => 'date',
        'update_ts' => 'datetime',
        'validmail_ts' => 'datetime',
        'saldo' => 'decimal:2',
        'deuda' => 'decimal:2',
        'descuento' => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'suspendido' => 'boolean',
        'facturado' => 'boolean',
        'validmail_st' => 'boolean',
        'semaforo' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'foto_url',
    ];

    /**
     * Scope para usuarios locales
     */
    public function scopeLocal($query)
    {
        return $query->where('user_type', UserType::LOCAL);
    }

    /**
     * Scope para usuarios API
     */
    public function scopeApi($query)
    {
        return $query->where('user_type', UserType::API);
    }

    /**
     * Scope para usuarios que necesitan refresh desde la API
     */
    public function scopeNeedsRefresh($query, int $hours = 24)
    {
        return $query->where('user_type', UserType::API)
                    ->where(function ($q) use ($hours) {
                        $q->whereNull('api_updated_at')
                          ->orWhere('api_updated_at', '<', now()->subHours($hours));
                    });
    }

    /**
     * Scope para usuarios con datos completos
     */
    public function scopeComplete($query)
    {
        return $query->where('user_type', UserType::API)
                    ->whereNotNull('socio_id');
    }

    /**
     * Scope para usuarios elegibles para promoción
     */
    public function scopeEligibleForPromotion($query)
    {
        return $query->where('user_type', UserType::LOCAL)
                    ->where('promotion_status', PromotionStatus::NONE);
    }

    /**
     * Accessor para el nombre de display
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_type === UserType::API && $this->apellido && $this->nombre
                ? trim("{$this->apellido}, {$this->nombre}")
                : $this->name
        );
    }

    /**
     * Accessor para el nombre completo (API users)
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_type === UserType::API && $this->apellido && $this->nombre
                ? trim("{$this->apellido}, {$this->nombre}")
                : $this->name
        );
    }

    /**
     * Get the user's avatar URL.
     * Prioriza foto_url (URL directa) sobre avatar_path (almacenamiento local)
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->foto_url 
                ? $this->foto_url
                : ($this->avatar_path ? asset("storage/{$this->avatar_path}") : null),
        );
    }

    /**
     * Accessor para foto_url - prioriza URL directa sobre avatar local
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['foto_url'] 
                ?? ($this->avatar_path ? asset("storage/{$this->avatar_path}") : null)
        );
    }

    /**
     * Determina si el usuario puede ser promocionado
     */
    public function canPromote(): bool
    {
        return $this->user_type === UserType::LOCAL && 
               $this->promotion_status === PromotionStatus::NONE;
    }

    /**
     * Determina si el usuario tiene datos completos
     */
    public function isComplete(): bool
    {
        return $this->user_type === UserType::API;
    }

    /**
     * Determina si el usuario es local
     */
    public function isLocal(): bool
    {
        return $this->user_type === UserType::LOCAL;
    }

    /**
     * Determina si el usuario es API
     */
    public function isApi(): bool
    {
        return $this->user_type === UserType::API;
    }

    /**
     * Determina si el usuario necesita refresh desde la API
     */
    public function needsRefresh(int $hours = 24): bool
    {
        return $this->user_type === UserType::API && (
            $this->api_updated_at === null || 
            $this->api_updated_at->diffInHours(now()) > $hours
        );
    }

    /**
     * Marca el usuario como actualizado desde la API
     */
    public function markAsRefreshed(): void
    {
        $this->update(['api_updated_at' => now()]);
    }

    /**
     * Promociona el usuario de local a API
     */
    public function promoteToApi(array $apiData): void
    {
        $this->update([
            'user_type' => UserType::API,
            'promotion_status' => PromotionStatus::APPROVED,
            'promoted_at' => now(),
            ...$apiData
        ]);
    }

    /**
     * Obtiene los campos permitidos para edición según el tipo de usuario
     */
    public function getEditableFields(): array
    {
        return match($this->user_type) {
            UserType::LOCAL => ['name', 'email', 'phone', 'password'],
            UserType::API => ['phone'], // Solo algunos campos para usuarios API
        };
    }
}
