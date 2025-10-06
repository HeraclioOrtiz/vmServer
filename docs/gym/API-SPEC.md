# Gym Service - API Specification (Draft)

Este documento define endpoints propuestos para el servicio de gimnasios. Se asume autenticación con Sanctum y control de roles (`profesor`, `alumno`). Las rutas definitivas pueden ajustarse durante implementación.

## Convenciones
- Base Admin: `/admin/gym/*` (rol `profesor`).
- Base Móvil: `/gym/*` (rol `alumno`).
- Respuestas JSON; validación con Form Requests; paginación en listados.

---

## Admin - Ejercicios (Catálogo)

- GET `/admin/gym/exercises`
  - Query: `q`, `tags[]`, `equipment`, `muscle_group`, `page`.
  - 200: `{ data: Exercise[], meta: { pagination } }`

- POST `/admin/gym/exercises`
  - Body: `{ name, muscle_group, movement_pattern, equipment, difficulty, tags[], instructions, tempo }`
  - 201: `{ id, ... }`

- GET `/admin/gym/exercises/{id}`
  - 200: `{ ...Exercise }`

- PUT `/admin/gym/exercises/{id}`
  - Body: campos editables.
  - 200: `{ id, ... }`

- DELETE `/admin/gym/exercises/{id}`
  - 204

---

## Admin - Rutinas Diarias (Plantillas)

- GET `/admin/gym/daily-templates`
  - Query: `q`, `goal`, `level`, `tags[]`, `page`.
  - 200: `{ data: DailyTemplate[], meta }`

- POST `/admin/gym/daily-templates`
  - Body:
    ```json
    {
      "title": "Push 60' fuerza",
      "goal": "strength|hypertrophy|endurance",
      "estimated_duration_min": 60,
      "level": "beginner|intermediate|advanced",
      "tags": ["push","gym"],
      "exercises": [
        {
          "exercise_id": 123,
          "order": 1,
          "notes": "Pausa 2s en pecho",
          "sets": [
            { "set_number": 1, "reps_min": 6, "reps_max": 8, "rest_seconds": 120, "tempo": "3-0-1", "rpe_target": 8 }
          ]
        }
      ]
    }
    ```
  - 201: `{ id, ... }`

- GET `/admin/gym/daily-templates/{id}`
  - 200: `{ ...DailyTemplate with exercises & sets }`

- PUT `/admin/gym/daily-templates/{id}`
  - Body: mismo shape; versión nueva o actualización según política.
  - 200

- DELETE `/admin/gym/daily-templates/{id}`
  - 204 (si no referenciada por asignaciones actuales)

---

## Admin - Rutinas Semanales (Plantillas)

- GET `/admin/gym/weekly-templates`
  - Query: `q`, `goal`, `split`, `days_per_week`, `page`.
  - 200: `{ data: WeeklyTemplate[], meta }`

- POST `/admin/gym/weekly-templates`
  - Body:
    ```json
    {
      "title": "PPL Intermedio 6d",
      "goal": "hypertrophy",
      "split": "PPL",
      "days_per_week": 6,
      "days": [
        { "weekday": 1, "daily_template_id": 10 },
        { "weekday": 2, "daily_template_id": 11 }
      ]
    }
    ```
  - 201

- GET `/admin/gym/weekly-templates/{id}`
  - 200: `{ ...WeeklyTemplate with days }`

- PUT `/admin/gym/weekly-templates/{id}`
  - 200

- DELETE `/admin/gym/weekly-templates/{id}`
  - 204

---

## Admin - Asignaciones Semanales (a alumnos)

- GET `/admin/gym/assignments`
  - Query: `user_id`, `from`, `to`, `page`.
  - 200: `{ data: WeeklyAssignment[], meta }`

- POST `/admin/gym/assignments`
  - Body:
    ```json
    {
      "user_id": 1001,
      "week_start": "2025-09-22",
      "strategy": "from_weekly_template|manual|assistant",
      "weekly_template_id": 33,
      "days": [
        { "weekday": 1, "daily_template_id": 10 },
        { "weekday": 3, "daily_template_id": 12 }
      ]
    }
    ```
  - 201: `{ id, ...snapshot }`

- GET `/admin/gym/assignments/{id}`
  - 200: `{ ...WeeklyAssignment with days/exercises/sets }`

- PUT `/admin/gym/assignments/{id}` (overrides)
  - Body: cambios puntuales (reemplazar día, actualizar reps/series/descanso de un ejercicio).
  - 200

- POST `/admin/gym/assignments/{id}/duplicate`
  - Query: `progression=none|light|moderate`
  - 201: nueva asignación a semana siguiente con ajustes automáticos.

- DELETE `/admin/gym/assignments/{id}`
  - 204

---

## Admin - Asistente de armado

- POST `/admin/gym/assistant/weekly-proposal`
  - Body:
    ```json
    {
      "days_per_week": 4,
      "split": "UpperLower|FullBody|PPL",
      "goal": "strength|hypertrophy|endurance",
      "equipment": ["barbell","dumbbell","machine","bands","bodyweight"]
    }
    ```
  - 200: `{ suggested_days: [{ weekday, daily_template_id, rationale }...] }`

---

## Móvil - Alumno

- GET `/gym/my-week?date=YYYY-MM-DD`
  - 200: `{ week_start, week_end, days: [{weekday, has_session, title}] }`

- GET `/gym/my-day?date=YYYY-MM-DD`
  - 200:
    ```json
    {
      "title": "Push 60' fuerza",
      "exercises": [
        {
          "name": "Press banca",
          "order": 1,
          "sets": [ { "reps": "6-8", "rest_seconds": 120, "tempo": "3-0-1" } ],
          "notes": "Pausa 1s en pecho"
        }
      ]
    }
    ```

- (Futuro) POST `/gym/my-day/logs`
  - Registrar progreso de series.

---

## Errores comunes
- 401/403: autenticación/rol.
- 422: validación.
- 409: conflictos (referencias activas al borrar plantillas).
- 404: no encontrado.
