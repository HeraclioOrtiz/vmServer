<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dni' => $this->dni,
            'user_type' => $this->user_type->value ?? 'local',
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            
            // Campos de promoción
            'promotion_status' => $this->promotion_status->value ?? 'none',
            
            // Foto URL (siempre incluir, puede ser null)
            'foto_url' => $this->foto_url,
            
            // Campos específicos de usuarios API (solo si es tipo API)
            'socio_id' => $this->when($this->user_type->value === 'api', $this->socio_id),
            'socio_n' => $this->when($this->user_type->value === 'api', $this->socio_n),
            'nombre' => $this->when($this->user_type->value === 'api', $this->nombre),
            'apellido' => $this->when($this->user_type->value === 'api', $this->apellido),
            'barcode' => $this->when($this->user_type->value === 'api', $this->barcode),
            
            // Campos financieros críticos para usuarios API
            'saldo' => $this->when($this->user_type->value === 'api', $this->saldo),
            'semaforo' => $this->when($this->user_type->value === 'api', $this->semaforo),
            'deuda' => $this->when($this->user_type->value === 'api', $this->deuda),
            
            // Información personal adicional para usuarios API
            'nacionalidad' => $this->when($this->user_type->value === 'api', $this->nacionalidad),
            'nacimiento' => $this->when($this->user_type->value === 'api', $this->nacimiento?->toDateString()),
            'domicilio' => $this->when($this->user_type->value === 'api', $this->domicilio),
            'localidad' => $this->when($this->user_type->value === 'api', $this->localidad),
            'telefono' => $this->when($this->user_type->value === 'api', $this->telefono),
            'celular' => $this->when($this->user_type->value === 'api', $this->celular),
            
            // Estado del socio
            'estado_socio' => $this->when($this->user_type->value === 'api', $this->estado_socio),
            'suspendido' => $this->when($this->user_type->value === 'api', $this->suspendido),
            'categoria' => $this->when($this->user_type->value === 'api', $this->categoria),
            
            // Campos del sistema (roles y permisos)
            'is_professor' => $this->is_professor ?? false,
            'is_admin' => $this->is_admin ?? false,
            'permissions' => $this->permissions ?? [],
            'account_status' => $this->account_status ?? 'active',
            'type_label' => $this->type_label,
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
