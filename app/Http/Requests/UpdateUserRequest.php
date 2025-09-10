<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar autorización basada en roles
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->route('user');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:100'
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'password' => [
                'sometimes',
                'string',
                'min:8'
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede exceder 100 caracteres.',
            
            'email.email' => 'Debe ser un email válido.',
            'email.unique' => 'Este email ya está en uso.',
            
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            
            'phone.max' => 'El teléfono no puede exceder 20 caracteres.'
        ];
    }
}
