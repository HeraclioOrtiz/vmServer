# 📱 PROMPT PARA DESARROLLO DE APP MÓVIL - SISTEMA DE GIMNASIOS

## 🎯 OBJETIVO DEL DESARROLLO

Implementar la **sección de gimnasios** en la app móvil de Villa Mitre, permitiendo a los **estudiantes** ver y gestionar sus entrenamientos asignados por profesores.

## 🏗️ ARQUITECTURA DEL SISTEMA

### **FLUJO JERÁRQUICO:**
1. **👑 Admin** → Asigna estudiantes a profesores
2. **👨‍🏫 Profesor** → Asigna plantillas de entrenamiento a estudiantes
3. **🎓 Estudiante** → Ve y ejecuta sus entrenamientos asignados
4. **📊 Sistema** → Genera progreso automático

### **ROLES Y PERMISOS:**
- **🎓 Estudiantes:** Solo pueden **VER** sus entrenamientos (no pueden modificar)
- **👨‍🏫 Profesores:** Pueden **ASIGNAR** plantillas a sus estudiantes
- **👑 Admins:** Pueden **GESTIONAR** todo el sistema

## 📋 FUNCIONALIDADES REQUERIDAS

### **🎓 FUNCIONALIDADES PARA ESTUDIANTES:**

#### **1. 🏠 Dashboard Principal**
- ✅ Resumen de entrenamientos de hoy
- ✅ Próximos entrenamientos programados
- ✅ Información del profesor asignado

#### **2. 📋 Mis Plantillas**
- ✅ Lista de todas las plantillas asignadas
- ✅ Filtros por estado (activas)
- ✅ Búsqueda por nombre de plantilla
- ✅ Vista de tarjetas con información clave

#### **3. 🏋️ Detalles de Entrenamiento**
- ✅ Ejercicios completos con series y repeticiones
- ✅ Instrucciones paso a paso
- ✅ Músculos objetivo y equipo necesario
- ✅ Notas del profesor

#### **4. 📅 Calendario de Entrenamientos**
- ✅ Vista semanal
- ✅ Días con entrenamientos marcados
- ✅ Múltiples entrenamientos por día
- ✅ Días de descanso claramente marcados

## 🔌 ENDPOINTS DISPONIBLES

### **🔐 AUTENTICACIÓN:**
```
POST /api/auth/login
POST /api/auth/logout
GET /api/auth/me
```

### **🎓 ESTUDIANTES:**
```
GET /api/student/my-templates              # Mis plantillas asignadas
GET /api/student/template/{id}/details     # Detalles de plantilla específica
GET /api/student/my-weekly-calendar        # Calendario semanal
```


## 📊 ESTRUCTURA DE DATOS

### **🎓 RESPUESTA DE PLANTILLAS DEL ESTUDIANTE:**
```json
{
  "data": {
    "professor": {
      "id": 2,
      "name": "Profesor Juan Pérez",
      "email": "profesor@villamitre.com"
    },
    "templates": [
      {
        "id": 8,
        "daily_template": {
          "title": "Atlético Explosivo 50'",
          "goal": "strength",
          "level": "advanced",
          "estimated_duration_min": 50,
          "exercises_count": 5
        },
        "start_date": "2025-09-27",
        "end_date": "2025-10-10",
        "frequency_days": ["Lunes"],
        "professor_notes": "Rutina avanzada",
        "status": "active"
      }
    ]
  }
}
```

### **🏋️ RESPUESTA DE EJERCICIOS DETALLADOS:**
```json
{
  "data": {
    "template": {
      "title": "Atlético Explosivo 50'",
      "estimated_duration_min": 50
    },
    "exercises": [
      {
        "order": 1,
        "exercise": {
          "name": "Sentadillas con Salto",
          "description": "Ejercicio pliométrico",
          "target_muscle_groups": ["cuádriceps", "glúteos"],
          "equipment": ["peso corporal"],
          "instructions": "Realizar sentadilla profunda..."
        },
        "sets": [
          {
            "set_number": 1,
            "reps": 8,
            "rest_seconds": 120,
            "notes": "Máxima explosividad"
          }
        ]
      }
    ]
  }
}
```

## 🎨 DISEÑO Y UX RECOMENDADO

### **📱 PANTALLAS PRINCIPALES:**

1. **🏠 Home/Dashboard**
   - Header con saludo y nombre del estudiante
   - Card con entrenamiento de hoy
   - Resumen semanal
   - Acceso rápido a calendario

2. **📋 Mis Entrenamientos**
   - Lista de plantillas con cards atractivas
   - Badges para nivel y duración
   - Indicador de días de la semana
   - Botón "Ver Detalles"

3. **🏋️ Detalle de Entrenamiento**
   - Header con título y duración
   - Lista de ejercicios expandibles
   - Botón "Iniciar Entrenamiento"
   - Información del profesor

4. **📅 Calendario**
   - Vista semanal por defecto
   - Días con entrenamientos destacados
   - Posibilidad de cambiar semana
   - Tap en día para ver entrenamientos


### **🎨 ELEMENTOS DE DISEÑO:**

#### **📊 Cards de Plantillas:**
```
┌─────────────────────────────────────┐
│ 🏋️ Atlético Explosivo 50'          │
│ ⏱️ 50 min  📊 Avanzado  📅 Lun     │
│ 👨‍🏫 Prof. Juan Pérez               │
│ 📝 "Rutina avanzada para..."       │
│                    [Ver Detalles] ▶ │
└─────────────────────────────────────┘
```

#### **🏋️ Cards de Ejercicios:**
```
┌─────────────────────────────────────┐
│ 1. 💪 Sentadillas con Salto         │
│ 🎯 Cuádriceps, Glúteos              │
│ 📊 3 series × 8 reps                │
│ ⏱️ Descanso: 2 min                  │
│ 📝 "Máxima explosividad"            │
│                         [Expandir] ▼ │
└─────────────────────────────────────┘
```

## 🔧 IMPLEMENTACIÓN TÉCNICA

### **📱 TECNOLOGÍAS RECOMENDADAS:**
- **React Native** o **Flutter** para desarrollo multiplataforma
- **Redux/MobX** para manejo de estado
- **AsyncStorage** para cache local
- **React Navigation** para navegación
- **Axios** para llamadas HTTP

### **🗄️ MANEJO DE DATOS:**
```javascript
// Estructura de store recomendada
const store = {
  auth: {
    user: {},
    token: "",
    isAuthenticated: false
  },
  gym: {
    templates: [],
    currentTemplate: null,
    calendar: [],
    professor: {},
    loading: false
  }
}
```

### **🔄 FLUJO DE DATOS:**
1. **Login** → Guardar token y datos de usuario
2. **Cargar plantillas** → GET /api/student/my-templates
3. **Mostrar lista** → Renderizar cards con datos
4. **Seleccionar plantilla** → GET /api/student/template/{id}/details
5. **Mostrar ejercicios** → Renderizar lista detallada
6. **Ejecutar entrenamiento** → UI interactiva con timers

## 🚀 PLAN DE DESARROLLO

### **📅 FASE 1 (2-3 semanas) - MVP:**
- ✅ Autenticación de estudiantes
- ✅ Lista de plantillas asignadas
- ✅ Vista detallada de ejercicios
- ✅ Navegación básica

### **📅 FASE 2 (2-3 semanas) - Funcionalidades Completas:**
- ✅ Calendario de entrenamientos
- ✅ Dashboard con resumen
- ✅ Información del profesor
- ✅ Filtros y búsqueda
- ✅ Optimización y pulido de UI

## ⚠️ CONSIDERACIONES IMPORTANTES

### **🔒 SEGURIDAD:**
- ✅ Validar token en cada request
- ✅ Manejar expiración de sesión
- ✅ No almacenar datos sensibles en local

### **📱 PERFORMANCE:**
- ✅ Cache de plantillas en AsyncStorage
- ✅ Lazy loading de imágenes
- ✅ Paginación en listas largas
- ✅ Optimización de re-renders

### **🌐 CONECTIVIDAD:**
- ✅ Manejo de errores de red
- ✅ Modo offline básico
- ✅ Retry automático de requests
- ✅ Indicadores de carga

### **♿ ACCESIBILIDAD:**
- ✅ Labels descriptivos
- ✅ Contraste adecuado
- ✅ Tamaños de texto ajustables
- ✅ Navegación por teclado

## 🧪 TESTING

### **✅ CASOS DE PRUEBA CRÍTICOS:**
1. **Login exitoso** con credenciales válidas
2. **Carga de plantillas** sin errores
3. **Navegación** entre pantallas fluida
4. **Manejo de errores** de red
5. **Persistencia** de datos en cache
6. **Visualización correcta** de ejercicios y series

## 📞 CONTACTO Y SOPORTE

- **🔧 Backend API:** Completamente funcional y testeado
- **📋 Documentación:** Disponible en `/docs/frontend/`
- **🧪 Testing:** Endpoints probados con datos reales
- **💬 Consultas:** Contactar al equipo de backend para dudas técnicas

---

**🎯 OBJETIVO:** Crear una app móvil intuitiva que permita a los estudiantes gestionar sus entrenamientos de forma eficiente y motivadora.

**⏰ TIMELINE:** 4-6 semanas para implementación completa

**🎉 RESULTADO:** App móvil funcional con sistema de gimnasios integrado
