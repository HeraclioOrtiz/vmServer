<?php

namespace App\Http\Requests\Gym;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WeeklyTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_professor || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $templateId = $this->route('weeklyTemplate')?->id ?? $this->route('template')?->id;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('weekly_templates')->ignore($templateId)
            ],
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:strength,cardio,flexibility,mixed,rehabilitation,weight_loss,muscle_gain,endurance,beginner,intermediate,advanced',
            'difficulty_level' => 'required|integer|min:1|max:5',
            'weeks_duration' => 'required|integer|min:1|max:52',
            'sessions_per_week' => 'required|integer|min:1|max:7',
            'target_goals' => 'required|array|min:1',
            'target_goals.*' => 'string|in:weight_loss,muscle_gain,strength,endurance,flexibility,rehabilitation,general_fitness',
            'target_muscle_groups' => 'required|array|min:1',
            'target_muscle_groups.*' => 'string|in:chest,back,shoulders,arms,biceps,triceps,forearms,abs,core,legs,quadriceps,hamstrings,calves,glutes,full_body',
            'equipment_needed' => 'nullable|array',
            'equipment_needed.*' => 'string|max:100',
            'is_preset' => 'boolean',
            'is_public' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string|max:1000',
            
            // Días de la semana
            'days' => 'required|array|min:1|max:7',
            'days.*.day_of_week' => 'required|integer|min:0|max:6', // 0=Domingo, 6=Sábado
            'days.*.daily_template_id' => 'required|integer|exists:daily_templates,id',
            'days.*.is_rest_day' => 'boolean',
            'days.*.notes' => 'nullable|string|max:300',
            'days.*.order' => 'nullable|integer|min:1|max:7',
            
            // Progresión semanal (opcional)
            'progression' => 'nullable|array',
            'progression.type' => 'nullable|string|in:linear,percentage,custom',
            'progression.increment_weight' => 'nullable|numeric|min:0|max:100',
            'progression.increment_reps' => 'nullable|integer|min:0|max:10',
            'progression.increment_sets' => 'nullable|integer|min:0|max:5',
            'progression.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'Ya existe una plantilla semanal con este título.',
            'title.required' => 'El título de la plantilla es obligatorio.',
            'category.in' => 'Categoría de plantilla inválida.',
            'difficulty_level.min' => 'El nivel de dificultad debe ser entre 1 y 5.',
            'difficulty_level.max' => 'El nivel de dificultad debe ser entre 1 y 5.',
            'weeks_duration.min' => 'La duración debe ser al menos 1 semana.',
            'weeks_duration.max' => 'La duración no puede exceder 52 semanas.',
            'sessions_per_week.min' => 'Debe tener al menos 1 sesión por semana.',
            'sessions_per_week.max' => 'No puede tener más de 7 sesiones por semana.',
            'target_goals.required' => 'Debe seleccionar al menos un objetivo.',
            'target_muscle_groups.required' => 'Debe seleccionar al menos un grupo muscular.',
            'days.required' => 'Debe configurar al menos un día de entrenamiento.',
            'days.max' => 'No puede configurar más de 7 días.',
            'days.*.day_of_week.min' => 'Día de la semana inválido.',
            'days.*.day_of_week.max' => 'Día de la semana inválido.',
            'days.*.daily_template_id.exists' => 'La plantilla diaria seleccionada no existe.',
            'progression.increment_weight.max' => 'El incremento de peso no puede exceder 100kg.',
            'progression.increment_reps.max' => 'El incremento de repeticiones no puede exceder 10.',
            'progression.increment_sets.max' => 'El incremento de sets no puede exceder 5.',
        ];
    }

    public function attributes(): array
    {
        return [
            'difficulty_level' => 'nivel de dificultad',
            'weeks_duration' => 'duración en semanas',
            'sessions_per_week' => 'sesiones por semana',
            'target_goals' => 'objetivos',
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

        // Validar días únicos
        if ($this->has('days') && is_array($this->days)) {
            $days = collect($this->days);
            
            // Marcar días de descanso
            $days = $days->map(function ($day) {
                if (isset($day['is_rest_day']) && is_string($day['is_rest_day'])) {
                    $day['is_rest_day'] = filter_var($day['is_rest_day'], FILTER_VALIDATE_BOOLEAN);
                }
                return $day;
            });

            $this->merge(['days' => $days->toArray()]);
        }
    }
}
