# 📊 GUÍA DE CAMBIOS - TIPOS DE DATOS PARA PANEL PROFESORES

**Versión:** 2025-10-06  
**Alcance:** Adaptación panel administrador → función profesores

---

## 🔄 **CAMBIOS EN MODELOS DE DATOS**

### **1. Modelo Exercise**
```php
// CAMPOS ACTUALIZADOS
'target_muscle_groups' => 'array'     // JSON → Array cast
'equipment'           => 'array'     // JSON → Array cast  
'tags'               => 'array'     // JSON → Array cast
'difficulty_level'   => 'string'    // Enum: beginner|intermediate|advanced
'exercise_type'      => 'string'    // Enum: strength|cardio|flexibility|balance
```

### **2. Modelo DailyTemplateSet**
```php
// CAMPOS AGREGADOS
'weight_min'    => 'float'          // Peso mínimo recomendado (kg)
'weight_max'    => 'float'          // Peso máximo recomendado (kg)  
'weight_target' => 'float'          // Peso objetivo/sugerido (kg)

// CAMPOS REMOVIDOS
'tempo'         => ELIMINADO        // Ya no se usa en sets
```

### **3. Modelo AssignedSet**
```php
// CAMPOS AGREGADOS (sincronización con DailyTemplateSet)
'weight_min'    => 'float'          // Peso mínimo recomendado (kg)
'weight_max'    => 'float'          // Peso máximo recomendado (kg)
'weight_target' => 'float'          // Peso objetivo/sugerido (kg)
```

---

## 📋 **ESTRUCTURA DE RESPUESTAS API**

### **1. Endpoint: GET /api/student/template/{id}/details**
```json
{
  "exercises": [
    {
      "exercise": {
        "target_muscle_groups": ["chest", "triceps"],    // Array
        "equipment": ["barbell", "bench"],               // Array
        "tags": ["compound", "upper-body"],              // Array
        "difficulty_level": "intermediate",              // String
        "exercise_type": "strength"                      // String
      },
      "sets": [
        {
          "reps_min": 4,                                // Integer
          "reps_max": 6,                                // Integer
          "weight_min": 40.0,                           // Float (NUEVO)
          "weight_max": 80.0,                           // Float (NUEVO)
          "weight_target": 60.0,                        // Float (NUEVO)
          "rpe_target": 8.5,                           // Float
          "rest_seconds": 180                          // Integer
        }
      ]
    }
  ]
}
```

### **2. Endpoint: GET /api/admin/gym/exercises**
```json
{
  "data": [
    {
      "target_muscle_groups": ["quadriceps", "glutes"], // Array (no string)
      "equipment": ["barbell", "squat-rack"],           // Array (no string)
      "tags": ["compound", "lower-body"],               // Array (no string)
      "difficulty_level": "intermediate",               // String enum
      "exercise_type": "strength"                       // String enum
    }
  ]
}
```

---

## 🔧 **CAMBIOS EN VALIDACIONES**

### **1. ExerciseRequest**
```php
// REGLAS ACTUALIZADAS
'target_muscle_groups' => 'array|max:5'              // Array, máximo 5 elementos
'target_muscle_groups.*' => 'string|max:50'          // Cada elemento string
'equipment' => 'array|max:10'                        // Array, máximo 10 elementos  
'equipment.*' => 'string|max:50'                     // Cada elemento string
'tags' => 'array|max:10'                            // Array, máximo 10 elementos
'tags.*' => 'string|max:30'                         // Cada elemento string
'difficulty_level' => 'in:beginner,intermediate,advanced'
'exercise_type' => 'in:strength,cardio,flexibility,balance'
```

### **2. SetService - validateSetData()**
```php
// VALIDACIONES AGREGADAS
'weight_min' => 'numeric|min:0|max:1000'            // Float, 0-1000kg
'weight_max' => 'numeric|min:0|max:1000'            // Float, 0-1000kg  
'weight_target' => 'numeric|min:0|max:1000'         // Float, 0-1000kg
```

---

## 🗄️ **CAMBIOS EN BASE DE DATOS**

### **1. Tabla: gym_exercises**
```sql
-- COLUMNAS MODIFICADAS (tipo JSON mantenido, cast en modelo)
target_muscle_groups JSON          -- Cast a array en modelo
equipment JSON                     -- Cast a array en modelo  
tags JSON                         -- Cast a array en modelo
difficulty_level VARCHAR(20)       -- Enum en validación
exercise_type VARCHAR(20)          -- Enum en validación
```

### **2. Tabla: gym_daily_template_sets**
```sql
-- COLUMNAS AGREGADAS
weight_min DECIMAL(8,2) NULL       -- Peso mínimo (kg)
weight_max DECIMAL(8,2) NULL       -- Peso máximo (kg)
weight_target DECIMAL(8,2) NULL    -- Peso objetivo (kg)

-- COLUMNAS REMOVIDAS  
tempo VARCHAR(20)                  -- ELIMINADA
```

### **3. Tabla: gym_assigned_sets**
```sql
-- COLUMNAS AGREGADAS (sincronización)
weight_min DECIMAL(8,2) NULL       -- Peso mínimo (kg)
weight_max DECIMAL(8,2) NULL       -- Peso máximo (kg)  
weight_target DECIMAL(8,2) NULL    -- Peso objetivo (kg)
```

---

## 📤 **TIPOS DE DATOS EN SEEDERS**

### **1. GymExercisesSeeder**
```php
// FORMATO DE DATOS
[
    'target_muscle_groups' => ['chest', 'triceps'],      // Array directo
    'equipment' => ['barbell', 'bench'],                 // Array directo
    'tags' => ['compound', 'upper-body'],                // Array directo
    'difficulty_level' => 'intermediate',                // String
    'exercise_type' => 'strength'                        // String
]
```

### **2. GymDailyTemplatesSeeder**
```php
// FORMATO DE SETS CON PESOS
[
    'reps_min' => 4,                    // Integer
    'reps_max' => 6,                    // Integer  
    'weight_min' => 40.0,               // Float (NUEVO)
    'weight_max' => 80.0,               // Float (NUEVO)
    'weight_target' => 60.0,            // Float (NUEVO)
    'rpe_target' => 8.5,                // Float
    'rest_seconds' => 180               // Integer
]
```

---

## 🔄 **COMPATIBILIDAD Y MIGRACIÓN**

### **Datos Existentes:**
- **Arrays JSON:** Automáticamente cast a array PHP
- **Campos peso:** NULL por defecto, poblados por seeders
- **Validaciones:** Retrocompatibles con datos existentes

### **Frontend Expectations:**
- **Recibir arrays** en lugar de strings para campos múltiples
- **Campos de peso** siempre presentes en sets (pueden ser null)
- **Enums** como strings simples para difficulty_level y exercise_type

---

## 📊 **RESUMEN DE IMPACTO**

| Componente | Cambio Principal | Tipo Resultado |
|------------|------------------|----------------|
| **Exercise Model** | JSON → Array cast | `array` |
| **Sets Models** | Campos peso agregados | `float\|null` |
| **API Responses** | Estructura con pesos | `object` con arrays |
| **Validations** | Reglas para arrays/pesos | `array\|numeric` |
| **Database** | Columnas peso agregadas | `DECIMAL(8,2)` |
| **Seeders** | Datos con pesos realistas | `float` values |

---

**🎯 Resultado:** Panel de profesores recibe datos estructurados como arrays y con información completa de pesos para funcionalidad profesional de gimnasio.
