<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->canManageUsers();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'dni' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users')->ignore($userId)
            ],
            'telefono' => 'sometimes|nullable|string|max:20',
            'celular' => 'sometimes|nullable|string|max:20',
            'domicilio' => 'sometimes|nullable|string|max:255',
            'localidad' => 'sometimes|nullable|string|max:100',
            'nacionalidad' => 'sometimes|nullable|string|max:50',
            'nacimiento' => 'sometimes|nullable|date|before:today',
            'categoria' => 'sometimes|nullable|string|max:50',
            'observaciones' => 'sometimes|nullable|string|max:1000',
            'account_status' => 'sometimes|in:active,suspended,pending',
            'admin_notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Este email ya está registrado por otro usuario.',
            'dni.unique' => 'Este DNI ya está registrado por otro usuario.',
            'nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'account_status.in' => 'El estado de cuenta debe ser: active, suspended o pending.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('dni')) {
            $this->merge([
                'dni' => preg_replace('/[^0-9]/', '', $this->dni)
            ]);
        }
    }
}
