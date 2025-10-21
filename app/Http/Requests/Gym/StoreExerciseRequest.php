<?php

namespace App\Http\Requests\Gym;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'muscle_groups' => 'nullable|array',
            'muscle_groups.*' => 'string',
            'target_muscle_groups' => 'nullable|array',
            'target_muscle_groups.*' => 'string',
            'movement_pattern' => 'nullable|string|max:255',
            'equipment' => 'nullable|string|max:255',
            'difficulty_level' => 'nullable|string|in:beginner,intermediate,advanced',
            'exercise_type' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'instructions' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del ejercicio es obligatorio.',
            'name.max' => 'El nombre del ejercicio no puede superar 255 caracteres.',
            'difficulty_level.in' => 'El nivel de dificultad debe ser: beginner, intermediate o advanced.',
            'muscle_groups.array' => 'Los grupos musculares deben ser un array.',
            'target_muscle_groups.array' => 'Los músculos objetivo deben ser un array.',
            'tags.array' => 'Las etiquetas deben ser un array.',
        ];
    }
}
