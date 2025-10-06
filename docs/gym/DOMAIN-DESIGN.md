# Gym Service - Domain Design

Este documento define el dominio del servicio de gimnasios para soportar rutinas semanales (microciclos) compuestas por rutinas diarias (sesiones) con ejercicios y prescripciones de series, repeticiones, descansos y tempo.

## Objetivos
- Proveer a profesores una herramienta para armar semanas de entrenamiento rápidamente.
- Permitir reutilizar plantillas diarias/semanales y realizar overrides por alumno.
- Entregar a la app móvil del alumno su semana vigente y el detalle diario.

## Entidades principales
- Usuario (Alumno)
  - Ya existe en `users`. Se vincula por `user_id`.

- Ejercicio (Catálogo)
  - Atributos: `name`, `muscle_group`, `movement_pattern`, `equipment`, `difficulty`, `tags[]`, `instructions`, `tempo`.
  - Usado por plantillas diarias.

- Plantilla diaria (DailyTemplate)
  - Representa una sesión reusable.
  - Contiene lista ordenada de ejercicios con prescripción.
  - `title`, `goal`, `estimated_duration_min`, `level`, `tags[]`.

- Plantilla semanal (WeeklyTemplate)
  - Mapea 7 días (L-D) a `DailyTemplate` (algunos días pueden estar vacíos).
  - `title`, `goal`, `split` (e.g., PPL, Upper/Lower, Full-Body), `days_per_week`.

- Asignación semanal (WeeklyAssignment)
  - Asigna una semana a un alumno con `week_start` (lunes) y `week_end`.
  - Puede derivar de plantilla semanal o construirse manualmente.
  - Guarda snapshot de los días y sus ejercicios (para no depender de futuras ediciones de plantilla).

- Día asignado (DailyAssignment)
  - Día específico dentro de la semana asignada.
  - Contiene orden de ejercicios y prescripciones (snapshot u overrides).

- Ejercicio asignado (AssignedExercise)
  - Un ejercicio dentro de un día asignado.
  - Datos de prescripción a nivel ejercicio (si no se desglosan por serie).

- Serie prescrita (AssignedSet)
  - Detalle de la serie: `set_number`, `reps` (o rango), `rest_seconds`, `tempo`, `rpe_target`, `notes`.

- Overrides
  - Ajustes puntuales en `DailyAssignment` o `AssignedExercise/AssignedSet` sin alterar plantillas.

## Relaciones (alto nivel)
- `users` 1—N `weekly_assignments`
- `weekly_assignments` 1—N `daily_assignments`
- `daily_assignments` 1—N `assigned_exercises`
- `assigned_exercises` 1—N `assigned_sets`
- Catálogo:
  - Profesor N—N `daily_templates`
  - `weekly_templates` 1—7 `daily_templates`
  - `daily_templates` 1—N `daily_template_exercises` 1—N `daily_template_sets`

## Reglas de dominio
- Las asignaciones guardan snapshot para garantizar inmutabilidad de semanas ya entregadas.
- Plantillas son versionadas: cambios crean nueva versión.
- Un profesor sólo puede editar sus plantillas (o plantillas globales según política).
- Días inactivos en la semana: permitidos (no todos los 7 días deben tener sesión).

## Productividad para profesor
- Librería de 20 plantillas diarias prefijadas (semilla) para armado rápido.
- Asistente: split + objetivo + días/semana => propuesta de semana base.
- Duplicar semana anterior con progresión configurable (reps/carga/series).
- Sustituciones inteligentes por equipo disponible.

## Observabilidad
- Log de creación/edición de plantillas y asignaciones.
- Métricas de uso (tiempo de armado, adherencia futura).

## Seguridad / Roles
- Rol `profesor`: acceso a endpoints `/admin/gym/*`.
- Rol `alumno`: acceso a `/gym/*` propios.
- Autorización con Sanctum.
