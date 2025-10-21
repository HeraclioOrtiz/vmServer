<?php

namespace App\Http\Requests\Gym;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeeklyTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'goal' => 'nullable|string|max:50',
            'split' => 'nullable|string|max:50',
            'days_per_week' => 'nullable|integer|min:1|max:7',
            'tags' => 'array',
            'tags.*' => 'string',
            'days' => 'array',
            'days.*.weekday' => 'required|integer|min:1|max:7',
            'days.*.daily_template_id' => 'nullable|integer|exists:gym_daily_templates,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título de la plantilla semanal es obligatorio.',
            'title.max' => 'El título no puede superar 255 caracteres.',
            'days_per_week.min' => 'Los días por semana deben ser al menos 1.',
            'days_per_week.max' => 'Los días por semana no pueden ser más de 7.',
            'days.array' => 'Los días deben ser un array.',
            'days.*.weekday.required' => 'El día de la semana es obligatorio.',
            'days.*.weekday.min' => 'El día de la semana debe estar entre 1 y 7.',
            'days.*.weekday.max' => 'El día de la semana debe estar entre 1 y 7.',
            'days.*.daily_template_id.exists' => 'La plantilla diaria seleccionada no existe.',
        ];
    }
}
