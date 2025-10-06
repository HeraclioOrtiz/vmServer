<?php

namespace App\Http\Requests\Gym;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_professor || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $exerciseId = $this->route('exercise')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gym_exercises', 'name')->ignore($exerciseId)
            ],
            'description' => 'nullable|string',
            'muscle_groups' => 'nullable|array',
            'muscle_groups.*' => 'string|max:100',
            'target_muscle_groups' => 'nullable|array',
            'target_muscle_groups.*' => 'string|max:100',
            'movement_pattern' => 'nullable|string|max:255',
            'equipment' => 'nullable|string|max:255',
            'difficulty_level' => 'nullable|string|in:beginner,intermediate,advanced',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'instructions' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe un ejercicio con este nombre.',
            'name.required' => 'El nombre del ejercicio es obligatorio.',
            'difficulty_level.in' => 'El nivel de dificultad debe ser: beginner, intermediate o advanced.',
        ];
    }

    public function attributes(): array
    {
        return [
            'muscle_groups' => 'grupos musculares',
            'target_muscle_groups' => 'músculos objetivo',
            'difficulty_level' => 'nivel de dificultad',
            'movement_pattern' => 'patrón de movimiento',
            'instructions' => 'instrucciones',
        ];
    }
}
