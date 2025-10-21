<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required_without:dni|email|max:255',
            'dni' => 'required_without:email|string|digits_between:7,8',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Debes proporcionar un email o DNI.',
            'dni.required_without' => 'Debes proporcionar un email o DNI.',
            'email.email' => 'El email no es válido.',
            'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos.',
        ];
    }
}
