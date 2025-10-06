# 🔧 SOLUCIÓN ERROR 422 - CREAR EJERCICIOS

**Fecha:** 2025-10-06 13:57  
**Endpoint:** `POST /admin/gym/exercises`

---

## 🚨 **PROBLEMA IDENTIFICADO**

### **Error actual:**
```json
{
  "name": "Heraclio Ejercicio",
  "target_muscle_groups": "chest,back",     // ❌ INCORRECTO (string)
  "equipment": "machine",
  "tags": "hiperhinflacion"                 // ❌ INCORRECTO (string)
}
```

### **Causa del error 422:**
Los campos `target_muscle_groups` y `tags` **DEBEN SER ARRAYS**, no strings.

---

## ✅ **FORMATO CORRECTO REQUERIDO**

### **Payload correcto:**
```json
{
  "name": "Heraclio Ejercicio",
  "target_muscle_groups": ["chest", "back"],     // ✅ ARRAY
  "equipment": "machine",                        // ✅ STRING (este está bien)
  "tags": ["hiperhinflacion"]                    // ✅ ARRAY
}
```

---

## 📋 **ESPECIFICACIONES COMPLETAS**

### **1. Campos que DEBEN ser ARRAYS:**
```json
{
  "muscle_groups": ["grupo1", "grupo2"],           // Array opcional
  "target_muscle_groups": ["musculo1", "musculo2"], // Array opcional  
  "tags": ["tag1", "tag2"]                        // Array opcional
}
```

### **2. Campos que son STRINGS:**
```json
{
  "name": "Nombre del ejercicio",                 // String requerido
  "description": "Descripción...",                // String opcional
  "movement_pattern": "push horizontal",          // String opcional
  "equipment": "barra, banco",                    // String opcional
  "difficulty_level": "intermediate",             // String opcional (enum)
  "instructions": "Instrucciones..."              // String opcional
}
```

---

## 🎯 **VALIDACIONES ESPECÍFICAS**

### **Arrays (target_muscle_groups, muscle_groups, tags):**
- **Tipo:** `array` (no string)
- **Elementos:** Cada elemento debe ser `string`
- **Límites:** 
  - `target_muscle_groups`: máximo 100 caracteres por elemento
  - `muscle_groups`: máximo 100 caracteres por elemento  
  - `tags`: máximo 50 caracteres por elemento

### **difficulty_level (enum):**
- **Valores válidos:** `"beginner"`, `"intermediate"`, `"advanced"`
- **Tipo:** `string`

---

## 📝 **EJEMPLOS DE VALORES VÁLIDOS**

### **target_muscle_groups (ejemplos del sistema):**
```json
[
  "pectoral mayor", "pectoral menor", "tríceps", 
  "deltoides anterior", "cuádriceps", "glúteo mayor",
  "isquiotibiales", "dorsal ancho", "trapecio",
  "erectores espinales", "abdominales"
]
```

### **muscle_groups (ejemplos del sistema):**
```json
[
  "pecho", "espalda", "piernas", "hombros", 
  "tríceps", "bíceps", "glúteos", "core"
]
```

### **tags (ejemplos del sistema):**
```json
[
  "compuesto", "aislamiento", "fuerza", "hipertrofia",
  "funcional", "básico", "avanzado", "cardio"
]
```

---

## 🔧 **PAYLOAD COMPLETO CORREGIDO**

```json
{
  "name": "Heraclio Ejercicio",
  "description": "Descripción del ejercicio de Heraclio",
  "muscle_groups": ["pecho", "espalda"],
  "target_muscle_groups": ["pectoral mayor", "dorsal ancho"],
  "movement_pattern": "push horizontal",
  "equipment": "machine",
  "difficulty_level": "intermediate",
  "tags": ["hiperhinflacion", "funcional"],
  "instructions": "Instrucciones detalladas del ejercicio..."
}
```

---

## ⚡ **SOLUCIÓN RÁPIDA**

### **Cambios necesarios en tu payload:**
1. **target_muscle_groups:** `"chest,back"` → `["chest", "back"]`
2. **tags:** `"hiperhinflacion"` → `["hiperhinflacion"]`

### **Payload mínimo funcional:**
```json
{
  "name": "Heraclio Ejercicio",
  "target_muscle_groups": ["chest", "back"],
  "tags": ["hiperhinflacion"]
}
```

---

## 🎯 **RESUMEN**

| Campo | Tipo Requerido | Ejemplo |
|-------|----------------|---------|
| `target_muscle_groups` | **Array** | `["chest", "back"]` |
| `muscle_groups` | **Array** | `["pecho", "espalda"]` |
| `tags` | **Array** | `["hiperhinflacion"]` |
| `equipment` | **String** | `"machine"` |
| `difficulty_level` | **String** (enum) | `"intermediate"` |

**🔑 Clave:** Los campos múltiples siempre son **arrays**, no strings separados por comas.
