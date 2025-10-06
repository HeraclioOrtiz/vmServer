<?php

namespace App\Http\Requests\Gym;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_professor || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $templateId = $this->route('dailyTemplate')?->id ?? $this->route('template')?->id;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('daily_templates')->ignore($templateId)
            ],
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:strength,cardio,flexibility,mixed,rehabilitation,beginner,intermediate,advanced',
            'difficulty_level' => 'required|integer|min:1|max:5',
            'estimated_duration' => 'required|integer|min:10|max:180',
            'target_muscle_groups' => 'required|array|min:1',
            'target_muscle_groups.*' => 'string|in:chest,back,shoulders,arms,biceps,triceps,forearms,abs,core,legs,quadriceps,hamstrings,calves,glutes,full_body',
            'equipment_needed' => 'nullable|array',
            'equipment_needed.*' => 'string|max:100',
            'is_preset' => 'boolean',
            'is_public' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string|max:500',
            
            // Ejercicios de la plantilla
            'exercises' => 'required|array|min:1|max:20',
            'exercises.*.exercise_id' => 'required|integer|exists:exercises,id',
            'exercises.*.order' => 'required|integer|min:1',
            'exercises.*.rest_seconds' => 'nullable|integer|min:0|max:600',
            'exercises.*.notes' => 'nullable|string|max:300',
            
            // Sets por ejercicio
            'exercises.*.sets' => 'required|array|min:1|max:10',
            'exercises.*.sets.*.set_number' => 'required|integer|min:1',
            'exercises.*.sets.*.reps' => 'nullable|integer|min:1|max:100',
            'exercises.*.sets.*.weight' => 'nullable|numeric|min:0|max:1000',
            'exercises.*.sets.*.duration_seconds' => 'nullable|integer|min:1|max:3600',
            'exercises.*.sets.*.distance_meters' => 'nullable|integer|min:1|max:50000',
            'exercises.*.sets.*.rest_seconds' => 'nullable|integer|min:0|max:600',
            'exercises.*.sets.*.notes' => 'nullable|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'Ya existe una plantilla con este título.',
            'title.required' => 'El título de la plantilla es obligatorio.',
            'category.in' => 'Categoría de plantilla inválida.',
            'difficulty_level.min' => 'El nivel de dificultad debe ser entre 1 y 5.',
            'difficulty_level.max' => 'El nivel de dificultad debe ser entre 1 y 5.',
            'estimated_duration.min' => 'La duración estimada debe ser al menos 10 minutos.',
            'estimated_duration.max' => 'La duración estimada no puede exceder 180 minutos.',
            'target_muscle_groups.required' => 'Debe seleccionar al menos un grupo muscular objetivo.',
            'exercises.required' => 'Debe incluir al menos un ejercicio.',
            'exercises.max' => 'No puede incluir más de 20 ejercicios.',
            'exercises.*.exercise_id.exists' => 'El ejercicio seleccionado no existe.',
            'exercises.*.sets.required' => 'Cada ejercicio debe tener al menos un set.',
            'exercises.*.sets.max' => 'No puede incluir más de 10 sets por ejercicio.',
            'exercises.*.sets.*.reps.max' => 'Las repeticiones no pueden exceder 100.',
            'exercises.*.sets.*.weight.max' => 'El peso no puede exceder 1000kg.',
            'exercises.*.sets.*.duration_seconds.max' => 'La duración no puede exceder 1 hora.',
            'exercises.*.sets.*.distance_meters.max' => 'La distancia no puede exceder 50km.',
        ];
    }

    public function attributes(): array
    {
        return [
            'difficulty_level' => 'nivel de dificultad',
            'estimated_duration' => 'duración estimada',
            'target_muscle_groups' => 'grupos musculares objetivo',
            'equipment_needed' => 'equipamiento necesario',
            'is_preset' => 'es plantilla predefinida',
            'is_public' => 'es pública',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir strings a boolean
        foreach (['is_preset', 'is_public'] as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $this->merge([
                    $field => filter_var($this->$field, FILTER_VALIDATE_BOOLEAN)
                ]);
            }
        }

        // Ordenar ejercicios por order
        if ($this->has('exercises') && is_array($this->exercises)) {
            $exercises = collect($this->exercises)->sortBy('order')->values()->toArray();
            $this->merge(['exercises' => $exercises]);
        }
    }
}
