<?php

namespace App\Http\Requests\Gym;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyTemplateRequest extends FormRequest
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
            'estimated_duration_min' => 'nullable|integer|min:0|max:600',
            'level' => 'nullable|string|max:50',
            'tags' => 'array',
            'tags.*' => 'string',
            'exercises' => 'array',
            'exercises.*.exercise_id' => 'nullable|integer|exists:gym_exercises,id',
            'exercises.*.order' => 'nullable|integer|min:1',
            'exercises.*.notes' => 'nullable|string',
            'exercises.*.sets' => 'array',
            'exercises.*.sets.*.set_number' => 'nullable|integer|min:1',
            'exercises.*.sets.*.reps_min' => 'nullable|integer|min:1',
            'exercises.*.sets.*.reps_max' => 'nullable|integer|min:1',
            'exercises.*.sets.*.weight_min' => 'nullable|numeric|min:0|max:1000',
            'exercises.*.sets.*.weight_max' => 'nullable|numeric|min:0|max:1000',
            'exercises.*.sets.*.weight_target' => 'nullable|numeric|min:0|max:1000',
            'exercises.*.sets.*.rest_seconds' => 'nullable|integer|min:0',
            'exercises.*.sets.*.rpe_target' => 'nullable|numeric|min:0|max:10',
            'exercises.*.sets.*.notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título de la plantilla es obligatorio.',
            'title.max' => 'El título no puede superar 255 caracteres.',
            'estimated_duration_min.max' => 'La duración estimada no puede superar 600 minutos.',
            'exercises.array' => 'Los ejercicios deben ser un array.',
            'exercises.*.exercise_id.exists' => 'El ejercicio seleccionado no existe.',
            'exercises.*.sets.*.weight_min.numeric' => 'El peso mínimo debe ser un número.',
            'exercises.*.sets.*.weight_max.numeric' => 'El peso máximo debe ser un número.',
            'exercises.*.sets.*.weight_target.numeric' => 'El peso objetivo debe ser un número.',
            'exercises.*.sets.*.rpe_target.max' => 'El RPE no puede ser mayor a 10.',
        ];
    }
}
