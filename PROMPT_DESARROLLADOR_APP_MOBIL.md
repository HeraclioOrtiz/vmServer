# 📱 GUÍA DE IMPLEMENTACIÓN - APP MÓVIL GIMNASIO VILLA MITRE

**Para:** Desarrollador App Móvil  
**Fecha:** Octubre 2025  
**Versión API:** 2.0

---

## 🎯 **RESUMEN EJECUTIVO**

El backend del sistema de gimnasio ha sido **completamente reestructurado** con una nueva arquitectura jerárquica de asignaciones. Esta guía explica los cambios conceptuales y cómo implementarlos en la app móvil.

**NO incluye código específico**, solo la lógica de negocio y flujos que debes implementar.

---


---

## 📐 **CONCEPTOS CLAVE**



---



### **3. Sesiones de Progreso**

**Qué es:** El sistema genera automáticamente UNA sesión por cada día de entrenamiento.

**Datos importantes:**
- Fecha programada
- Estado (pending, completed, skipped, cancelled)
- Progreso por ejercicio (cuando se completa)
- Notas del estudiante
- Feedback del profesor
- Calificación general (1.0 - 5.0)

**Ejemplo:**
Si un estudiante tiene plantilla Lun/Mie/Vie durante 4 semanas:
→ Sistema genera 12 sesiones automáticamente
→ Una por cada día de entrenamiento

---

## 🔌 **NUEVOS ENDPOINTS PARA LA APP**

### **Endpoint 1: Mis Plantillas Asignadas**

**Ruta:** `GET /api/student/my-templates`

**Autenticación:** Token del estudiante

**Estructura de respuesta:**
```json
{
  "message": "string",
  "data": {
    "professor": {
      "id": "integer",
      "name": "string", 
      "email": "string"
    },
    "templates": [
      {
        "id": "integer",
        "daily_template": {
          "id": "integer",
          "title": "string",
          "goal": "string",
          "level": "string", 
          "estimated_duration_min": "integer",
          "tags": "array",
          "exercises_count": "integer"
        },
        "start_date": "string (YYYY-MM-DD)",
        "end_date": "string (YYYY-MM-DD) | null",
        "frequency": "array [integers]",
        "frequency_days": "array [strings]",
        "professor_notes": "string",
        "status": "string",
        "assigned_by": {
          "id": "integer",
          "name": "string"
        },
        "created_at": "string (ISO 8601)"
      }
    ]
  }
}
```

**Campos importantes:**
- `professor`: Información del profesor asignado
- `templates`: Array de plantillas asignadas
- `frequency`: Array de números (0=Dom, 1=Lun, 2=Mar, etc.)
- `frequency_days`: Array de nombres de días en español
- `exercises_count`: Número de ejercicios en la plantilla

**Cuándo usarlo:** 
- Al abrir la app
- En pantalla principal
- Para mostrar rutinas activas

---

### **Endpoint 2: Detalles de Plantilla**

**Ruta:** `GET /api/student/template/{id}/details`

**Autenticación:** Token del estudiante

**Estructura de respuesta:**
```json
{
  "message": "string",
  "data": {
    "assignment_info": {
      "id": "integer",
      "start_date": "string (YYYY-MM-DD)",
      "end_date": "string (YYYY-MM-DD) | null",
      "frequency": "array [integers]",
      "frequency_days": "array [strings]",
      "professor_notes": "string",
      "status": "string",
      "assigned_by": {
        "id": "integer",
        "name": "string",
        "email": "string"
      }
    },
    "template": {
      "id": "integer",
      "title": "string",
      "goal": "string",
      "level": "string",
      "estimated_duration_min": "integer",
      "tags": "array",
      "created_at": "string (ISO 8601)"
    },
    "exercises": [
      {
        "id": "integer",
        "order": "integer",
        "exercise": {
          "id": "integer",
          "name": "string",
          "description": "string",
          "target_muscle_groups": "array [strings]",
          "equipment": "string",
          "difficulty_level": "string",
          "instructions": "string"
        },
        "sets": [
          {
            "id": "integer",
            "set_number": "integer",
            "reps_min": "integer",
            "reps_max": "integer",
            "rpe_target": "float",
            "rest_seconds": "integer",
            "notes": "string | null"
          }
        ],
        "notes": "string | null"
      }
    ]
  }
}
```

**Campos importantes:**
- `assignment_info`: Información de la asignación (periodo, frecuencia, profesor)
- `template`: Información básica de la plantilla
- `exercises`: Array completo de ejercicios con sets
- `target_muscle_groups`: Array de músculos trabajados
- `rpe_target`: Escala RPE (Rate of Perceived Exertion) 1.0-10.0
- `rest_seconds`: Tiempo de descanso entre sets

**Cuándo usarlo:**
- Al tocar una plantilla
- Para mostrar ejercicios del día
- Antes de empezar entrenamiento

---

### **Endpoint 3: Calendario Semanal**

**Ruta:** `GET /api/student/my-weekly-calendar`

**Parámetros opcionales:**
- `date`: Ver semana específica (default: semana actual)

**Autenticación:** Token del estudiante

**Estructura de respuesta:**
```json
{
  "message": "string",
  "data": {
    "week_start": "string (YYYY-MM-DD)",
    "week_end": "string (YYYY-MM-DD)",
    "days": [
      {
        "date": "string (YYYY-MM-DD)",
        "day_name": "string",
        "day_short": "string",
        "day_number": "integer",
        "has_workouts": "boolean",
        "assignments": [
          {
            "id": "integer",
            "daily_template": {
              "id": "integer",
              "title": "string",
              "goal": "string",
              "level": "string",
              "estimated_duration_min": "integer"
            },
            "professor_notes": "string",
            "assigned_by": {
              "name": "string"
            }
          }
        ]
      }
    ]
  }
}
```

**Campos importantes:**
- `week_start` / `week_end`: Rango de la semana mostrada
- `days`: Array de 7 días (Domingo a Sábado)
- `day_name`: Nombre completo del día en inglés
- `day_short`: Abreviación del día (Mon, Tue, etc.)
- `has_workouts`: Boolean indicando si hay entrenamientos ese día
- `assignments`: Array de plantillas programadas para ese día

**Cuándo usarlo:**
- Vista de calendario semanal
- Dashboard principal
- Planificación semanal

---

### **Endpoint 4: Completar Progreso de Sesión**

**Ruta:** `POST /api/student/progress/{session_id}/complete`

**Autenticación:** Token del estudiante

**Estructura de envío:**
```json
{
  "exercise_progress": [
    {
      "exercise_id": "integer",
      "sets": [
        {
          "set_number": "integer",
          "reps_completed": "integer",
          "weight": "float | null",
          "rpe_actual": "float | null",
          "notes": "string | null"
        }
      ]
    }
  ],
  "student_notes": "string | null",
  "completed_at": "string (ISO 8601)"
}
```

**Estructura de respuesta:**
```json
{
  "message": "string",
  "data": {
    "session_id": "integer",
    "status": "string",
    "completed_at": "string (ISO 8601)",
    "exercises_completed": "integer",
    "total_exercises": "integer"
  }
}
```

**Campos importantes:**
- `exercise_progress`: Array con progreso de cada ejercicio
- `reps_completed`: Repeticiones realmente realizadas
- `weight`: Peso usado (opcional)
- `rpe_actual`: RPE percibido por el estudiante
- `student_notes`: Notas generales de la sesión

**Cuándo usarlo:**
- Al finalizar una sesión de entrenamiento
- Para enviar progreso al backend

---

## 🎨 **FLUJOS DE USUARIO RECOMENDADOS**

### **Flujo 1: Ver Mis Entrenamientos**

**Paso 1:** Usuario abre la app
→ Llamar `GET /api/student/my-templates`
→ Mostrar plantillas activas

**Paso 2:** Usuario ve calendario
→ Llamar `GET /api/student/my-weekly-calendar`
→ Mostrar días con entrenamiento marcados

**Paso 3:** Usuario toca un día con entrenamiento
→ Usar datos del calendario
→ Llamar `GET /api/student/template/{id}/details`
→ Mostrar ejercicios del día

---

### **Flujo 2: Realizar Entrenamiento**

**Paso 1:** Usuario inicia sesión de entrenamiento
→ Cargar detalles de plantilla
→ Mostrar primer ejercicio

**Paso 2:** Usuario completa cada set
→ Guardar localmente:
  - Repeticiones reales
  - Peso usado
  - RPE percibido
  - Notas personales

**Paso 3:** Usuario completa todos los ejercicios
→ Enviar progreso al backend
→ Endpoint: `POST /api/student/progress/{session_id}/complete`
→ Usar estructura definida en Endpoint 4

**Ejemplo de datos a enviar:**
```json
{
  "exercise_progress": [
    {
      "exercise_id": 2,
      "sets": [
        {
          "set_number": 1,
          "reps_completed": 8,
          "weight": 60.0,
          "rpe_actual": 8.5,
          "notes": "Buena forma"
        },
        {
          "set_number": 2,
          "reps_completed": 7,
          "weight": 60.0,
          "rpe_actual": 9.0,
          "notes": null
        }
      ]
    }
  ],
  "student_notes": "Me sentí bien hoy",
  "completed_at": "2025-10-03T14:30:00Z"
}
```

---

### **Flujo 3: Ver Historial**

**Paso 1:** Usuario quiere ver entrenamientos pasados
→ Llamar `GET /api/student/my-progress?status=completed`
→ Mostrar lista de sesiones completadas

**Paso 2:** Usuario toca una sesión
→ Mostrar detalles:
  - Ejercicios realizados
  - Sets completados
  - Pesos usados
  - Progreso vs objetivo
  - Feedback del profesor (si existe)

---

## 📊 **EJEMPLOS DE VALORES REALES**

### **Valores de `goal`:**
- `"strength"` - Fuerza
- `"hypertrophy"` - Hipertrofia  
- `"endurance"` - Resistencia
- `"general"` - General
- `"weight_loss"` - Pérdida de peso

### **Valores de `level`:**
- `"beginner"` - Principiante
- `"intermediate"` - Intermedio
- `"advanced"` - Avanzado

### **Valores de `status`:**
- `"active"` - Activo
- `"paused"` - Pausado
- `"completed"` - Completado
- `"cancelled"` - Cancelado

### **Valores de `difficulty_level`:**
- `"beginner"` - Principiante
- `"intermediate"` - Intermedio
- `"advanced"` - Avanzado

### **Ejemplos de `target_muscle_groups`:**
```json
[
  "cuádriceps",
  "glúteo mayor", 
  "isquiotibiales",
  "erectores espinales",
  "pectoral mayor",
  "deltoides anterior",
  "tríceps",
  "dorsal ancho",
  "bíceps"
]
```

### **Ejemplos de `equipment`:**
- `"barra, rack, discos"`
- `"banco, barra, discos"`
- `"barra de dominadas"`
- `"mancuernas"`
- `"peso corporal"`

### **Rangos de `rpe_target`:**
- Valores de 1.0 a 10.0
- Ejemplos: 7.5, 8.0, 8.5, 9.0
- 10.0 = Máximo esfuerzo

### **Ejemplos de `frequency`:**
- `[1, 3, 5]` = Lunes, Miércoles, Viernes
- `[1, 2, 3, 4, 5]` = Lunes a Viernes
- `[0, 6]` = Domingo y Sábado

---

## 📱 **PANTALLAS SUGERIDAS**

### **Pantalla 1: Dashboard**

**Elementos:**
- Profesor asignado (nombre, foto)
- Plantillas activas (cards)
- Calendario semanal compacto
- Próximo entrenamiento destacado

**Datos a mostrar:**
- Días de esta semana con entrenamiento
- Plantilla de hoy (si corresponde)
- Progreso semanal (X de Y completados)

---

### **Pantalla 2: Calendario Semanal**

**Elementos:**
- Vista de 7 días
- Indicadores visuales:
  - Día con entrenamiento (marca especial)
  - Día completado (check verde)
  - Día pendiente (pendiente)
  - Día saltado (marca gris)

**Interacción:**
- Tocar día → Ver detalles de entrenamiento
- Deslizar → Cambiar semana

---

### **Pantalla 3: Detalle de Plantilla**

**Elementos:**
- Título de plantilla
- Goal (fuerza, hipertrofia, resistencia)
- Nivel (principiante, intermedio, avanzado)
- Duración estimada
- **Lista de ejercicios:**
  - Foto/icono del ejercicio
  - Nombre
  - Músculos trabajados
  - Número de sets
- Notas del profesor

**Interacción:**
- Tocar ejercicio → Ver instrucciones detalladas
- Botón "Iniciar entrenamiento"

---

### **Pantalla 4: Durante Entrenamiento**

**Elementos:**
- Timer de descanso
- Ejercicio actual (foto, nombre)
- Set actual (X de Y)
- Objetivo (ej: 8-12 reps @ RPE 8)
- **Inputs:**
  - Repeticiones realizadas
  - Peso usado
  - RPE percibido (slider 1-10)
  - Notas (opcional)

**Navegación:**
- Botón "Set completado" → Siguiente set
- Botón "Ejercicio completado" → Siguiente ejercicio
- Botón "Finalizar sesión"

---

### **Pantalla 5: Resumen Post-Entrenamiento**

**Elementos:**
- Duración total
- Ejercicios completados
- Sets totales
- Comparación vs objetivos
- Campo para notas generales
- Botón "Enviar progreso"

**Después de enviar:**
- Mostrar confirmación
- Actualizar calendario (marcar día completado)
- Volver a dashboard

---

## 🔐 **CONSIDERACIONES DE SEGURIDAD**

### **1. Autenticación**
- Todos los endpoints requieren token Sanctum
- Token debe enviarse en header: `Authorization: Bearer {token}`
- Token se obtiene al login

### **2. Validaciones**
- Solo puedes ver TUS plantillas asignadas
- Solo puedes completar TUS sesiones
- Backend valida automáticamente permisos

### **3. Datos Sensibles**
- No guardes credenciales en local storage
- Token en almacenamiento seguro del dispositivo
- Limpiar token al logout

---

## 📊 **DATOS QUE DEBES GUARDAR LOCALMENTE**

### **Mientras el usuario entrena (sin conexión):**
- Progreso de sets en tiempo real
- Repeticiones, pesos, RPE
- Notas por ejercicio
- Timestamp de cada set

### **Al terminar entrenamiento:**
- Sincronizar con backend
- Enviar progreso completo
- Limpiar datos locales al confirmar

### **Persistente:**
- Token de autenticación
- Preferencias de usuario
- Cache de plantillas (opcional)

---

## 🎯 **DIFERENCIAS CON SISTEMA ANTERIOR**

### **Cambio 1: Fuente de Datos**

**Antes:** 
- Endpoint directo de plantillas asignadas

**Ahora:**
- Endpoint de plantillas con información de asignación
- Incluye frecuencia, periodo, profesor

### **Cambio 2: Calendario**

**Antes:**
- No existía concepto de calendario
- Usuario decidía cuándo entrenar

**Ahora:**
- Calendario generado automáticamente
- Días específicos marcados
- Sistema sugiere cuándo entrenar

### **Cambio 3: Progreso**

**Antes:**
- Progreso por plantilla general

**Ahora:**
- Progreso por SESIÓN específica
- Cada día es una sesión única
- Tracking más granular

---

## 📋 **CHECKLIST DE IMPLEMENTACIÓN**





### **Fase 3: Calendario**
- [ ] Vista semanal de 7 días
- [ ] Marcar días con entrenamiento
- [ ] Mostrar plantilla del día
- [ ] Navegación entre semanas

### **Fase 4: Plantillas**
- [ ] Detalle de plantilla
- [ ] Lista de ejercicios
- [ ] Sets por ejercicio
- [ ] Notas del profesor

### **Fase 5: Entrenamiento**
- [ ] Pantalla de ejecución
- [ ] Timer de descanso
- [ ] Inputs de progreso
- [ ] Guardar datos localmente
- [ ] Enviar al backend

### **Fase 6: Historial**
- [ ] Lista de sesiones completadas
- [ ] Detalle de sesión pasada
- [ ] Gráficos de progreso (opcional)
- [ ] Feedback del profesor

---

## 🚀 **PRIORIDADES DE DESARROLLO**



### **Media Prioridad:**
1. Calendario semanal
2. Historial de entrenamientos
3. Feedback del profesor
4. Timer de descanso

### **Baja Prioridad (Nice to have):**
1. Gráficos de progreso
2. Comparativas semana a semana
3. Notificaciones de entrenamiento
4. Modo offline completo

---

## 💡 **RECOMENDACIONES TÉCNICAS**

### **1. Manejo de Estado**
- Usa estado global para:
  - Usuario autenticado
  - Plantillas activas
  - Entrenamiento en curso
- Cache de datos del backend
- Sincronización periódica

### **2. Offline First**
- Permitir entrenar sin conexión
- Guardar progreso localmente
- Sincronizar cuando haya conexión
- Mostrar indicador de sincronización

### **3. UX**
- Loading states claros
- Mensajes de error informativos
- Confirmaciones antes de acciones importantes
- Animaciones suaves entre pantallas

### **4. Performance**
- Cache de imágenes de ejercicios
- Lazy loading de listas
- Pagination si hay muchas sesiones
- Optimizar requests (evitar llamadas redundantes)

---

## ❓ **PREGUNTAS FRECUENTES**

### **Q: ¿Puedo usar los endpoints viejos?**
**A:** No, están deprecated. Usa los nuevos bajo `/api/student/*`

### **Q: ¿Qué pasa si el estudiante no tiene profesor?**
**A:** El endpoint devuelve mensaje indicando "No tienes profesor asignado" y lista vacía.

### **Q: ¿Puedo mostrar varias semanas a la vez?**
**A:** Sí, llama al endpoint de calendario con diferentes fechas.

### **Q: ¿Cómo sé si una sesión está completada?**
**A:** Viene en el campo `status` de cada sesión: "pending", "completed", "skipped", "cancelled"

### **Q: ¿Puedo editar una sesión ya completada?**
**A:** No por ahora. Solo completar sesiones pendientes.

### **Q: ¿Cómo manejo múltiples plantillas activas?**
**A:** El backend puede devolver múltiples. Mostrar tabs o lista. Usuario elige cuál hacer hoy.

---

## 📞 **SOPORTE Y DOCUMENTACIÓN**

### **Endpoints documentados:**
- Ver archivo `GUIA_VERIFICACION_PANEL_ADMIN_PROFESORES.md`
- Testing realizado en `test_student_endpoints.php`

### **Ejemplos de respuestas:**
- Incluidos en la guía de verificación
- Puedes hacer requests de prueba al backend de desarrollo

### **Ambiente de testing:**
- URL base: `http://localhost:8000/api`
- Usuario de prueba: maria.garcia@villamitre.com
- Profesor de prueba: profesor@villamitre.com

---

## ✅ **CRITERIOS DE ACEPTACIÓN**

### **El MVP está listo cuando:**
1. ✅ Usuario puede hacer login
2. ✅ Usuario ve sus plantillas asignadas
3. ✅ Usuario ve ejercicios del día
4. ✅ Usuario puede completar un entrenamiento
5. ✅ Usuario envía progreso al backend
6. ✅ Usuario ve su historial básico

### **La versión completa incluye:**
1. ✅ Todo lo del MVP
2. ✅ Calendario semanal funcional
3. ✅ Timer de descanso
4. ✅ Feedback del profesor visible
5. ✅ Modo offline básico
6. ✅ UX pulida y animaciones

---

**Fin del documento. ¡Éxito con la implementación!** 🚀
