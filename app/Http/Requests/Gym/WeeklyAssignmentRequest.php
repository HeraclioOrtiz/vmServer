<?php

namespace App\Http\Requests\Gym;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class WeeklyAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_professor || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'week_start' => 'required|date|after_or_equal:today',
            'week_end' => 'required|date|after:week_start',
            'source_type' => 'required|string|in:template,custom',
            'source_id' => 'required_if:source_type,template|nullable|integer|exists:weekly_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'difficulty_level' => 'required|integer|min:1|max:5',
            'target_goals' => 'required|array|min:1',
            'target_goals.*' => 'string|in:weight_loss,muscle_gain,strength,endurance,flexibility,rehabilitation,general_fitness',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'notes' => 'nullable|string|max:1000',
            'auto_progress' => 'boolean',
            'send_reminders' => 'boolean',
            'track_adherence' => 'boolean',
            
            // Asignaciones diarias
            'daily_assignments' => 'required|array|min:1|max:7',
            'daily_assignments.*.date' => 'required|date|between:week_start,week_end',
            'daily_assignments.*.daily_template_id' => 'nullable|integer|exists:daily_templates,id',
            'daily_assignments.*.is_rest_day' => 'boolean',
            'daily_assignments.*.notes' => 'nullable|string|max:300',
            'daily_assignments.*.estimated_duration' => 'nullable|integer|min:10|max:180',
            
            // Customizaciones por ejercicio (si es custom)
            'daily_assignments.*.exercises' => 'nullable|array',
            'daily_assignments.*.exercises.*.exercise_id' => 'required|integer|exists:exercises,id',
            'daily_assignments.*.exercises.*.order' => 'required|integer|min:1',
            'daily_assignments.*.exercises.*.sets' => 'required|array|min:1|max:10',
            'daily_assignments.*.exercises.*.sets.*.reps' => 'nullable|integer|min:1|max:100',
            'daily_assignments.*.exercises.*.sets.*.weight' => 'nullable|numeric|min:0|max:1000',
            'daily_assignments.*.exercises.*.sets.*.duration_seconds' => 'nullable|integer|min:1|max:3600',
            'daily_assignments.*.exercises.*.sets.*.distance_meters' => 'nullable|integer|min:1|max:50000',
            'daily_assignments.*.exercises.*.sets.*.rest_seconds' => 'nullable|integer|min:0|max:600',
            'daily_assignments.*.exercises.*.notes' => 'nullable|string|max:200',
            
            // Progresión personalizada
            'progression_settings' => 'nullable|array',
            'progression_settings.type' => 'nullable|string|in:linear,percentage,custom,none',
            'progression_settings.increment_weight' => 'nullable|numeric|min:0|max:100',
            'progression_settings.increment_reps' => 'nullable|integer|min:0|max:10',
            'progression_settings.increment_duration' => 'nullable|integer|min:0|max:300',
            'progression_settings.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'week_start.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'week_end.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'source_id.required_if' => 'Debe seleccionar una plantilla cuando el tipo es "template".',
            'source_id.exists' => 'La plantilla seleccionada no existe.',
            'difficulty_level.min' => 'El nivel de dificultad debe ser entre 1 y 5.',
            'difficulty_level.max' => 'El nivel de dificultad debe ser entre 1 y 5.',
            'target_goals.required' => 'Debe seleccionar al menos un objetivo.',
            'priority.in' => 'Prioridad inválida.',
            'daily_assignments.required' => 'Debe incluir al menos una asignación diaria.',
            'daily_assignments.max' => 'No puede incluir más de 7 asignaciones diarias.',
            'daily_assignments.*.date.between' => 'La fecha debe estar entre el inicio y fin de semana.',
            'daily_assignments.*.daily_template_id.exists' => 'La plantilla diaria no existe.',
            'daily_assignments.*.estimated_duration.min' => 'La duración estimada debe ser al menos 10 minutos.',
            'daily_assignments.*.estimated_duration.max' => 'La duración estimada no puede exceder 180 minutos.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'usuario',
            'week_start' => 'inicio de semana',
            'week_end' => 'fin de semana',
            'source_type' => 'tipo de origen',
            'source_id' => 'plantilla origen',
            'difficulty_level' => 'nivel de dificultad',
            'target_goals' => 'objetivos',
            'auto_progress' => 'progresión automática',
            'send_reminders' => 'enviar recordatorios',
            'track_adherence' => 'rastrear adherencia',
            'daily_assignments' => 'asignaciones diarias',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir strings a boolean
        foreach (['auto_progress', 'send_reminders', 'track_adherence'] as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $this->merge([
                    $field => filter_var($this->$field, FILTER_VALIDATE_BOOLEAN)
                ]);
            }
        }

        // Calcular week_end automáticamente si no se proporciona
        if ($this->has('week_start') && !$this->has('week_end')) {
            $weekStart = Carbon::parse($this->week_start);
            $this->merge([
                'week_end' => $weekStart->copy()->endOfWeek()->format('Y-m-d')
            ]);
        }

        // Ordenar asignaciones diarias por fecha
        if ($this->has('daily_assignments') && is_array($this->daily_assignments)) {
            $assignments = collect($this->daily_assignments)->sortBy('date')->values()->toArray();
            $this->merge(['daily_assignments' => $assignments]);
        }

        // Procesar días de descanso
        if ($this->has('daily_assignments')) {
            $assignments = collect($this->daily_assignments)->map(function ($assignment) {
                if (isset($assignment['is_rest_day']) && is_string($assignment['is_rest_day'])) {
                    $assignment['is_rest_day'] = filter_var($assignment['is_rest_day'], FILTER_VALIDATE_BOOLEAN);
                }
                return $assignment;
            })->toArray();

            $this->merge(['daily_assignments' => $assignments]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que las fechas de asignaciones diarias estén dentro del rango de la semana
            if ($this->has('daily_assignments') && $this->has('week_start') && $this->has('week_end')) {
                $weekStart = Carbon::parse($this->week_start);
                $weekEnd = Carbon::parse($this->week_end);

                foreach ($this->daily_assignments as $index => $assignment) {
                    if (isset($assignment['date'])) {
                        $assignmentDate = Carbon::parse($assignment['date']);
                        if (!$assignmentDate->between($weekStart, $weekEnd)) {
                            $validator->errors()->add(
                                "daily_assignments.{$index}.date",
                                'La fecha debe estar dentro del rango de la semana.'
                            );
                        }
                    }
                }
            }

            // Validar que no haya fechas duplicadas
            if ($this->has('daily_assignments')) {
                $dates = collect($this->daily_assignments)->pluck('date')->filter();
                $duplicates = $dates->duplicates();
                
                if ($duplicates->isNotEmpty()) {
                    $validator->errors()->add(
                        'daily_assignments',
                        'No puede haber fechas duplicadas en las asignaciones diarias.'
                    );
                }
            }
        });
    }
}
