# 📚 API ENDPOINTS - PANEL DE GIMNASIO

**Base URL:** `https://appvillamitre.surtekbb.com/api/admin/gym`

**Autenticación:** Bearer Token (Sanctum)

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 🏋️ **EJERCICIOS**

### **1. Listar Ejercicios**
```
GET /admin/gym/exercises
```

**Query Parameters:**
```typescript
{
  search?: string;              // Búsqueda por nombre/descripción
  muscle_groups?: string[];     // Filtro por grupos musculares
  target_muscle_groups?: string[]; // Filtro por músculos objetivo
  equipment?: string;           // Filtro por equipamiento
  difficulty_level?: 'beginner' | 'intermediate' | 'advanced';
  movement_pattern?: string;    // Patrón de movimiento
  tags?: string[];             // Filtro por tags
  sort_by?: 'name' | 'difficulty_level' | 'created_at';
  sort_direction?: 'asc' | 'desc';
  per_page?: number;           // Default: 20, Max: 100
}
```

**Respuesta:**
```typescript
{
  data: Exercise[];
  current_page: number;
  total: number;
  per_page: number;
}
```

---

### **2. Crear Ejercicio**
```
POST /admin/gym/exercises
```

**Body:**
```typescript
{
  name: string;                    // Requerido
  description?: string;
  muscle_groups?: string[];        // Array de grupos musculares
  target_muscle_groups?: string[]; // Array de músculos objetivo
  movement_pattern?: string;
  equipment?: string;
  difficulty_level?: 'beginner' | 'intermediate' | 'advanced';
  tags?: string[];
  instructions?: string;
}
```

---

### **3. Ver Ejercicio**
```
GET /admin/gym/exercises/{id}
```

---

### **4. Editar Ejercicio**
```
PUT /admin/gym/exercises/{id}
```

**Body:** Igual que crear, todos los campos son opcionales (`sometimes`)

---

### **5. Verificar Dependencias**
```
GET /admin/gym/exercises/{id}/dependencies
```

**Respuesta:**
```typescript
{
  can_delete: boolean;
  dependencies: {
    daily_templates: number;  // Cantidad de plantillas que lo usan
  };
  total_references: number;
  exercise: {
    id: number;
    name: string;
  };
}
```

---

### **6. Eliminar Ejercicio (Suave)**
```
DELETE /admin/gym/exercises/{id}
```

**Respuesta:** ❌ Error 422 si está en uso

---

### **7. Eliminar Ejercicio (Forzado) - ADMIN**
```
DELETE /admin/gym/exercises/{id}/force
```

**⚠️ IMPORTANTE:** Elimina el ejercicio + todas las plantillas que lo usan + desasigna automáticamente de estudiantes.

**Respuesta:**
```typescript
{
  success: true;
  message: "Ejercicio eliminado correctamente. Se eliminaron 3 plantilla(s) y sus asignaciones.";
  warning: "Esta acción eliminó 3 plantilla(s) que usaban este ejercicio y las desasignó de todos los estudiantes.";
  deleted_templates_count: 3;
}
```

---

### **8. Duplicar Ejercicio**
```
POST /admin/gym/exercises/{id}/duplicate
{
  search?: string;              // Búsqueda por título
  q?: string;                   // Alias de search
  difficulty?: string;
  start_date?: string;            // Fecha de inicio (formato: 'YYYY-MM-DD')
  end_date?: string;              // Fecha de fin (formato: 'YYYY-MM-DD')
  level?: string;               // Alias de difficulty
  primary_goal?: string;
  goal?: string;                // Alias de primary_goal
  target_muscle_groups?: string;
  equipment_needed?: string;
  tags?: string;
  intensity_level?: string;
  sort_by?: string;             // Default: 'created_at'
  sort_direction?: 'asc' | 'desc'; // Default: 'desc'
  is_preset?: boolean;          // Filtrar presets
  per_page?: number;            // Default: 20
  with_exercises?: boolean;     // Incluir ejercicios
  with_sets?: boolean;          // Incluir sets
  include?: string;             // 'exercises,exercises.sets,exercises.exercise'
}
```

---

### **2. Crear Plantilla**
```
POST /admin/gym/daily-templates
```

**Body:**
```typescript
{
  title: string;                    // Requerido
  goal?: string;                    // strength|hypertrophy|endurance
  estimated_duration_min?: number;  // 0-600
  level?: string;                   // beginner|intermediate|advanced
  tags?: string[];
  exercises?: {
    exercise_id?: number;           // ID del ejercicio
    order?: number;                 // Orden de visualización
    notes?: string;
    sets?: {
      set_number?: number;
      reps_min?: number;
      reps_max?: number;
      rest_seconds?: number;
      rpe_target?: number;          // 0-10
      weight_min?: number;          // 0-1000
      weight_max?: number;          // 0-1000
      weight_target?: number;       // 0-1000
      notes?: string;
    }[];
  }[];
}
```

---

### **3. Ver Plantilla**
```
GET /admin/gym/daily-templates/{id}
```

**Respuesta:** Incluye `exercises.sets` y `exercises.exercise`

---

### **4. Editar Plantilla**
```
PUT /admin/gym/daily-templates/{id}
```

**Body:** Igual que crear. Si envías `exercises`, reemplaza todos los ejercicios existentes.

---

### **5. Eliminar Plantilla**
```
DELETE /admin/gym/daily-templates/{id}
```

**⚠️ IMPORTANTE:** Automáticamente desasigna la plantilla de todos los estudiantes (cascade en BD).

**Respuesta:**
```
204 No Content
```

---

### **6. Duplicar Plantilla**
```
POST /admin/gym/daily-templates/{id}/duplicate
```

**Respuesta:** Nueva plantilla con título "(Copia)"

---

## ⚙️ **SETS (Series Individuales)**

### **1. Editar Set**
```
PUT /admin/gym/sets/{id}
```

**Body:**
```typescript
{
  set_number?: number;      // Número de serie
  reps_min?: number;        // Reps mínimas
  reps_max?: number;        // Reps máximas
  rest_seconds?: number;    // Descanso en segundos
  rpe_target?: number;      // RPE objetivo (0-10)
  weight_min?: number;      // Peso mínimo (kg)
  weight_max?: number;      // Peso máximo (kg)
  weight_target?: number;   // Peso objetivo (kg)
  notes?: string;           // Notas adicionales
}
```

**Respuesta:**
```typescript
{
  id: number;
  daily_template_exercise_id: number;
  set_number: number;
  reps_min: number | null;
  reps_max: number | null;
  rest_seconds: number | null;
  rpe_target: number | null;
  weight_min: number | null;
  weight_max: number | null;
  weight_target: number | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
}
```

---

### **2. Eliminar Set**
```
DELETE /admin/gym/sets/{id}
```

**Respuesta:**
```typescript
{
  message: "Set eliminado correctamente";
}
```

---

## 👨‍🏫 **ASIGNACIONES DE PLANTILLAS (PROFESOR)**

### **1. Asignar Plantilla a Estudiante**
```
POST /professor/assign-template
```

**Body:**
```typescript
{
  professor_student_assignment_id: number;  // ID de la asignación profesor-estudiante
  daily_template_id: number;                // ID de la plantilla a asignar
  start_date: string;                       // Fecha de inicio (YYYY-MM-DD)
  end_date?: string;                        // Fecha de fin opcional (YYYY-MM-DD)
  frequency: number[];                      // Días de la semana [0-6] (0=Domingo, 6=Sábado)
  professor_notes?: string;                 // Notas del profesor (max 1000 chars)
}
```

**Ejemplo:**
```json
{
  "professor_student_assignment_id": 1,
  "daily_template_id": 5,
  "start_date": "2025-10-15",
  "end_date": "2025-11-15",
  "frequency": [1, 3, 5],  // Lunes, Miércoles, Viernes
  "professor_notes": "Enfócate en la técnica antes que el peso"
}
```

---

### **2. Ver Detalles de Asignación**
```
GET /professor/assignments/{id}
```

**Respuesta:** Incluye `dailyTemplate.exercises.sets`, `professorStudentAssignment.student`, `progress[]`

---

### **3. Actualizar Asignación**
```
PUT /professor/assignments/{id}
```

**Body:**
```typescript
{
  end_date?: string;                        // Extender o acortar periodo
  frequency?: number[];                     // Cambiar días de entrenamiento
  professor_notes?: string;                 // Actualizar notas
  status?: 'active' | 'paused' | 'completed' | 'cancelled';
}
```

---

### **4. Desasignar/Eliminar Plantilla (NUEVO)**
```
DELETE /professor/assignments/{id}
```

**⚠️ IMPORTANTE:** Elimina completamente la asignación y todo su progreso (cascade).

**Respuesta:**
```typescript
{
  message: "Plantilla 'Upper Body Strength' desasignada exitosamente de Juan Pérez";
  student_name: "Juan Pérez";
  template_title: "Upper Body Strength";
}
```

---

### **5. Ver Progreso de Estudiante**
```
GET /professor/students/{studentId}/progress
```

---

### **6. Agregar Feedback a Sesión**
```
POST /professor/progress/{progressId}/feedback
```

**Body:**
```typescript
{
  professor_feedback: string;    // Requerido, max 1000 chars
  overall_rating?: number;       // Opcional, 1-5
}
```

---

### **7. Sesiones de Hoy**
```
GET /professor/today-sessions
```

---

### **8. Calendario Semanal**
```
GET /professor/weekly-calendar?start_date=2025-10-15
```

---

## 🔐 **PERMISOS**

Todas las rutas están protegidas con middleware `professor`:

```php
// Pueden acceder:
- is_admin = true
- is_super_admin = true  
- is_professor = true

// NO pueden acceder:
- Usuarios regulares
```

---

## 📊 **ESTRUCTURA DE DATOS**

### **Exercise**
```typescript
interface Exercise {
  id: number;
  name: string;
  description: string | null;
  muscle_groups: string[];          // Array JSON
  target_muscle_groups: string[];   // Array JSON
  movement_pattern: string | null;
  equipment: string | null;
  difficulty_level: 'beginner' | 'intermediate' | 'advanced' | null;
  tags: string[];                   // Array JSON
  instructions: string | null;
  created_at: string;
  updated_at: string;
}
```

### **DailyTemplate**
```typescript
interface DailyTemplate {
  id: number;
  created_by: number | null;
  title: string;
  goal: string | null;
  estimated_duration_min: number | null;
  level: string | null;
  tags: string[];                   // Array JSON
  is_preset: boolean;
  created_at: string;
  updated_at: string;
  exercises?: DailyTemplateExercise[];
}
```

### **DailyTemplateExercise**
```typescript
interface DailyTemplateExercise {
  id: number;
  daily_template_id: number;
  exercise_id: number;
  display_order: number;
  notes: string | null;
  created_at: string;
  updated_at: string;
  exercise?: Exercise;              // Eager loading
  sets?: DailyTemplateSet[];        // Eager loading
}
```

### **DailyTemplateSet**
```typescript
interface DailyTemplateSet {
  id: number;
  daily_template_exercise_id: number;
  set_number: number;
  reps_min: number | null;
  reps_max: number | null;
  rest_seconds: number | null;
  rpe_target: number | null;        // Decimal 0.00-10.00
  weight_min: number | null;        // Decimal kg
  weight_max: number | null;        // Decimal kg
  weight_target: number | null;     // Decimal kg
  notes: string | null;
  created_at: string;
  updated_at: string;
}
```

---

## 🚨 **MANEJO DE ERRORES**

### **422 Validation Error**
```typescript
{
  message: "The given data was invalid.";
  errors: {
    field_name: ["Error message"];
  };
}
```

### **403 Forbidden**
```typescript
{
  message: "No tienes permisos para realizar eliminación forzada";
  error: "INSUFFICIENT_PERMISSIONS";
}
```

### **404 Not Found**
```typescript
{
  message: "No query results for model [Exercise] {id}";
}
```

### **500 Internal Server Error**
```typescript
{
  success: false;
  error: "DELETE_FAILED";
  message: "Error al eliminar el ejercicio";
  details: {
    exercise_id: number;
    exercise_name: string;
  };
}
```

---

## 🎯 **FLUJO RECOMENDADO**

### **Crear Plantilla Completa:**
1. `POST /exercises` → Crear ejercicios necesarios
2. `POST /daily-templates` → Crear plantilla con ejercicios y sets
3. Frontend recibe plantilla completa con IDs

### **Editar Plantilla:**
**Opción A (Recomendada):** Editar plantilla completa
```
PUT /daily-templates/{id}
Body: { exercises: [...] } // Reemplaza todo
```

**Opción B:** Editar sets individuales
```
PUT /sets/{set_id}
Body: { reps_min: 10, reps_max: 12 }
```

### **Eliminar Ejercicio:**
1. `GET /exercises/{id}/dependencies` → Ver cuántas plantillas lo usan
2. Mostrar warning al usuario
3. `DELETE /exercises/{id}/force` → Eliminación forzada (solo admin)

---

## 💡 **TIPS PARA FRONTEND**

1. **Eager Loading:** Usa `include=exercises,exercises.sets,exercises.exercise` para obtener datos completos
2. **Paginación:** Siempre respeta `per_page` para evitar sobrecarga
3. **Validación:** Los arrays (`muscle_groups`, `tags`, etc.) deben enviarse como arrays JSON, no strings
4. **Cascade:** Al eliminar plantillas, las asignaciones se eliminan automáticamente (no necesitas hacer nada extra)
5. **Auditoría:** Todas las acciones se registran automáticamente en `audit_logs`

---

## 📞 **SOPORTE**

Para dudas o problemas:
- Revisar logs: `storage/logs/laravel-{fecha}.log`
- Endpoint de health check: `GET /api/health`
- Documentación completa: `/docs/resumeneliminar/`
