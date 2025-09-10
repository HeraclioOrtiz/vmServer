<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'club_password' => [
                'required',
                'string',
                'min:4',
                'max:50'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'club_password.required' => 'La contraseña del club es obligatoria.',
            'club_password.min' => 'La contraseña del club debe tener al menos 4 caracteres.',
            'club_password.max' => 'La contraseña del club no puede exceder 50 caracteres.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'club_password' => 'contraseña del club'
        ];
    }
}
