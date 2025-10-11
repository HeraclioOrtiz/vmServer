# 📊 TIPOS DE DATOS - PLANTILLAS DIARIAS Y SETS

**Documento Técnico Detallado**  
**Fecha:** 11 de Octubre 2025  
**Sistema:** Villa Mitre - Panel de Gimnasio

---

## 🎯 **PLANTILLAS DIARIAS (DailyTemplate)**

### **CREAR PLANTILLA**
**Endpoint:** `POST /admin/gym/daily-templates`

```typescript
interface CreateDailyTemplateRequest {
  // CAMPOS OBLIGATORIOS
  title: string;                    // max: 255 caracteres
  
  // CAMPOS OPCIONALES - Metadatos
  goal?: string;                    // max: 50 caracteres (ej: "strength", "hypertrophy", "endurance")
  estimated_duration_min?: number;  // integer, min: 0, max: 600 (minutos)
  level?: string;                   // max: 50 caracteres (ej: "beginner", "intermediate", "advanced")
  tags?: string[];                  // array de strings
  
  // CAMPOS OPCIONALES - Ejercicios
  exercises?: ExerciseTemplate[];   // array de ejercicios
}

interface ExerciseTemplate {
  exercise_id?: number;             // integer, debe existir en gym_exercises
  order?: number;                   // integer, min: 1 (orden de visualización)
  notes?: string;                   // texto libre
  sets?: SetTemplate[];             // array de sets/series
}

interface SetTemplate {
  set_number?: number;              // integer, min: 1
  reps_min?: number;                // integer, min: 1 (repeticiones mínimas)
  reps_max?: number;                // integer, min: 1 (repeticiones máximas)
  rest_seconds?: number;            // integer, min: 0 (descanso en segundos)
  rpe_target?: number;              // numeric (float), min: 0, max: 10 (esfuerzo percibido)
  notes?: string;                   // texto libre
  
  // CAMPOS DE PESO (agregados posteriormente)
  weight_min?: number;              // numeric (float), min: 0, max: 1000 (kg)
  weight_max?: number;              // numeric (float), min: 0, max: 1000 (kg)
  weight_target?: number;           // numeric (float), min: 0, max: 1000 (kg)
}
```

---

### **EDITAR PLANTILLA**
**Endpoint:** `PUT /admin/gym/daily-templates/{id}`

```typescript
interface UpdateDailyTemplateRequest {
  // TODOS LOS CAMPOS SON OPCIONALES (sometimes)
  title?: string;                   // max: 255 caracteres
  goal?: string;                    // max: 50 caracteres
  estimated_duration_min?: number;  // integer, min: 0, max: 600
  level?: string;                   // max: 50 caracteres
  tags?: string[];                  // array de strings
  
  // SI SE ENVÍA exercises, REEMPLAZA TODOS LOS EJERCICIOS EXISTENTES
  exercises?: ExerciseTemplate[];   // estructura igual que en CREATE
}
```

**⚠️ IMPORTANTE:**
- Si envías `exercises`, se eliminan TODOS los ejercicios anteriores y sus sets
- Si no envías `exercises`, solo se actualizan los metadatos de la plantilla
- Para editar sets individuales, usa el endpoint de sets

---

## ⚙️ **SETS INDIVIDUALES (DailyTemplateSet)**

### **EDITAR SET INDIVIDUAL**
**Endpoint:** `PUT /admin/gym/sets/{id}`

```typescript
interface UpdateSetRequest {
  // TODOS LOS CAMPOS SON OPCIONALES
  set_number?: number;              // integer, min: 1
  reps_min?: number;                // integer, min: 1
  reps_max?: number;                // integer, min: 1
  rest_seconds?: number;            // integer, min: 0
  rpe_target?: number;              // numeric (float), min: 0, max: 10
  weight_min?: number;              // numeric (float), min: 0, max: 1000
  weight_max?: number;              // numeric (float), min: 0, max: 1000
  weight_target?: number;           // numeric (float), min: 0, max: 1000
  notes?: string;                   // texto libre
}
```

**✅ VENTAJAS:**
- Puedes editar solo UN campo sin afectar los demás
- No necesitas reenviar toda la plantilla
- Más eficiente para cambios pequeños

---

## 📋 **ESTRUCTURA DE BASE DE DATOS**

### **Tabla: gym_daily_templates**
```sql
CREATE TABLE gym_daily_templates (
  id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  created_by              BIGINT UNSIGNED NULL,
  title                   VARCHAR(255) NOT NULL,
  goal                    VARCHAR(50) NULL,
  estimated_duration_min  SMALLINT UNSIGNED NULL,
  level                   VARCHAR(50) NULL,
  tags                    JSON NULL,
  is_preset               BOOLEAN DEFAULT FALSE,
  created_at              TIMESTAMP NULL,
  updated_at              TIMESTAMP NULL
);
```

### **Tabla: gym_daily_template_exercises**
```sql
CREATE TABLE gym_daily_template_exercises (
  id                 BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  daily_template_id  BIGINT UNSIGNED NOT NULL,
  exercise_id        BIGINT UNSIGNED NOT NULL,
  display_order      SMALLINT UNSIGNED DEFAULT 1,
  notes              TEXT NULL,
  created_at         TIMESTAMP NULL,
  updated_at         TIMESTAMP NULL,
  
  FOREIGN KEY (daily_template_id) REFERENCES gym_daily_templates(id) ON DELETE CASCADE,
  FOREIGN KEY (exercise_id) REFERENCES gym_exercises(id) ON DELETE RESTRICT
);
```

### **Tabla: gym_daily_template_sets**
```sql
CREATE TABLE gym_daily_template_sets (
  id                          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  daily_template_exercise_id  BIGINT UNSIGNED NOT NULL,
  set_number                  SMALLINT UNSIGNED DEFAULT 1,
  reps_min                    SMALLINT UNSIGNED NULL,
  reps_max                    SMALLINT UNSIGNED NULL,
  weight_min                  DECIMAL(8,2) NULL COMMENT 'Peso mínimo en kg',
  weight_max                  DECIMAL(8,2) NULL COMMENT 'Peso máximo en kg',
  weight_target               DECIMAL(8,2) NULL COMMENT 'Peso objetivo en kg',
  rest_seconds                SMALLINT UNSIGNED NULL,
  rpe_target                  DECIMAL(4,2) UNSIGNED NULL COMMENT '0.00-10.00',
  notes                       TEXT NULL,
  created_at                  TIMESTAMP NULL,
  updated_at                  TIMESTAMP NULL,
  
  FOREIGN KEY (daily_template_exercise_id) REFERENCES gym_daily_template_exercises(id) ON DELETE CASCADE,
  UNIQUE (daily_template_exercise_id, set_number)
);
```

---

## 🎯 **TIPOS DE DATOS DETALLADOS**

### **Campos INTEGER (Enteros)**
```typescript
// Base de datos: SMALLINT UNSIGNED (0-65535) o BIGINT UNSIGNED
// Validación Laravel: 'integer|min:X|max:Y'
// TypeScript: number (sin decimales)

set_number:              number;  // 1-65535
reps_min:                number;  // 1-65535
reps_max:                number;  // 1-65535
rest_seconds:            number;  // 0-65535
estimated_duration_min:  number;  // 0-600
display_order:           number;  // 1-65535
```

### **Campos DECIMAL/FLOAT (Decimales)**
```typescript
// Base de datos: DECIMAL(8,2) o DECIMAL(4,2)
// Validación Laravel: 'numeric|min:X|max:Y'
// TypeScript: number (con decimales)
// Modelo Laravel: cast 'float'

rpe_target:      number;  // 0.00-10.00 (precisión 2 decimales)
weight_min:      number;  // 0.00-999999.99 kg (precisión 2 decimales)
weight_max:      number;  // 0.00-999999.99 kg (precisión 2 decimales)
weight_target:   number;  // 0.00-999999.99 kg (precisión 2 decimales)
```

### **Campos STRING (Texto)**
```typescript
// Base de datos: VARCHAR o TEXT
// Validación Laravel: 'string|max:X'
// TypeScript: string

title:   string;  // max 255 caracteres
goal:    string;  // max 50 caracteres
level:   string;  // max 50 caracteres
notes:   string;  // sin límite (TEXT)
```

### **Campos ARRAY (JSON)**
```typescript
// Base de datos: JSON
// Validación Laravel: 'array'
// TypeScript: string[]
// Modelo Laravel: cast 'array'

tags: string[];  // ["cardio", "hiit", "upper_body"]
```

---

## 🔍 **EJEMPLOS COMPLETOS**

### **Ejemplo 1: Crear Plantilla Simple**
```json
POST /admin/gym/daily-templates
{
  "title": "Upper Body Strength",
  "goal": "strength",
  "estimated_duration_min": 60,
  "level": "intermediate",
  "tags": ["upper_body", "strength", "push"]
}
```

**Respuesta:**
```json
{
  "id": 1,
  "created_by": 1,
  "title": "Upper Body Strength",
  "goal": "strength",
  "estimated_duration_min": 60,
  "level": "intermediate",
  "tags": ["upper_body", "strength", "push"],
  "is_preset": false,
  "created_at": "2025-10-11T14:30:00.000000Z",
  "updated_at": "2025-10-11T14:30:00.000000Z",
  "exercises": []
}
```

---

### **Ejemplo 2: Crear Plantilla con Ejercicios y Sets**
```json
POST /admin/gym/daily-templates
{
  "title": "Push Day - Hypertrophy",
  "goal": "hypertrophy",
  "estimated_duration_min": 75,
  "level": "advanced",
  "tags": ["push", "chest", "shoulders"],
  "exercises": [
    {
      "exercise_id": 1,
      "order": 1,
      "notes": "Mantén el core activado",
      "sets": [
        {
          "set_number": 1,
          "reps_min": 8,
          "reps_max": 10,
          "weight_min": 60.0,
          "weight_max": 70.0,
          "weight_target": 65.0,
          "rest_seconds": 90,
          "rpe_target": 7.5,
          "notes": "Set de calentamiento"
        },
        {
          "set_number": 2,
          "reps_min": 8,
          "reps_max": 10,
          "weight_min": 70.0,
          "weight_max": 80.0,
          "weight_target": 75.0,
          "rest_seconds": 120,
          "rpe_target": 8.5
        },
        {
          "set_number": 3,
          "reps_min": 6,
          "reps_max": 8,
          "weight_min": 75.0,
          "weight_max": 85.0,
          "weight_target": 80.0,
          "rest_seconds": 120,
          "rpe_target": 9.0,
          "notes": "Set máximo"
        }
      ]
    },
    {
      "exercise_id": 5,
      "order": 2,
      "notes": "Mantén los codos pegados al cuerpo",
      "sets": [
        {
          "set_number": 1,
          "reps_min": 10,
          "reps_max": 12,
          "weight_min": 30.0,
          "weight_max": 35.0,
          "weight_target": 32.5,
          "rest_seconds": 60,
          "rpe_target": 8.0
        }
      ]
    }
  ]
}
```

**Respuesta:**
```json
{
  "id": 2,
  "created_by": 1,
  "title": "Push Day - Hypertrophy",
  "goal": "hypertrophy",
  "estimated_duration_min": 75,
  "level": "advanced",
  "tags": ["push", "chest", "shoulders"],
  "is_preset": false,
  "created_at": "2025-10-11T14:35:00.000000Z",
  "updated_at": "2025-10-11T14:35:00.000000Z",
  "exercises": [
    {
      "id": 1,
      "daily_template_id": 2,
      "exercise_id": 1,
      "display_order": 1,
      "notes": "Mantén el core activado",
      "created_at": "2025-10-11T14:35:00.000000Z",
      "updated_at": "2025-10-11T14:35:00.000000Z",
      "exercise": {
        "id": 1,
        "name": "Press de Banca con Barra",
        "description": "Ejercicio compuesto para pecho",
        "muscle_groups": ["pectoral_mayor", "triceps", "deltoides_anterior"],
        "target_muscle_groups": ["pectoral_mayor"],
        "equipment": "Barra olímpica",
        "difficulty_level": "intermediate"
      },
      "sets": [
        {
          "id": 1,
          "daily_template_exercise_id": 1,
          "set_number": 1,
          "reps_min": 8,
          "reps_max": 10,
          "weight_min": 60.0,
          "weight_max": 70.0,
          "weight_target": 65.0,
          "rest_seconds": 90,
          "rpe_target": 7.5,
          "notes": "Set de calentamiento",
          "created_at": "2025-10-11T14:35:00.000000Z",
          "updated_at": "2025-10-11T14:35:00.000000Z"
        },
        {
          "id": 2,
          "daily_template_exercise_id": 1,
          "set_number": 2,
          "reps_min": 8,
          "reps_max": 10,
          "weight_min": 70.0,
          "weight_max": 80.0,
          "weight_target": 75.0,
          "rest_seconds": 120,
          "rpe_target": 8.5,
          "notes": null,
          "created_at": "2025-10-11T14:35:00.000000Z",
          "updated_at": "2025-10-11T14:35:00.000000Z"
        },
        {
          "id": 3,
          "daily_template_exercise_id": 1,
          "set_number": 3,
          "reps_min": 6,
          "reps_max": 8,
          "weight_min": 75.0,
          "weight_max": 85.0,
          "weight_target": 80.0,
          "rest_seconds": 120,
          "rpe_target": 9.0,
          "notes": "Set máximo",
          "created_at": "2025-10-11T14:35:00.000000Z",
          "updated_at": "2025-10-11T14:35:00.000000Z"
        }
      ]
    }
  ]
}
```

---

### **Ejemplo 3: Editar Solo Metadatos**
```json
PUT /admin/gym/daily-templates/2
{
  "title": "Push Day - Hypertrophy (Actualizado)",
  "estimated_duration_min": 80,
  "tags": ["push", "chest", "shoulders", "triceps"]
}
```

**Nota:** No se tocan los ejercicios ni sets existentes.

---

### **Ejemplo 4: Editar Set Individual**
```json
PUT /admin/gym/sets/2
{
  "rpe_target": 9.0,
  "weight_target": 77.5
}
```

**Respuesta:**
```json
{
  "id": 2,
  "daily_template_exercise_id": 1,
  "set_number": 2,
  "reps_min": 8,
  "reps_max": 10,
  "weight_min": 70.0,
  "weight_max": 80.0,
  "weight_target": 77.5,
  "rest_seconds": 120,
  "rpe_target": 9.0,
  "notes": null,
  "created_at": "2025-10-11T14:35:00.000000Z",
  "updated_at": "2025-10-11T14:40:00.000000Z"
}
```

---

## ⚠️ **VALIDACIONES IMPORTANTES**

### **Validaciones en Backend (Laravel)**
```php
// Plantilla Diaria
'title' => 'required|string|max:255'
'goal' => 'nullable|string|max:50'
'estimated_duration_min' => 'nullable|integer|min:0|max:600'
'level' => 'nullable|string|max:50'
'tags' => 'array'
'tags.*' => 'string'

// Ejercicio en Plantilla
'exercises.*.exercise_id' => 'nullable|integer|exists:gym_exercises,id'
'exercises.*.order' => 'nullable|integer|min:1'
'exercises.*.notes' => 'nullable|string'

// Sets
'exercises.*.sets.*.set_number' => 'nullable|integer|min:1'
'exercises.*.sets.*.reps_min' => 'nullable|integer|min:1'
'exercises.*.sets.*.reps_max' => 'nullable|integer|min:1'
'exercises.*.sets.*.rest_seconds' => 'nullable|integer|min:0'
'exercises.*.sets.*.rpe_target' => 'nullable|numeric|min:0|max:10'
'exercises.*.sets.*.weight_min' => 'nullable|numeric|min:0|max:1000'
'exercises.*.sets.*.weight_max' => 'nullable|numeric|min:0|max:1000'
'exercises.*.sets.*.weight_target' => 'nullable|numeric|min:0|max:1000'
'exercises.*.sets.*.notes' => 'nullable|string'
```

### **Validaciones Recomendadas en Frontend**
```typescript
// Validar que reps_min <= reps_max
if (reps_min && reps_max && reps_min > reps_max) {
  throw new Error('reps_min no puede ser mayor que reps_max');
}

// Validar que weight_min <= weight_max
if (weight_min && weight_max && weight_min > weight_max) {
  throw new Error('weight_min no puede ser mayor que weight_max');
}

// Validar RPE en rango
if (rpe_target && (rpe_target < 0 || rpe_target > 10)) {
  throw new Error('rpe_target debe estar entre 0 y 10');
}

// Validar duración estimada
if (estimated_duration_min && (estimated_duration_min < 0 || estimated_duration_min > 600)) {
  throw new Error('Duración debe estar entre 0 y 600 minutos (10 horas)');
}
```

---

## 🎨 **VALORES SUGERIDOS PARA UI**

### **Goal (Objetivo)**
```typescript
const GOALS = [
  { value: 'strength', label: 'Fuerza' },
  { value: 'hypertrophy', label: 'Hipertrofia' },
  { value: 'endurance', label: 'Resistencia' },
  { value: 'power', label: 'Potencia' },
  { value: 'mobility', label: 'Movilidad' },
  { value: 'cardio', label: 'Cardio' }
];
```

### **Level (Nivel)**
```typescript
const LEVELS = [
  { value: 'beginner', label: 'Principiante' },
  { value: 'intermediate', label: 'Intermedio' },
  { value: 'advanced', label: 'Avanzado' }
];
```

### **RPE (Rate of Perceived Exertion)**
```typescript
// Escala 0-10 con incrementos de 0.5
const RPE_VALUES = [
  { value: 6.0, label: '6 - Muy fácil' },
  { value: 6.5, label: '6.5' },
  { value: 7.0, label: '7 - Fácil' },
  { value: 7.5, label: '7.5' },
  { value: 8.0, label: '8 - Moderado' },
  { value: 8.5, label: '8.5' },
  { value: 9.0, label: '9 - Difícil' },
  { value: 9.5, label: '9.5' },
  { value: 10.0, label: '10 - Máximo' }
];
```

### **Rest Seconds (Descanso)**
```typescript
const REST_PRESETS = [
  { value: 30, label: '30s' },
  { value: 45, label: '45s' },
  { value: 60, label: '1 min' },
  { value: 90, label: '1.5 min' },
  { value: 120, label: '2 min' },
  { value: 180, label: '3 min' },
  { value: 240, label: '4 min' },
  { value: 300, label: '5 min' }
];
```

---

## 💡 **CONSEJOS DE IMPLEMENTACIÓN**

### **1. Valores por Defecto Sugeridos**
```typescript
// Para nuevos sets
const DEFAULT_SET = {
  reps_min: 8,
  reps_max: 12,
  rest_seconds: 90,
  rpe_target: 8.0,
  weight_min: null,
  weight_max: null,
  weight_target: null,
  notes: null
};
```

### **2. Campos Opcionales vs Required**
- ✅ **SIEMPRE enviar:** `title` (plantilla)
- ✅ **Recomendado:** `goal`, `level`, `estimated_duration_min`
- ⚠️ **Opcional pero importante:** `weight_target` (para seguimiento de progreso)
- ℹ️ **Opcional:** `notes`, `tags`

### **3. Gestión de Decimales**
```typescript
// Siempre enviar como número, no string
// ✅ Correcto
{ "rpe_target": 8.5, "weight_target": 75.5 }

// ❌ Incorrecto
{ "rpe_target": "8.5", "weight_target": "75.5" }

// Para formatear en UI
const formatWeight = (weight: number) => weight.toFixed(1) + ' kg';
const formatRPE = (rpe: number) => rpe.toFixed(1);
```

### **4. Manejo de Nulls**
```typescript
// Todos los campos opcionales pueden ser null
// Backend NO acepta undefined, usar null explícitamente

// ✅ Correcto
{ "weight_target": null }

// ❌ Incorrecto
{ "weight_target": undefined }

// Omitir el campo también está bien
{ } // sin weight_target
```

---

## 🔄 **FLUJOS RECOMENDADOS**

### **Flujo 1: Crear Plantilla Completa (UI Simple)**
1. Usuario ingresa datos básicos (título, objetivo, nivel)
2. Usuario añade ejercicios uno por uno
3. Para cada ejercicio, genera sets con valores predeterminados
4. Usuario ajusta los sets según necesidad
5. Frontend envía TODO en un solo POST

### **Flujo 2: Editor Avanzado (UI Compleja)**
1. Crear plantilla básica primero (solo metadatos)
2. Añadir ejercicios posteriormente
3. Editar sets individuales con PUT /sets/{id}
4. Permite edición granular sin reenviar todo

---

## 📌 **RESUMEN EJECUTIVO**

| Campo | Tipo BD | Tipo TS | Validación | Requerido |
|-------|---------|---------|------------|-----------|
| **title** | VARCHAR(255) | string | max:255 | ✅ SÍ |
| **goal** | VARCHAR(50) | string | max:50 | ❌ No |
| **estimated_duration_min** | SMALLINT | number | 0-600 | ❌ No |
| **level** | VARCHAR(50) | string | max:50 | ❌ No |
| **tags** | JSON | string[] | array | ❌ No |
| **set_number** | SMALLINT | number | min:1 | ❌ No |
| **reps_min** | SMALLINT | number | min:1 | ❌ No |
| **reps_max** | SMALLINT | number | min:1 | ❌ No |
| **rest_seconds** | SMALLINT | number | min:0 | ❌ No |
| **rpe_target** | DECIMAL(4,2) | number | 0-10 | ❌ No |
| **weight_min** | DECIMAL(8,2) | number | 0-1000 | ❌ No |
| **weight_max** | DECIMAL(8,2) | number | 0-1000 | ❌ No |
| **weight_target** | DECIMAL(8,2) | number | 0-1000 | ❌ No |
| **notes** | TEXT | string | sin límite | ❌ No |

**Notas:**
- Todos los campos numéricos pueden ser `null`
- Los arrays pueden estar vacíos `[]`
- Los pesos están en kilogramos con 2 decimales
- RPE está en escala 0-10 con 2 decimales
