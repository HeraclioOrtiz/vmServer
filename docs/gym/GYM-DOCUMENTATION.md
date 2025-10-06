# 🏋️ Documentación del Sistema de Gimnasio

## 📋 **Arquitectura del Sistema**

El sistema de gimnasio está dividido en dos paneles principales:

### **🎯 Panel de Profesores** (`/api/admin/gym/*`)
- **Acceso**: Usuarios con `is_professor: true`
- **Middleware**: `professor`
- **Funcionalidades**: Gestión completa de ejercicios, plantillas y asignaciones

### **📱 Panel Móvil/Estudiantes** (`/api/gym/*`)
- **Acceso**: Usuarios autenticados (estudiantes)
- **Middleware**: `auth:sanctum`
- **Funcionalidades**: Consulta de rutinas asignadas

## 🔐 **Autenticación y Permisos**

### **Roles del Sistema**
```php
// Usuario Profesor
{
  "is_professor": true,
  "permissions": [
    "gym_admin",           // Acceso al panel del gimnasio
    "create_templates",    // Crear plantillas
    "assign_routines",     // Asignar rutinas a estudiantes
    "view_all_students"    // Ver todos los estudiantes (opcional)
  ]
}

// Usuario Estudiante
{
  "is_professor": false,
  // Solo puede ver sus propias asignaciones
}
```

### **Middleware Aplicado**
```php
// Rutas de profesores
Route::prefix('admin/gym')->middleware(['auth:sanctum', 'professor'])->group(function () {
    // Endpoints de gestión
});

// Rutas de estudiantes
Route::prefix('gym')->middleware('auth:sanctum')->group(function () {
    // Endpoints de consulta
});
```

## 📊 **Modelos y Relaciones**

### **Estructura de Base de Datos**
```
gym_exercises              # Catálogo de ejercicios
├── id, name, muscle_group, equipment, difficulty, instructions, tempo, tags

gym_daily_templates        # Plantillas de día
├── id, title, goal, duration_minutes, level, description, created_by
└── → gym_daily_template_exercises (pivot)

gym_weekly_templates       # Plantillas de semana  
├── id, title, split, days_per_week, description, created_by
└── → gym_weekly_template_days (pivot)

gym_weekly_assignments     # Asignaciones a estudiantes
├── id, user_id, week_start, week_end, created_by, source_type
├── → gym_daily_assignments
    └── → gym_assigned_exercises
        └── → gym_assigned_sets
```

### **Relaciones Eloquent**
```php
// User.php
public function createdAssignments() {
    return $this->hasMany(WeeklyAssignment::class, 'created_by');
}

public function receivedAssignments() {
    return $this->hasMany(WeeklyAssignment::class, 'user_id');
}

// WeeklyAssignment.php
public function user() {
    return $this->belongsTo(User::class);
}

public function creator() {
    return $this->belongsTo(User::class, 'created_by');
}

public function days() {
    return $this->hasMany(DailyAssignment::class);
}
```

## 🎯 **Panel de Profesores - Endpoints**

### **1. Gestión de Ejercicios**

#### **GET /api/admin/gym/exercises**
Lista ejercicios con filtros.

**Query Parameters:**
```
?q=press                    // Búsqueda por nombre, músculo o equipo
&muscle_group=chest         // Filtro por grupo muscular
&equipment=barbell          // Filtro por equipamiento
&per_page=20               // Paginación (default: 20)
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Press de Banca",
      "muscle_group": "chest",
      "movement_pattern": "push",
      "equipment": "barbell",
      "difficulty": "intermediate",
      "tags": ["compound", "strength"],
      "instructions": "Acostarse en el banco...",
      "tempo": "3-1-1",
      "created_at": "2025-09-18T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150
  }
}
```

#### **POST /api/admin/gym/exercises**
Crear nuevo ejercicio.

**Request Body:**
```json
{
  "name": "Press de Banca",
  "muscle_group": "chest",
  "movement_pattern": "push",
  "equipment": "barbell",
  "difficulty": "intermediate",
  "tags": ["compound", "strength"],
  "instructions": "Descripción detallada del ejercicio...",
  "tempo": "3-1-1"
}
```

#### **PUT /api/admin/gym/exercises/{id}**
Actualizar ejercicio existente.

#### **DELETE /api/admin/gym/exercises/{id}**
Eliminar ejercicio.

### **2. Gestión de Plantillas Diarias**

#### **GET /api/admin/gym/daily-templates**
Lista plantillas diarias.

**Query Parameters:**
```
?goal=strength              // Filtro por objetivo
&level=intermediate         // Filtro por nivel
&duration_min=30           // Duración mínima en minutos
&duration_max=90           // Duración máxima en minutos
&created_by=1              // Filtro por creador
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Entrenamiento de Pecho",
      "goal": "strength",
      "duration_minutes": 60,
      "level": "intermediate",
      "description": "Rutina enfocada en fuerza...",
      "exercises_count": 5,
      "created_by": {
        "id": 1,
        "name": "Prof. Juan Pérez"
      },
      "created_at": "2025-09-18T10:00:00Z"
    }
  ]
}
```

#### **POST /api/admin/gym/daily-templates**
Crear plantilla diaria.

**Request Body:**
```json
{
  "title": "Entrenamiento de Pecho",
  "goal": "strength",
  "duration_minutes": 60,
  "level": "intermediate",
  "description": "Rutina enfocada en fuerza de pecho",
  "exercises": [
    {
      "exercise_id": 1,
      "display_order": 1,
      "notes": "Calentar bien antes",
      "sets": [
        {
          "set_number": 1,
          "reps_min": 8,
          "reps_max": 12,
          "rest_seconds": 90,
          "rpe_target": 7.5
        }
      ]
    }
  ]
}
```

### **3. Gestión de Plantillas Semanales**

#### **GET /api/admin/gym/weekly-templates**
Lista plantillas semanales.

#### **POST /api/admin/gym/weekly-templates**
Crear plantilla semanal.

**Request Body:**
```json
{
  "title": "Rutina Push/Pull/Legs",
  "split": "ppl",
  "days_per_week": 6,
  "description": "División clásica de 6 días",
  "days": [
    {
      "weekday": 1,
      "daily_template_id": 1,
      "is_rest_day": false
    },
    {
      "weekday": 2,
      "daily_template_id": 2,
      "is_rest_day": false
    },
    {
      "weekday": 7,
      "is_rest_day": true
    }
  ]
}
```

### **4. Gestión de Asignaciones**

#### **GET /api/admin/gym/weekly-assignments**
Lista asignaciones semanales.

**Query Parameters:**
```
?user_id=1                 // Filtro por estudiante
&from=2025-09-01           // Fecha desde
&to=2025-09-30             // Fecha hasta
&created_by=1              // Filtro por profesor creador
&source_type=template      // Tipo de fuente
&status=active             // Estado de la asignación
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user": {
        "id": 2,
        "name": "Juan Estudiante",
        "avatar_url": null
      },
      "week_start": "2025-09-16",
      "week_end": "2025-09-22",
      "source_type": "template",
      "weekly_template_id": 1,
      "adherence_percentage": 85.5,
      "status": "active",
      "notes": "Rutina personalizada",
      "created_by": {
        "id": 1,
        "name": "Prof. Juan Pérez"
      },
      "created_at": "2025-09-15T10:00:00Z"
    }
  ]
}
```

#### **POST /api/admin/gym/weekly-assignments**
Crear nueva asignación.

**Request Body:**
```json
{
  "user_id": 2,
  "week_start": "2025-09-16",
  "week_end": "2025-09-22",
  "source_type": "manual",
  "weekly_template_id": null,
  "notes": "Rutina personalizada para principiante",
  "days": [
    {
      "weekday": 1,
      "date": "2025-09-16",
      "title": "Entrenamiento de Pecho",
      "exercises": [
        {
          "exercise_id": 1,
          "order": 1,
          "name": "Press de Banca",
          "muscle_group": "chest",
          "equipment": "barbell",
          "instructions": "Mantener la espalda pegada...",
          "sets": [
            {
              "set_number": 1,
              "reps_min": 8,
              "reps_max": 12,
              "rest_seconds": 90,
              "rpe_target": 7.5,
              "notes": "Serie de calentamiento"
            }
          ]
        }
      ]
    }
  ]
}
```

## 📱 **Panel Móvil/Estudiantes - Endpoints**

### **GET /api/gym/my-week**
Obtiene la rutina semanal del usuario autenticado.

**Query Parameters:**
```
?date=2025-09-18           // Fecha específica (opcional, default: hoy)
```

**Response:**
```json
{
  "week_start": "2025-09-16",
  "week_end": "2025-09-22",
  "days": [
    {
      "weekday": 1,
      "date": "2025-09-16",
      "has_session": true,
      "title": "Entrenamiento de Pecho"
    },
    {
      "weekday": 2,
      "date": "2025-09-17",
      "has_session": true,
      "title": "Entrenamiento de Espalda"
    },
    {
      "weekday": 3,
      "date": "2025-09-18",
      "has_session": false,
      "title": null
    }
  ]
}
```

### **GET /api/gym/my-day**
Obtiene la rutina del día específico.

**Query Parameters:**
```
?date=2025-09-18           // Fecha específica (opcional, default: hoy)
```

**Response:**
```json
{
  "title": "Entrenamiento de Pecho",
  "exercises": [
    {
      "name": "Press de Banca",
      "order": 1,
      "sets": [
        {
          "reps": "8-12",
          "rest_seconds": 90,
          "tempo": "3-1-1",
          "rpe_target": 7.5,
          "notes": "Serie de calentamiento"
        },
        {
          "reps": "6-8",
          "rest_seconds": 120,
          "tempo": "3-1-1",
          "rpe_target": 8.5,
          "notes": "Serie de trabajo"
        }
      ],
      "notes": "Mantener la espalda pegada al banco"
    }
  ]
}
```

**Response (Sin asignación):**
```json
{
  "message": "No assignment found for date"
}
```

## 🔧 **Validaciones y Reglas de Negocio**

### **Ejercicios**
```php
// Validaciones para crear/editar ejercicios
[
    'name' => 'required|string|max:255|unique:gym_exercises,name',
    'muscle_group' => 'nullable|string|max:255',
    'movement_pattern' => 'nullable|string|max:255',
    'equipment' => 'nullable|string|max:255',
    'difficulty' => 'nullable|in:beginner,intermediate,advanced',
    'tags' => 'array',
    'tags.*' => 'string|max:50',
    'instructions' => 'nullable|string|max:2000',
    'tempo' => 'nullable|string|regex:/^\d+-\d+-\d+$/',
]
```

### **Plantillas Diarias**
```php
[
    'title' => 'required|string|max:255',
    'goal' => 'required|in:strength,hypertrophy,endurance,mobility',
    'duration_minutes' => 'required|integer|min:15|max:180',
    'level' => 'required|in:beginner,intermediate,advanced',
    'description' => 'nullable|string|max:1000',
    'exercises' => 'required|array|min:1',
    'exercises.*.exercise_id' => 'required|exists:gym_exercises,id',
    'exercises.*.display_order' => 'required|integer|min:1',
    'exercises.*.sets' => 'required|array|min:1',
    'exercises.*.sets.*.reps_min' => 'nullable|integer|min:1|max:100',
    'exercises.*.sets.*.reps_max' => 'nullable|integer|min:1|max:100|gte:exercises.*.sets.*.reps_min',
]
```

### **Asignaciones Semanales**
```php
[
    'user_id' => 'required|exists:users,id',
    'week_start' => 'required|date',
    'week_end' => 'required|date|after:week_start',
    'source_type' => 'required|in:template,manual,assistant',
    'weekly_template_id' => 'nullable|exists:gym_weekly_templates,id',
    'days' => 'required_if:source_type,manual|array',
    'days.*.weekday' => 'required|integer|min:1|max:7',
    'days.*.date' => 'required|date|between:week_start,week_end',
]
```

## 🚨 **Manejo de Errores**

### **Errores Comunes**
```json
// Usuario no es profesor
{
  "message": "Forbidden. Professor role required.",
  "code": "INSUFFICIENT_PERMISSIONS"
}

// Asignación no encontrada
{
  "message": "No assignment found for date",
  "code": "ASSIGNMENT_NOT_FOUND"
}

// Conflicto de fechas
{
  "message": "User already has an assignment for this week period",
  "code": "ASSIGNMENT_CONFLICT",
  "conflicts": [
    {
      "id": 1,
      "week_start": "2025-09-16",
      "week_end": "2025-09-22"
    }
  ]
}

// Validación de ejercicio
{
  "message": "The given data was invalid.",
  "errors": {
    "exercises.0.sets.0.reps_max": [
      "The reps max must be greater than or equal to reps min."
    ]
  }
}
```

## 📊 **Métricas y Estadísticas**

### **Estadísticas de Profesor**
```php
// Método en User model
public function getProfessorStats(): array
{
    return [
        'students_count' => $this->createdAssignments()->distinct('user_id')->count(),
        'active_assignments' => $this->createdAssignments()
            ->where('week_end', '>=', now())->count(),
        'templates_created' => $this->createdDailyTemplates()->count() + 
                              $this->createdWeeklyTemplates()->count(),
        'total_assignments' => $this->createdAssignments()->count(),
    ];
}
```

### **Cálculo de Adherencia**
```php
// Lógica para calcular adherencia de estudiantes
public function calculateAdherence(WeeklyAssignment $assignment): float
{
    $totalSessions = $assignment->days()->count();
    $completedSessions = $assignment->days()
        ->whereNotNull('completed_at')->count();
    
    return $totalSessions > 0 ? ($completedSessions / $totalSessions) * 100 : 0;
}
```

## 🔄 **Estados y Flujos**

### **Estados de Asignación**
- **`active`**: Asignación en curso (week_end >= hoy)
- **`completed`**: Asignación finalizada (week_end < hoy)
- **`cancelled`**: Asignación cancelada por el profesor

### **Tipos de Fuente**
- **`template`**: Creada desde plantilla semanal
- **`manual`**: Creada manualmente por el profesor
- **`assistant`**: Generada por asistente IA (futuro)

### **Flujo de Creación de Asignación**
1. **Seleccionar estudiante** → Verificar permisos
2. **Configurar fechas** → Verificar conflictos
3. **Elegir método** → Template vs Manual
4. **Personalizar** → Ajustar ejercicios y series
5. **Confirmar** → Crear asignación en BD

## 🎨 **Consideraciones de Frontend**

### **Componentes Recomendados**
- **ExerciseSelector**: Selector con búsqueda y filtros
- **SetEditor**: Editor de series con validación
- **WeekCalendar**: Vista semanal de asignaciones
- **StudentPicker**: Selector de estudiantes
- **TemplateBuilder**: Constructor de plantillas drag & drop

### **Estados de UI**
- **Loading**: Skeletons para listas, spinners para formularios
- **Empty**: Ilustraciones para listas vacías
- **Error**: Toast notifications y retry buttons
- **Success**: Confirmaciones de acciones

**El sistema de gimnasio está diseñado para ser escalable, mantenible y fácil de usar tanto para profesores como para estudiantes.** 🏋️‍♂️
