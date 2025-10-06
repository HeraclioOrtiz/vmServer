# Gym Service - Technical Architecture

## Visión General

El servicio de gimnasios permite a profesores crear y asignar rutinas semanales a alumnos, quienes las consumen desde la app móvil. Implementa un modelo de **plantillas reutilizables** y **asignaciones inmutables** para garantizar consistencia.

## Arquitectura de Datos

### Entidades Principales

```
Catálogo (Reutilizable)
├── Exercise (ejercicios base)
├── DailyTemplate (sesiones reutilizables)
│   ├── DailyTemplateExercise (ejercicios ordenados)
│   └── DailyTemplateSet (series prescritas)
└── WeeklyTemplate (semanas reutilizables)
    └── WeeklyTemplateDay (mapeo día → plantilla diaria)

Asignaciones (Inmutables)
├── WeeklyAssignment (semana asignada a alumno)
│   └── DailyAssignment (día específico)
│       └── AssignedExercise (ejercicio con snapshot)
│           └── AssignedSet (serie prescrita)
```

### Flujo de Datos

1. **Profesor crea catálogo**: ejercicios → plantillas diarias → plantillas semanales
2. **Profesor asigna semana**: crea `WeeklyAssignment` con snapshot inmutable
3. **Alumno consume**: lee su `WeeklyAssignment` vigente sin modificar plantillas

## Componentes Técnicos

### Modelos Eloquent
- `app/Models/Gym/Exercise.php`
- `app/Models/Gym/DailyTemplate.php` + `DailyTemplateExercise.php` + `DailyTemplateSet.php`
- `app/Models/Gym/WeeklyTemplate.php` + `WeeklyTemplateDay.php`
- `app/Models/Gym/WeeklyAssignment.php` + `DailyAssignment.php` + `AssignedExercise.php` + `AssignedSet.php`

### Controladores
- **Admin**: `app/Http/Controllers/Gym/Admin/`
  - `ExerciseController.php` - CRUD ejercicios
  - `DailyTemplateController.php` - CRUD plantillas diarias
  - `WeeklyTemplateController.php` - CRUD plantillas semanales
  - `WeeklyAssignmentController.php` - asignaciones a alumnos
- **Móvil**: `app/Http/Controllers/Gym/Mobile/`
  - `MyPlanController.php` - consulta de rutinas del alumno

### Seguridad
- **Autenticación**: Sanctum (tokens Bearer)
- **Autorización**: Middleware `professor` para `/admin/gym/*`
- **Campo**: `users.is_professor` (boolean, indexado)

### Base de Datos
- **Catálogo**: `gym_exercises`, `gym_daily_templates`, `gym_weekly_templates`
- **Asignaciones**: `gym_weekly_assignments`, `gym_daily_assignments`, `gym_assigned_exercises`, `gym_assigned_sets`
- **Índices**: DNI+tipo, fechas, relaciones FK

## Flujos Operativos

### Flujo Admin (Profesor)

1. **Gestión de Catálogo**
   ```
   POST /admin/gym/exercises
   POST /admin/gym/daily-templates (con ejercicios+sets)
   POST /admin/gym/weekly-templates (con días)
   ```

2. **Asignación a Alumno**
   ```
   POST /admin/gym/weekly-assignments
   Body: user_id, week_start, week_end, days[]{ejercicios+sets}
   ```

3. **Gestión de Asignaciones**
   ```
   GET /admin/gym/weekly-assignments?user_id=X
   PUT /admin/gym/weekly-assignments/{id} (solo notas)
   DELETE /admin/gym/weekly-assignments/{id}
   ```

### Flujo Móvil (Alumno)

1. **Consulta Semana Vigente**
   ```
   GET /gym/my-week?date=YYYY-MM-DD
   Response: {week_start, week_end, days[]{weekday, has_session, title}}
   ```

2. **Consulta Día Específico**
   ```
   GET /gym/my-day?date=YYYY-MM-DD
   Response: {title, exercises[]{name, sets[]{reps, rest_seconds, tempo}}}
   ```

## Principios de Diseño

### Inmutabilidad de Asignaciones
- Las `WeeklyAssignment` guardan snapshot completo (ejercicios+sets)
- Cambios en plantillas NO afectan asignaciones ya creadas
- Garantiza consistencia para el alumno

### Reutilización de Plantillas
- `DailyTemplate`: 20 prefijadas + creadas por profesor
- `WeeklyTemplate`: combinaciones de plantillas diarias
- Reduce tiempo de armado de rutinas

### Performance
- Índices en consultas frecuentes (user_id, fechas, relaciones)
- Cache potencial en `my-week` por alumno
- Paginación en listados admin

### Escalabilidad
- Separación clara admin/móvil
- Snapshot evita dependencias complejas
- Estructura permite múltiples profesores/alumnos

## Testing

### Cobertura Actual
- **AdminAccessTest**: control de acceso por rol
- **AdminExerciseTest**: CRUD ejercicios
- **AdminDailyTemplateTest**: CRUD plantillas diarias
- **AdminWeeklyTemplateTest**: CRUD plantillas semanales
- **AdminWeeklyAssignmentTest**: asignaciones completas
- **MobileMyPlanTest**: consulta de rutinas

### Estrategia de Testing
- SQLite en memoria para aislamiento
- `RefreshDatabase` entre tests
- Factories para usuarios profesor/alumno
- Validación de estructura JSON y códigos HTTP

## Extensibilidad Futura

### Funcionalidades Planeadas
- **Asistente de armado**: sugerencias por split/objetivo/días
- **Duplicación con progresión**: clonar semana con +reps/+carga
- **Registro de progreso**: log de series realizadas por alumno
- **Métricas de adherencia**: % completitud, feedback

### Consideraciones Técnicas
- Versionado de plantillas para cambios mayores
- Políticas de ownership (profesor propietario vs global)
- Rate limiting en endpoints móviles
- Notificaciones push para rutinas del día
