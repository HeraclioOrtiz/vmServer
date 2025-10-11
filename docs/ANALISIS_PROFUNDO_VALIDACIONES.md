# 🔬 ANÁLISIS PROFUNDO DE VALIDACIONES Y TIPOS DE DATOS

**Documento Técnico Avanzado**  
**Sistema:** Villa Mitre - Panel de Gimnasio  
**Complejidad:** Alta (Estructuras Anidadas)

---

## ⚠️ **CORRECCIÓN APLICADA**

Se detectó y corrigió un bug crítico donde las validaciones NO incluían los campos de peso que fueron agregados posteriormente a la base de datos.

**Archivos corregidos:**
- ✅ `app/Http/Controllers/Gym/Admin/DailyTemplateController.php` (líneas 112-114 y 167-169)

---

## 🎯 **COMPLEJIDAD DE LA ESTRUCTURA**

### **Nivel 1: Plantilla Diaria (Padre)**
```typescript
interface DailyTemplate {
  // Campos simples
  id: number;
  created_by: number | null;
  title: string;
  goal: string | null;
  estimated_duration_min: number | null;
  level: string | null;
  is_preset: boolean;
  
  // Campos con CAST especial
  tags: string[];  // JSON → Array en modelo Laravel
  
  // Timestamps automáticos
  created_at: string;
  updated_at: string;
  
  // Relación (eager loading)
  exercises?: DailyTemplateExercise[];
}
```

**Casts en Modelo Laravel:**
```php
protected $casts = [
    'tags' => 'array',        // JSON ⟷ Array PHP
    'is_preset' => 'boolean',  // TINYINT ⟷ Boolean PHP
];
```

---

### **Nivel 2: Ejercicio en Plantilla (Hijo de Plantilla)**
```typescript
interface DailyTemplateExercise {
  // Identificadores
  id: number;
  daily_template_id: number;  // FK → gym_daily_templates
  exercise_id: number;         // FK → gym_exercises
  
  // Campos simples
  display_order: number;
  notes: string | null;
  
  // Timestamps
  created_at: string;
  updated_at: string;
  
  // Relaciones (eager loading)
  exercise?: Exercise;  // Datos completos del ejercicio
  sets?: DailyTemplateSet[];
}
```

**Sin Casts especiales** (todos los campos son nativos)

---

### **Nivel 3: Set/Serie (Nieto de Plantilla)**
```typescript
interface DailyTemplateSet {
  // Identificador
  id: number;
  daily_template_exercise_id: number;  // FK → gym_daily_template_exercises
  
  // Campos INTEGER (sin decimales)
  set_number: number;      // SMALLINT UNSIGNED
  reps_min: number | null; // SMALLINT UNSIGNED
  reps_max: number | null; // SMALLINT UNSIGNED
  rest_seconds: number | null; // SMALLINT UNSIGNED
  
  // Campos DECIMAL (con decimales) - CAST 'float'
  rpe_target: number | null;      // DECIMAL(4,2) → float
  weight_min: number | null;      // DECIMAL(8,2) → float
  weight_max: number | null;      // DECIMAL(8,2) → float
  weight_target: number | null;   // DECIMAL(8,2) → float
  
  // Texto
  notes: string | null;
  
  // Timestamps
  created_at: string;
  updated_at: string;
}
```

**Casts en Modelo Laravel:**
```php
protected $casts = [
    'rpe_target' => 'float',    // DECIMAL(4,2) ⟷ Float PHP
    'weight_min' => 'float',    // DECIMAL(8,2) ⟷ Float PHP
    'weight_max' => 'float',    // DECIMAL(8,2) ⟷ Float PHP
    'weight_target' => 'float', // DECIMAL(8,2) ⟷ Float PHP
];
```

---

### **Nivel 2-Alternativo: Exercise (Referenciado)**
```typescript
interface Exercise {
  id: number;
  name: string;
  description: string | null;
  
  // Campos con CAST 'array' (JSON → Array)
  muscle_groups: string[];        // JSON → Array
  target_muscle_groups: string[]; // JSON → Array
  tags: string[];                 // JSON → Array
  
  // Campos simples
  movement_pattern: string | null;
  equipment: string | null;
  difficulty_level: 'beginner' | 'intermediate' | 'advanced' | null;
  instructions: string | null;
  
  created_at: string;
  updated_at: string;
}
```

**Casts en Modelo Laravel:**
```php
protected $casts = [
    'muscle_groups' => 'array',
    'target_muscle_groups' => 'array',
    'tags' => 'array',
];
```

---

## 🔍 **VALIDACIONES ANIDADAS EXPLICADAS**

### **Sintaxis de Laravel para Arrays Anidados**

Laravel usa notación de punto (`.`) para validar estructuras anidadas:

```php
// Nivel 1
'exercises' => 'array'  // Validar que exercises sea un array

// Nivel 2 - Cada elemento del array
'exercises.*' => 'array'  // Cada exercises[i] es un objeto

// Nivel 2 - Campos específicos
'exercises.*.exercise_id' => 'nullable|integer|exists:gym_exercises,id'
// ↑        ↑    ↑
// array    any  campo
//          index

// Nivel 3 - Array anidado dentro de array
'exercises.*.sets' => 'array'

// Nivel 4 - Campos del array anidado
'exercises.*.sets.*.reps_min' => 'nullable|integer|min:1'
// ↑        ↑  ↑    ↑   ↑
// array    any array any campo
//          index     index
```

---

## 📊 **VALIDACIONES COMPLETAS LÍNEA POR LÍNEA**

### **POST /admin/gym/daily-templates (CREATE)**

```php
$request->validate([
    // ========== NIVEL 1: PLANTILLA ==========
    'title' => 'required|string|max:255',
    // ↑ Obligatorio, texto, máximo 255 caracteres
    
    'goal' => 'nullable|string|max:50',
    // ↑ Opcional, texto, máximo 50 caracteres
    
    'estimated_duration_min' => 'nullable|integer|min:0|max:600',
    // ↑ Opcional, entero, entre 0 y 600 minutos
    
    'level' => 'nullable|string|max:50',
    // ↑ Opcional, texto, máximo 50 caracteres
    
    'tags' => 'array',
    // ↑ Debe ser un array
    
    'tags.*' => 'string',
    // ↑ Cada elemento del array tags debe ser string
    
    // ========== NIVEL 2: EJERCICIOS ==========
    'exercises' => 'array',
    // ↑ Debe ser un array
    
    'exercises.*.exercise_id' => 'nullable|integer|exists:gym_exercises,id',
    // ↑ Opcional, entero, debe existir en tabla gym_exercises
    
    'exercises.*.order' => 'nullable|integer|min:1',
    // ↑ Opcional, entero, mínimo 1
    
    'exercises.*.notes' => 'nullable|string',
    // ↑ Opcional, texto libre
    
    // ========== NIVEL 3: SETS ==========
    'exercises.*.sets' => 'array',
    // ↑ Cada ejercicio puede tener un array de sets
    
    'exercises.*.sets.*.set_number' => 'nullable|integer|min:1',
    // ↑ Número de serie, opcional, entero, mínimo 1
    
    'exercises.*.sets.*.reps_min' => 'nullable|integer|min:1',
    // ↑ Reps mínimas, opcional, entero, mínimo 1
    
    'exercises.*.sets.*.reps_max' => 'nullable|integer|min:1',
    // ↑ Reps máximas, opcional, entero, mínimo 1
    
    'exercises.*.sets.*.weight_min' => 'nullable|numeric|min:0|max:1000',
    // ↑ Peso mínimo, opcional, numérico (float), 0-1000 kg
    
    'exercises.*.sets.*.weight_max' => 'nullable|numeric|min:0|max:1000',
    // ↑ Peso máximo, opcional, numérico (float), 0-1000 kg
    
    'exercises.*.sets.*.weight_target' => 'nullable|numeric|min:0|max:1000',
    // ↑ Peso objetivo, opcional, numérico (float), 0-1000 kg
    
    'exercises.*.sets.*.rest_seconds' => 'nullable|integer|min:0',
    // ↑ Descanso, opcional, entero, mínimo 0 segundos
    
    'exercises.*.sets.*.rpe_target' => 'nullable|numeric|min:0|max:10',
    // ↑ RPE, opcional, numérico (float), escala 0-10
    
    'exercises.*.sets.*.notes' => 'nullable|string',
    // ↑ Notas, opcional, texto libre
]);
```

---

### **PUT /admin/gym/daily-templates/{id} (UPDATE)**

```php
$request->validate([
    // ========== NIVEL 1: PLANTILLA ==========
    'title' => 'sometimes|required|string|max:255',
    // ↑ Si se envía, es obligatorio (sometimes + required)
    
    // Resto de campos IGUAL que CREATE pero opcionales
    // Si se envía 'exercises', REEMPLAZA todos los ejercicios existentes
]);
```

**⚠️ IMPORTANTE:** `sometimes` significa "solo validar si el campo existe en el request"

---

### **PUT /admin/gym/sets/{id} (UPDATE SET INDIVIDUAL)**

```php
$request->validate([
    'set_number' => 'sometimes|integer|min:1',
    'reps_min' => 'nullable|integer|min:1',
    'reps_max' => 'nullable|integer|min:1',
    'rest_seconds' => 'nullable|integer|min:0',
    'rpe_target' => 'nullable|numeric|min:0|max:10',
    'weight_min' => 'nullable|numeric|min:0|max:1000',
    'weight_max' => 'nullable|numeric|min:0|max:1000',
    'weight_target' => 'nullable|numeric|min:0|max:1000',
    'notes' => 'nullable|string',
]);
```

---

## 🎭 **DIFERENCIAS: integer vs numeric**

### **`integer` Validación**
```php
'reps_min' => 'integer|min:1'
```

**Acepta:**
- ✅ `10`
- ✅ `"10"` (Laravel lo convierte automáticamente)

**Rechaza:**
- ❌ `10.5`
- ❌ `"10.5"`
- ❌ `null` (a menos que agregues `nullable`)

**En Base de Datos:** `SMALLINT UNSIGNED` o `INT`

---

### **`numeric` Validación**
```php
'weight_target' => 'numeric|min:0|max:1000'
```

**Acepta:**
- ✅ `75`
- ✅ `75.5`
- ✅ `"75.5"` (Laravel lo convierte)
- ✅ `75.0`

**Rechaza:**
- ❌ `"75.5kg"` (no es numérico puro)
- ❌ `null` (a menos que agregues `nullable`)

**En Base de Datos:** `DECIMAL(8,2)` con cast `'float'` en modelo

---

## 🔄 **FLUJO DE DATOS: REQUEST → DATABASE**

### **Ejemplo Completo:**

**1. Frontend envía JSON:**
```json
{
  "title": "Push Day",
  "goal": "hypertrophy",
  "estimated_duration_min": 75,
  "tags": ["push", "chest"],
  "exercises": [
    {
      "exercise_id": 1,
      "order": 1,
      "sets": [
        {
          "set_number": 1,
          "reps_min": 8,
          "reps_max": 12,
          "weight_target": 75.5,
          "rpe_target": 8.5,
          "rest_seconds": 90
        }
      ]
    }
  ]
}
```

---

**2. Laravel valida:**
```php
// Validación línea por línea
'title' => 'required|string|max:255'  ✅ "Push Day" (8 chars)
'goal' => 'nullable|string|max:50'    ✅ "hypertrophy" (11 chars)
'estimated_duration_min' => 'nullable|integer|min:0|max:600'  ✅ 75
'tags' => 'array'  ✅ ["push", "chest"]
'tags.*' => 'string'  ✅ cada elemento es string
'exercises.0.exercise_id' => 'nullable|integer|exists:gym_exercises,id'  ✅ 1 existe
'exercises.0.sets.0.reps_min' => 'nullable|integer|min:1'  ✅ 8
'exercises.0.sets.0.weight_target' => 'nullable|numeric|min:0|max:1000'  ✅ 75.5
'exercises.0.sets.0.rpe_target' => 'nullable|numeric|min:0|max:10'  ✅ 8.5
```

---

**3. Controller crea registros:**
```php
// Nivel 1: Plantilla
$template = DailyTemplate::create([
    'title' => 'Push Day',
    'goal' => 'hypertrophy',
    'estimated_duration_min' => 75,
    'tags' => ['push', 'chest'], // Laravel lo convierte a JSON automáticamente
]);

// Nivel 2: Ejercicio
$templateExercise = DailyTemplateExercise::create([
    'daily_template_id' => $template->id,
    'exercise_id' => 1,
    'display_order' => 1,
]);

// Nivel 3: Set
$set = DailyTemplateSet::create([
    'daily_template_exercise_id' => $templateExercise->id,
    'set_number' => 1,
    'reps_min' => 8,
    'reps_max' => 12,
    'weight_target' => 75.5,  // Laravel lo guarda como DECIMAL(8,2)
    'rpe_target' => 8.5,       // Laravel lo guarda como DECIMAL(4,2)
    'rest_seconds' => 90,
]);
```

---

**4. Base de Datos guarda:**
```sql
-- gym_daily_templates
INSERT INTO gym_daily_templates 
(title, goal, estimated_duration_min, tags) 
VALUES 
('Push Day', 'hypertrophy', 75, '["push","chest"]');
-- ↑ JSON string

-- gym_daily_template_exercises
INSERT INTO gym_daily_template_exercises 
(daily_template_id, exercise_id, display_order) 
VALUES 
(1, 1, 1);

-- gym_daily_template_sets
INSERT INTO gym_daily_template_sets 
(daily_template_exercise_id, set_number, reps_min, reps_max, weight_target, rpe_target, rest_seconds) 
VALUES 
(1, 1, 8, 12, 75.50, 8.50, 90);
--                  ↑     ↑
--              DECIMAL  DECIMAL
```

---

**5. Laravel lee y devuelve:**
```php
$template = DailyTemplate::with(['exercises.sets', 'exercises.exercise'])->find(1);
```

```json
{
  "id": 1,
  "title": "Push Day",
  "goal": "hypertrophy",
  "estimated_duration_min": 75,
  "tags": ["push", "chest"],  // ← Cast automático JSON → Array
  "exercises": [
    {
      "id": 1,
      "daily_template_id": 1,
      "exercise_id": 1,
      "display_order": 1,
      "sets": [
        {
          "id": 1,
          "set_number": 1,
          "reps_min": 8,
          "reps_max": 12,
          "weight_target": 75.5,  // ← Cast automático DECIMAL → float
          "rpe_target": 8.5,      // ← Cast automático DECIMAL → float
          "rest_seconds": 90
        }
      ]
    }
  ]
}
```

---

## 🚨 **ERRORES COMUNES Y SOLUCIONES**

### **Error 1: Enviar string en vez de number**

❌ **Incorrecto:**
```json
{
  "estimated_duration_min": "75",
  "weight_target": "75.5"
}
```

✅ **Correcto:**
```json
{
  "estimated_duration_min": 75,
  "weight_target": 75.5
}
```

**Nota:** Laravel SÍ convierte strings numéricos automáticamente, pero es mejor práctica enviar el tipo correcto.

---

### **Error 2: Enviar undefined en vez de null**

❌ **Incorrecto (JavaScript):**
```javascript
{
  weight_target: undefined
}
```

✅ **Correcto:**
```javascript
{
  weight_target: null
}
```

O simplemente omitir el campo:
```javascript
{
  // sin weight_target
}
```

---

### **Error 3: Enviar array como string**

❌ **Incorrecto:**
```json
{
  "tags": "push,chest,shoulders"
}
```

✅ **Correcto:**
```json
{
  "tags": ["push", "chest", "shoulders"]
}
```

---

### **Error 4: Índices de arrays incorrectos**

❌ **Incorrecto:**
```json
{
  "exercises": {
    "0": { "exercise_id": 1 },
    "1": { "exercise_id": 2 }
  }
}
```

✅ **Correcto:**
```json
{
  "exercises": [
    { "exercise_id": 1 },
    { "exercise_id": 2 }
  ]
}
```

---

### **Error 5: Decimales con más de 2 dígitos**

⚠️ **Se trunca:**
```json
{
  "weight_target": 75.555  // Se guarda como 75.56 (redondeo)
}
```

✅ **Recomendado:**
```json
{
  "weight_target": 75.55  // Máximo 2 decimales
}
```

---

## 📊 **TABLA RESUMEN: TIPOS DE DATOS**

| Campo | Base de Datos | Cast Model | Validación Laravel | TypeScript | JSON |
|-------|---------------|------------|-------------------|------------|------|
| **id** | BIGINT UNSIGNED | - | - | number | number |
| **title** | VARCHAR(255) | - | string\|max:255 | string | string |
| **goal** | VARCHAR(50) | - | string\|max:50 | string\|null | string\|null |
| **estimated_duration_min** | SMALLINT UNSIGNED | - | integer\|0-600 | number\|null | number\|null |
| **tags** | JSON | array | array | string[] | string[] |
| **is_preset** | TINYINT(1) | boolean | - | boolean | boolean |
| **set_number** | SMALLINT UNSIGNED | - | integer\|min:1 | number | number |
| **reps_min** | SMALLINT UNSIGNED | - | integer\|min:1 | number\|null | number\|null |
| **rpe_target** | DECIMAL(4,2) | float | numeric\|0-10 | number\|null | number\|null |
| **weight_min** | DECIMAL(8,2) | float | numeric\|0-1000 | number\|null | number\|null |
| **muscle_groups** | JSON | array | array | string[] | string[] |

---

## 🎯 **CONSEJOS FINALES**

### **1. Validación en Frontend (Recomendado)**

```typescript
function validateSet(set: Partial<DailyTemplateSet>): string[] {
  const errors: string[] = [];
  
  // Validar reps_min <= reps_max
  if (set.reps_min && set.reps_max && set.reps_min > set.reps_max) {
    errors.push('Reps mínimas no pueden ser mayores que reps máximas');
  }
  
  // Validar weight_min <= weight_max
  if (set.weight_min && set.weight_max && set.weight_min > set.weight_max) {
    errors.push('Peso mínimo no puede ser mayor que peso máximo');
  }
  
  // Validar RPE en rango
  if (set.rpe_target !== null && set.rpe_target !== undefined) {
    if (set.rpe_target < 0 || set.rpe_target > 10) {
      errors.push('RPE debe estar entre 0 y 10');
    }
  }
  
  // Validar decimales
  if (set.weight_target) {
    const decimals = (set.weight_target.toString().split('.')[1] || '').length;
    if (decimals > 2) {
      errors.push('Peso solo permite 2 decimales');
    }
  }
  
  return errors;
}
```

---

### **2. Formateo de Datos**

```typescript
// Antes de enviar al backend
function sanitizeSet(set: any): DailyTemplateSet {
  return {
    ...set,
    // Convertir strings a numbers
    reps_min: set.reps_min ? Number(set.reps_min) : null,
    reps_max: set.reps_max ? Number(set.reps_max) : null,
    // Redondear decimales
    weight_target: set.weight_target ? Math.round(set.weight_target * 100) / 100 : null,
    rpe_target: set.rpe_target ? Math.round(set.rpe_target * 100) / 100 : null,
    // Convertir undefined a null
    notes: set.notes === undefined ? null : set.notes,
  };
}
```

---

### **3. Manejo de Errores del Backend**

```typescript
try {
  await api.post('/admin/gym/daily-templates', data);
} catch (error) {
  if (error.response?.status === 422) {
    // Errores de validación
    const errors = error.response.data.errors;
    
    // Ejemplo de error:
    // {
    //   "exercises.0.sets.0.weight_target": [
    //     "The exercises.0.sets.0.weight_target must not be greater than 1000."
    //   ]
    // }
    
    // Parsear y mostrar al usuario
    Object.entries(errors).forEach(([field, messages]) => {
      console.error(`Error en ${field}:`, messages);
    });
  }
}
```

---

## 📖 **RESUMEN EJECUTIVO**

**Estructura anidada de 3 niveles:**
1. **Plantilla** → campos simples + array `tags` (JSON)
2. **Ejercicios** → array dentro de plantilla + array `sets`
3. **Sets** → campos simples con tipos mixtos (integer + float)

**Tipos críticos:**
- **integer:** reps, rest_seconds, set_number
- **numeric (float):** weights, rpe_target
- **array (JSON):** tags, exercises, sets

**Validaciones anidadas:**
- Usa notación de punto: `exercises.*.sets.*.campo`
- Cada `*` representa "cualquier índice del array"

**Casts automáticos:**
- JSON ⟷ Array (tags, muscle_groups)
- DECIMAL ⟷ Float (weights, rpe)
- TINYINT ⟷ Boolean (is_preset)

---

**Fecha de corrección:** 11 de Octubre 2025  
**Bug corregido:** Validaciones de peso agregadas en DailyTemplateController
