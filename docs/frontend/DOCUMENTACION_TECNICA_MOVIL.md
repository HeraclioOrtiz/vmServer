# 📱 DOCUMENTACIÓN TÉCNICA - APP MÓVIL GIMNASIOS

## 🎯 RESUMEN EJECUTIVO

**Sistema de gimnasios** para app móvil Villa Mitre que permite a **estudiantes** ver entrenamientos asignados por **profesores**.

## 🔌 API ENDPOINTS

### **🔐 Autenticación**
```bash
POST /api/auth/login
{
  "dni": "33333333",
  "password": "estudiante123"
}
```

**Respuesta:**
```json
{
  "data": {
    "token": "368|z06jXXJKvkMYNKO46pTXlUm6KlByyLRH2p0jwfHY1b215b55",
    "user": {
      "id": 3,
      "name": "Estudiante María García",
      "email": "estudiante@villamitre.com",
      "is_professor": false
    }
  }
}
```

### **📋 Plantillas del Estudiante**
```bash
GET /api/student/my-templates
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "data": {
    "professor": {
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
        "frequency_days": ["Lunes"],
        "professor_notes": "Rutina avanzada",
        "status": "active"
      }
    ]
  }
}
```

### **🏋️ Detalles de Plantilla**
```bash
GET /api/student/template/{id}/details
Authorization: Bearer {token}
```

### **📅 Calendario Semanal**
```bash
GET /api/student/my-weekly-calendar
Authorization: Bearer {token}
```

## 📱 PANTALLAS REQUERIDAS

### **1. 🏠 Dashboard**
- Resumen de entrenamientos de hoy
- Próximos entrenamientos
- Información del profesor

### **2. 📋 Lista de Plantillas**
- Cards con título, duración, nivel
- Filtros por estado
- Información del profesor

### **3. 🏋️ Detalle de Entrenamiento**
- Lista de ejercicios con series
- Instrucciones detalladas
- Botón "Iniciar Entrenamiento"

### **4. 📅 Calendario**
- Vista semanal
- Días con entrenamientos marcados
- Navegación entre semanas

## 🔧 IMPLEMENTACIÓN

### **📱 Tecnologías**
- React Native / Flutter
- Redux para estado
- AsyncStorage para cache
- Axios para HTTP

### **🗄️ Estructura de Estado**
```javascript
{
  auth: { user, token, isAuthenticated },
  gym: { templates, calendar, professor, loading }
}
```

### **🔄 Flujo Principal**
1. Login → Guardar token
2. Cargar plantillas → Mostrar lista
3. Seleccionar plantilla → Mostrar detalles
4. Ejecutar entrenamiento → UI interactiva

## 📊 DATOS DE EJEMPLO

**Estudiante de prueba:**
- DNI: `33333333`
- Password: `estudiante123`
- Nombre: `María García`

**Plantillas asignadas:** 4
**Profesor:** Juan Pérez
**Entrenamientos por semana:** 4 días

## ⚡ FUNCIONALIDADES MVP

### **✅ Fase 1 (3 semanas):**
- Autenticación
- Lista de plantillas
- Detalles de ejercicios
- Navegación básica

### **✅ Fase 2 (2-3 semanas):**
- Calendario
- Dashboard
- Filtros y búsqueda
- Optimización y pulido

## 🚀 CONSIDERACIONES

### **🔒 Seguridad:**
- Validar token en requests
- Manejar expiración de sesión
- No almacenar datos sensibles

### **📱 Performance:**
- Cache local de plantillas
- Lazy loading
- Optimización de renders

### **🌐 Conectividad:**
- Manejo de errores de red
- Retry automático
- Indicadores de carga

## 📞 SOPORTE

- **API:** Completamente funcional
- **Testing:** Datos reales disponibles
- **Documentación:** En `/docs/frontend/`
- **Contacto:** Equipo backend para consultas

**🎯 Objetivo:** App intuitiva para gestión de entrenamientos
**⏰ Timeline:** 5-6 semanas implementación completa
