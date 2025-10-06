<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProfessorAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->canManageUsers();
    }

    public function rules(): array
    {
        return [
            'qualifications' => 'required|array|min:1',
            'qualifications.*.type' => 'required|string|in:certification,experience,education,other',
            'qualifications.*.title' => 'required|string|max:255',
            'qualifications.*.institution' => 'nullable|string|max:255',
            'qualifications.*.date_obtained' => 'nullable|date|before_or_equal:today',
            'qualifications.*.description' => 'nullable|string|max:500',
            'qualifications.*.verified' => 'boolean',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|in:strength,cardio,flexibility,rehabilitation,nutrition,personal_training',
            'max_students' => 'nullable|integer|min:1|max:100',
            'hourly_rate' => 'nullable|numeric|min:0|max:10000',
            'availability' => 'nullable|array',
            'availability.*.day' => 'required_with:availability|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'availability.*.start_time' => 'required_with:availability|date_format:H:i',
            'availability.*.end_time' => 'required_with:availability|date_format:H:i|after:availability.*.start_time',
            'notes' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'qualifications.required' => 'Debe proporcionar al menos una calificación.',
            'qualifications.*.type.in' => 'Tipo de calificación inválido.',
            'qualifications.*.title.required' => 'El título de la calificación es obligatorio.',
            'specializations.*.in' => 'Especialización inválida.',
            'max_students.max' => 'El máximo de estudiantes no puede exceder 100.',
            'hourly_rate.max' => 'La tarifa por hora no puede exceder $10,000.',
            'availability.*.day.in' => 'Día de la semana inválido.',
            'availability.*.end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'qualifications.*.type' => 'tipo de calificación',
            'qualifications.*.title' => 'título de calificación',
            'qualifications.*.institution' => 'institución',
            'qualifications.*.date_obtained' => 'fecha de obtención',
            'max_students' => 'máximo de estudiantes',
            'hourly_rate' => 'tarifa por hora',
            'start_date' => 'fecha de inicio',
        ];
    }
}
