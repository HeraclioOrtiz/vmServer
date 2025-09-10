<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'dni' => 'required|string|regex:/^\d{7,8}$/|max:8',
            'password' => 'required|string|min:4|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es requerido.',
            'dni.regex' => 'El DNI debe contener entre 7 y 8 dígitos.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 4 caracteres.',
        ];
    }

    /**
     * Get the validated DNI trimmed.
     */
    public function getDni(): string
    {
        return trim($this->validated()['dni']);
    }

    /**
     * Get the validated password.
     */
    public function getPassword(): string
    {
        return $this->validated()['password'];
    }
}
