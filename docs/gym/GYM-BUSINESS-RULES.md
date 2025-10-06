# 🏋️ Reglas de Negocio del Sistema de Gimnasio

## 📋 **Validaciones de Datos**

### **Ejercicios**
```php
// Validaciones para crear/editar ejercicios
$rules = [
    'name' => 'required|string|max:255|unique:gym_exercises,name,' . $exerciseId,
    'muscle_group' => 'nullable|string|max:100|in:chest,back,shoulders,arms,legs,core,cardio,full_body',
    'movement_pattern' => 'nullable|string|max:50|in:push,pull,squat,hinge,lunge,carry,rotation',
    'equipment' => 'nullable|string|max:100|in:barbell,dumbbell,machine,cable,bodyweight,kettlebell,resistance_band',
    'difficulty' => 'nullable|in:beginner,intermediate,advanced',
    'tags' => 'array|max:10',
    'tags.*' => 'string|max:50|distinct',
    'instructions' => 'nullable|string|max:2000',
    'tempo' => 'nullable|string|regex:/^\d+-\d+-\d+(-\d+)?$/',
    'video_url' => 'nullable|url|max:500',
    'image_url' => 'nullable|url|max:500',
];
```

### **Plantillas Diarias**
```php
$rules = [
    'title' => 'required|string|max:255',
    'goal' => 'required|in:strength,hypertrophy,endurance,mobility,rehabilitation',
    'duration_minutes' => 'required|integer|min:15|max:180',
    'level' => 'required|in:beginner,intermediate,advanced',
    'description' => 'nullable|string|max:1000',
    'exercises' => 'required|array|min:1|max:15',
    'exercises.*.exercise_id' => 'required|exists:gym_exercises,id',
    'exercises.*.display_order' => 'required|integer|min:1|distinct',
    'exercises.*.notes' => 'nullable|string|max:500',
    'exercises.*.sets' => 'required|array|min:1|max:10',
    'exercises.*.sets.*.set_number' => 'required|integer|min:1|distinct',
    'exercises.*.sets.*.reps_min' => 'nullable|integer|min:1|max:100',
    'exercises.*.sets.*.reps_max' => 'nullable|integer|min:1|max:100|gte:exercises.*.sets.*.reps_min',
    'exercises.*.sets.*.rest_seconds' => 'nullable|integer|min:0|max:600',
    'exercises.*.sets.*.tempo' => 'nullable|string|regex:/^\d+-\d+-\d+(-\d+)?$/',
    'exercises.*.sets.*.rpe_target' => 'nullable|numeric|min:1|max:10',
    'exercises.*.sets.*.notes' => 'nullable|string|max:200',
];
```

### **Plantillas Semanales**
```php
$rules = [
    'title' => 'required|string|max:255',
    'split' => 'required|in:full_body,upper_lower,push_pull_legs,body_part,custom',
    'days_per_week' => 'required|integer|min:1|max:7',
    'description' => 'nullable|string|max:1000',
    'days' => 'required|array|min:1|max:7',
    'days.*.weekday' => 'required|integer|min:1|max:7|distinct',
    'days.*.daily_template_id' => 'nullable|exists:gym_daily_templates,id',
    'days.*.is_rest_day' => 'required|boolean',
    'days.*.notes' => 'nullable|string|max:500',
];
```

### **Asignaciones Semanales**
```php
$rules = [
    'user_id' => 'required|exists:users,id',
    'week_start' => 'required|date|after_or_equal:today',
    'week_end' => 'required|date|after:week_start|before_or_equal:' . now()->addMonths(3)->toDateString(),
    'source_type' => 'required|in:template,manual,assistant',
    'weekly_template_id' => 'nullable|exists:gym_weekly_templates,id|required_if:source_type,template',
    'notes' => 'nullable|string|max:1000',
    'days' => 'required_if:source_type,manual|array|max:7',
    'days.*.weekday' => 'required|integer|min:1|max:7|distinct',
    'days.*.date' => 'required|date|between:week_start,week_end',
    'days.*.title' => 'required|string|max:255',
    'days.*.notes' => 'nullable|string|max:500',
    'days.*.exercises' => 'required|array|min:1|max:15',
];
```

## 🔒 **Reglas de Autorización**

### **Permisos por Rol**

#### **Profesor**
```php
// Puede hacer:
- Crear, editar, eliminar ejercicios
- Crear, editar, eliminar plantillas (propias)
- Ver plantillas públicas de otros profesores
- Crear, editar, eliminar asignaciones a sus estudiantes
- Ver estadísticas de sus estudiantes
- Exportar datos de sus estudiantes

// NO puede hacer:
- Editar plantillas de otros profesores (excepto si son colaborativas)
- Ver asignaciones de otros profesores
- Acceder a datos de estudiantes no asignados
- Modificar configuración global del sistema
```

#### **Estudiante**
```php
// Puede hacer:
- Ver sus propias asignaciones semanales
- Ver sus rutinas diarias
- Marcar ejercicios como completados
- Ver su historial de entrenamientos
- Ver estadísticas personales

// NO puede hacer:
- Ver asignaciones de otros estudiantes
- Crear o editar ejercicios/plantillas
- Acceder al panel de profesores
- Modificar asignaciones
```

#### **Administrador**
```php
// Puede hacer todo lo anterior más:
- Gestionar usuarios y profesores
- Ver todas las asignaciones del sistema
- Acceder a reportes globales
- Configurar parámetros del sistema
- Gestionar permisos de profesores
```

### **Validaciones de Negocio**

#### **Conflictos de Asignación**
```php
public function checkAssignmentConflicts(int $userId, string $weekStart, string $weekEnd, ?int $excludeId = null): array
{
    return WeeklyAssignment::where('user_id', $userId)
        ->where(function ($query) use ($weekStart, $weekEnd) {
            $query->whereBetween('week_start', [$weekStart, $weekEnd])
                  ->orWhereBetween('week_end', [$weekStart, $weekEnd])
                  ->orWhere(function ($subQuery) use ($weekStart, $weekEnd) {
                      $subQuery->where('week_start', '<=', $weekStart)
                               ->where('week_end', '>=', $weekEnd);
                  });
        })
        ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
        ->get()
        ->toArray();
}
```

#### **Límites de Profesor**
```php
public function validateProfessorLimits(User $professor, array $data): array
{
    $errors = [];
    
    // Límite de estudiantes por profesor
    $maxStudents = $professor->permissions['max_students'] ?? 50;
    $currentStudents = $professor->createdAssignments()
        ->where('week_end', '>=', now())
        ->distinct('user_id')
        ->count();
    
    if ($currentStudents >= $maxStudents) {
        $errors[] = "Has alcanzado el límite máximo de {$maxStudents} estudiantes.";
    }
    
    // Límite de plantillas por profesor
    $maxTemplates = 100;
    $currentTemplates = $professor->createdDailyTemplates()->count() + 
                       $professor->createdWeeklyTemplates()->count();
    
    if ($currentTemplates >= $maxTemplates) {
        $errors[] = "Has alcanzado el límite máximo de {$maxTemplates} plantillas.";
    }
    
    return $errors;
}
```

#### **Validación de Fechas**
```php
public function validateAssignmentDates(array $data): array
{
    $errors = [];
    
    $weekStart = Carbon::parse($data['week_start']);
    $weekEnd = Carbon::parse($data['week_end']);
    
    // La semana debe empezar en lunes
    if ($weekStart->dayOfWeek !== Carbon::MONDAY) {
        $errors[] = 'La semana debe comenzar en lunes.';
    }
    
    // La semana debe terminar en domingo
    if ($weekEnd->dayOfWeek !== Carbon::SUNDAY) {
        $errors[] = 'La semana debe terminar en domingo.';
    }
    
    // Duración exacta de 7 días
    if ($weekStart->diffInDays($weekEnd) !== 6) {
        $errors[] = 'La asignación debe durar exactamente 7 días.';
    }
    
    // No más de 3 meses en el futuro
    if ($weekStart->gt(now()->addMonths(3))) {
        $errors[] = 'No se pueden crear asignaciones con más de 3 meses de anticipación.';
    }
    
    return $errors;
}
```

## 📊 **Cálculos y Métricas**

### **Adherencia de Estudiantes**
```php
public function calculateAdherence(WeeklyAssignment $assignment): float
{
    $totalSessions = $assignment->days()
        ->whereHas('exercises')
        ->count();
    
    if ($totalSessions === 0) {
        return 0;
    }
    
    $completedSessions = $assignment->days()
        ->whereNotNull('completed_at')
        ->count();
    
    return round(($completedSessions / $totalSessions) * 100, 2);
}
```

### **Estadísticas de Profesor**
```php
public function getProfessorStats(User $professor): array
{
    $activeAssignments = $professor->createdAssignments()
        ->where('week_end', '>=', now());
    
    return [
        'students_count' => $activeAssignments->distinct('user_id')->count(),
        'active_assignments' => $activeAssignments->count(),
        'templates_created' => $professor->createdDailyTemplates()->count() + 
                              $professor->createdWeeklyTemplates()->count(),
        'total_assignments' => $professor->createdAssignments()->count(),
        'avg_adherence' => $this->calculateAverageAdherence($professor),
        'this_week_sessions' => $this->getThisWeekSessions($professor),
        'completion_rate' => $this->getCompletionRate($professor),
    ];
}

private function calculateAverageAdherence(User $professor): float
{
    $assignments = $professor->createdAssignments()
        ->where('week_end', '<', now())
        ->get();
    
    if ($assignments->isEmpty()) {
        return 0;
    }
    
    $totalAdherence = $assignments->sum(function ($assignment) {
        return $this->calculateAdherence($assignment);
    });
    
    return round($totalAdherence / $assignments->count(), 2);
}
```

### **Progreso de Estudiante**
```php
public function getStudentProgress(User $student, int $weeks = 12): array
{
    $assignments = $student->receivedAssignments()
        ->where('week_start', '>=', now()->subWeeks($weeks))
        ->orderBy('week_start')
        ->get();
    
    return [
        'total_weeks' => $assignments->count(),
        'completed_weeks' => $assignments->where('week_end', '<', now())->count(),
        'adherence_trend' => $this->getAdherenceTrend($assignments),
        'favorite_exercises' => $this->getFavoriteExercises($student),
        'workout_frequency' => $this->getWorkoutFrequency($assignments),
        'progress_notes' => $this->getProgressNotes($assignments),
    ];
}
```

## 🚨 **Estados y Transiciones**

### **Estados de Asignación**
```php
enum AssignmentStatus: string
{
    case DRAFT = 'draft';           // Borrador (no publicada)
    case ACTIVE = 'active';         // Activa (en curso)
    case COMPLETED = 'completed';   // Completada (finalizada)
    case CANCELLED = 'cancelled';   // Cancelada por profesor
    case PAUSED = 'paused';         // Pausada temporalmente
}
```

### **Transiciones Válidas**
```php
public function canTransitionTo(AssignmentStatus $newStatus): bool
{
    return match ([$this->status, $newStatus]) {
        [AssignmentStatus::DRAFT, AssignmentStatus::ACTIVE] => true,
        [AssignmentStatus::DRAFT, AssignmentStatus::CANCELLED] => true,
        [AssignmentStatus::ACTIVE, AssignmentStatus::COMPLETED] => $this->week_end < now(),
        [AssignmentStatus::ACTIVE, AssignmentStatus::CANCELLED] => true,
        [AssignmentStatus::ACTIVE, AssignmentStatus::PAUSED] => true,
        [AssignmentStatus::PAUSED, AssignmentStatus::ACTIVE] => true,
        [AssignmentStatus::PAUSED, AssignmentStatus::CANCELLED] => true,
        default => false,
    };
}
```

## 🔄 **Flujos de Trabajo**

### **Creación de Asignación**
```
1. Seleccionar Estudiante
   ├── Verificar permisos del profesor
   ├── Validar que el estudiante no sea profesor
   └── Verificar límites de estudiantes

2. Configurar Fechas
   ├── Validar formato de semana (lunes a domingo)
   ├── Verificar conflictos existentes
   └── Validar límites temporales (max 3 meses)

3. Elegir Método
   ├── Desde Plantilla → Validar acceso a plantilla
   ├── Manual → Validar ejercicios seleccionados
   └── Asistente IA → Validar parámetros de generación

4. Personalizar Contenido
   ├── Ajustar ejercicios y series
   ├── Agregar notas específicas
   └── Validar coherencia de la rutina

5. Confirmar y Crear
   ├── Validación final de datos
   ├── Crear registros en BD (transaccional)
   ├── Enviar notificación al estudiante
   └── Log de auditoría
```

### **Completar Sesión de Entrenamiento**
```
1. Estudiante accede a rutina del día
2. Marca ejercicios como completados
3. Registra pesos/repeticiones reales (opcional)
4. Agrega notas de la sesión (opcional)
5. Confirma finalización de la sesión
6. Sistema calcula adherencia actualizada
7. Notifica al profesor (si configurado)
```

## ⚠️ **Restricciones y Limitaciones**

### **Límites del Sistema**
```php
const LIMITS = [
    'max_exercises_per_template' => 15,
    'max_sets_per_exercise' => 10,
    'max_students_per_professor' => 50,
    'max_templates_per_professor' => 100,
    'max_assignments_per_week' => 1,
    'max_future_weeks' => 12,
    'max_exercise_name_length' => 255,
    'max_instructions_length' => 2000,
    'max_notes_length' => 500,
    'min_rest_seconds' => 0,
    'max_rest_seconds' => 600,
    'min_reps' => 1,
    'max_reps' => 100,
    'min_rpe' => 1,
    'max_rpe' => 10,
];
```

### **Reglas de Eliminación**
```php
// No se puede eliminar si:
- Ejercicio está siendo usado en plantillas activas
- Plantilla está siendo usada en asignaciones futuras
- Asignación está en curso (solo se puede cancelar)
- Profesor tiene estudiantes con asignaciones activas

// Eliminación en cascada:
- Al eliminar profesor → reasignar estudiantes a otro profesor
- Al eliminar plantilla → mantener asignaciones existentes como "manual"
- Al eliminar ejercicio → reemplazar en plantillas con ejercicio similar
```

### **Validaciones de Integridad**
```php
public function validateDataIntegrity(): array
{
    $errors = [];
    
    // Verificar asignaciones huérfanas
    $orphanedAssignments = WeeklyAssignment::whereDoesntHave('user')->count();
    if ($orphanedAssignments > 0) {
        $errors[] = "Encontradas {$orphanedAssignments} asignaciones sin usuario válido.";
    }
    
    // Verificar ejercicios sin plantillas
    $unusedExercises = Exercise::whereDoesntHave('templateExercises')->count();
    if ($unusedExercises > 50) {
        $errors[] = "Hay {$unusedExercises} ejercicios sin usar. Considerar limpieza.";
    }
    
    // Verificar coherencia de fechas
    $invalidDates = WeeklyAssignment::where('week_start', '>', 'week_end')->count();
    if ($invalidDates > 0) {
        $errors[] = "Encontradas {$invalidDates} asignaciones con fechas inválidas.";
    }
    
    return $errors;
}
```

**Estas reglas de negocio aseguran la integridad, consistencia y usabilidad del sistema de gimnasio, proporcionando una experiencia robusta tanto para profesores como para estudiantes.** 🏋️‍♂️
