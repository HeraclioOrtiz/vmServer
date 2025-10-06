# 🎯 **PROMPT PARA EQUIPO FRONTEND - SISTEMA DE ASIGNACIONES**

## 📋 **CONTEXTO DEL PROYECTO**

**¡Hola equipo frontend!** 👋

Hemos completado exitosamente la implementación del **Sistema de Asignaciones Jerárquico** en el backend. El sistema está **100% funcional, testado y listo** para integración frontend.

---

## 🎯 **TU MISIÓN**

Implementar las interfaces de usuario para un sistema de asignaciones que permite:

1. **👑 Administradores** asignan estudiantes a profesores
2. **👨‍🏫 Profesores** asignan plantillas de entrenamiento a sus estudiantes
3. **🎓 Estudiantes** siguen sus entrenamientos programados

---

## 🏗️ **ARQUITECTURA DEL SISTEMA**

### **🔄 FLUJO PRINCIPAL:**
```
ADMIN crea asignación → PROFESOR ve estudiante → PROFESOR asigna plantilla → SISTEMA genera progreso automático
```

### **👥 ROLES Y PERMISOS:**
- **Admin:** `user.is_admin = true`
- **Profesor:** `user.is_professor = true`
- **Estudiante:** `user.is_professor = false && user.is_admin = false`

---

## 🛠️ **STACK TECNOLÓGICO REQUERIDO**

```bash
# Instalar dependencias
npm install react@^18.2.0 typescript@^5.0.0
npm install @tanstack/react-query@^4.32.0 axios@^1.5.0
npm install react-router-dom@^6.15.0 tailwindcss@^3.3.0
npm install @headlessui/react@^1.7.0 @heroicons/react@^2.0.0
npm install react-hook-form@^7.45.0 zod@^3.22.0
npm install date-fns@^2.30.0 chart.js@^4.4.0 react-chartjs-2@^5.2.0
```

---

## 🔐 **CONFIGURACIÓN DE API**

### **🌐 BASE URL:**
```typescript
const API_BASE_URL = 'http://127.0.0.1:8000/api';
```

### **🔑 AUTENTICACIÓN:**
```typescript
// Login endpoint
POST /auth/login
{
  "dni": "22222222",
  "password": "profesor123"
}

// Response
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 2,
    "name": "Profesor Juan Pérez",
    "email": "profesor@villamitre.com",
    "is_admin": false,
    "is_professor": true
  }
}

// Headers para requests autenticados
Authorization: Bearer {token}
```

---

## 📊 **ENDPOINTS CRÍTICOS A IMPLEMENTAR**

### **👑 ADMIN ENDPOINTS:**

#### **📋 Dashboard Estadísticas:**
```typescript
GET /admin/assignments-stats
// Response:
{
  "total_professors": 2,
  "total_students": 19,
  "active_assignments": 1,
  "unassigned_students": 18,
  "assignment_rate": 5.3
}
```

#### **📝 Gestión de Asignaciones:**
```typescript
// Listar asignaciones
GET /admin/assignments?page=1&per_page=20&status=active

// Crear asignación
POST /admin/assignments
{
  "professor_id": 2,
  "student_id": 3,
  "start_date": "2025-09-26",
  "admin_notes": "Asignación inicial"
}

// Estudiantes sin asignar
GET /admin/students/unassigned
```

### **👨‍🏫 PROFESOR ENDPOINTS:**

#### **🎓 Mis Estudiantes:**
```typescript
GET /professor/my-students
// Response:
{
  "data": [
    {
      "id": 1,
      "student": {
        "id": 3,
        "name": "Estudiante María García"
      },
      "start_date": "2025-09-26",
      "status": "active"
    }
  ]
}
```

#### **📝 Asignar Plantilla:**
```typescript
POST /professor/assign-template
{
  "professor_student_assignment_id": 1,
  "daily_template_id": 84,
  "start_date": "2025-09-26",
  "frequency": [1, 3, 5], // Lun, Mie, Vie
  "professor_notes": "Rutina inicial"
}
```

#### **📅 Calendario y Estadísticas:**
```typescript
GET /professor/weekly-calendar?start_date=2025-09-26
GET /professor/my-stats
GET /professor/today-sessions
```

---

## 🎨 **COMPONENTES PRINCIPALES A CREAR**

### **1. 👑 PANEL ADMINISTRADOR**

#### **📊 AdminDashboard.tsx**
```typescript
// Mostrar 4 tarjetas de estadísticas:
// - Total Profesores
// - Total Estudiantes  
// - Asignaciones Activas
// - Estudiantes Sin Asignar

// Gráfico de barras con asignaciones por profesor
// Lista de últimas asignaciones creadas
```

#### **📋 AssignmentsManagement.tsx**
```typescript
// Tabla con columnas:
// - Profesor (nombre + avatar)
// - Estudiante (nombre)
// - Estado (badge colorido)
// - Fecha Inicio
// - Acciones (editar/eliminar)

// Filtros: profesor, estudiante, estado, búsqueda
// Paginación
// Modal para crear nueva asignación
```

#### **🎓 UnassignedStudents.tsx**
```typescript
// Grid de tarjetas de estudiantes sin asignar
// Botón "Asignar" que abre modal de selección de profesor
// Búsqueda y filtros por nombre
```

### **2. 👨‍🏫 PANEL PROFESOR**

#### **🎓 MyStudents.tsx**
```typescript
// Grid de tarjetas de estudiantes asignados
// Cada tarjeta muestra:
// - Nombre y avatar del estudiante
// - Fecha de asignación
// - Plantillas activas (count)
// - Progreso general (%)
// - Botones: "Asignar Plantilla", "Ver Progreso"
```

#### **📅 WeeklyCalendar.tsx**
```typescript
// Calendario semanal con 7 columnas (días)
// Cada día muestra sesiones programadas:
// - Nombre del estudiante
// - Plantilla asignada
// - Estado (pendiente/completado)
// - Hora (si aplica)

// Navegación entre semanas
// Click en sesión abre modal de detalles
```

#### **📝 AssignTemplateModal.tsx**
```typescript
// Modal con wizard de 3 pasos:
// 1. Seleccionar plantilla (grid con preview)
// 2. Configurar fechas y frecuencia
// 3. Agregar notas del profesor

// Validaciones en cada paso
// Preview de sesiones que se generarán
```

#### **📊 ProfessorStats.tsx**
```typescript
// Dashboard con métricas del profesor:
// - Total estudiantes asignados
// - Plantillas activas
// - Sesiones completadas vs pendientes
// - Tasa de adherencia promedio

// Gráficos: línea de progreso semanal, dona de estados
```

### **3. 🎓 PANEL ESTUDIANTE (OPCIONAL)**

#### **📋 MyWorkouts.tsx**
```typescript
// Lista de entrenamientos programados
// Filtros: próximos, completados, esta semana
// Cada item muestra:
// - Plantilla y ejercicios
// - Fecha programada
// - Estado actual
// - Botón "Iniciar" o "Ver Detalles"
```

---

## 🔧 **HOOKS PERSONALIZADOS REQUERIDOS**

### **📊 useAssignments.ts**
```typescript
export const useAssignments = (filters?: AssignmentFilters) => {
  return useQuery(
    ['assignments', filters],
    () => api.get('/admin/assignments', { params: filters }),
    { staleTime: 5 * 60 * 1000 }
  );
};

export const useCreateAssignment = () => {
  const queryClient = useQueryClient();
  return useMutation(
    (data: CreateAssignmentRequest) => api.post('/admin/assignments', data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['assignments']);
        queryClient.invalidateQueries(['admin-stats']);
        toast.success('Asignación creada exitosamente');
      }
    }
  );
};
```

### **👨‍🏫 useProfessor.ts**
```typescript
export const useProfessorStudents = () => {
  return useQuery(
    ['professor-students'],
    () => api.get('/professor/my-students'),
    { staleTime: 2 * 60 * 1000 }
  );
};

export const useAssignTemplate = () => {
  const queryClient = useQueryClient();
  return useMutation(
    (data: AssignTemplateRequest) => api.post('/professor/assign-template', data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(['professor-students']);
        queryClient.invalidateQueries(['weekly-calendar']);
        toast.success('Plantilla asignada exitosamente');
      }
    }
  );
};
```

---

## 🎨 **GUÍA DE DISEÑO**

### **🎨 COLORES:**
```css
:root {
  --primary: #3B82F6;      /* Azul principal */
  --success: #10B981;      /* Verde éxito */
  --warning: #F59E0B;      /* Amarillo advertencia */
  --error: #EF4444;        /* Rojo error */
  --gray-50: #F9FAFB;      /* Fondo claro */
  --gray-900: #111827;     /* Texto oscuro */
}
```

### **📱 RESPONSIVE:**
- **Mobile first:** Diseño optimizado para móviles
- **Breakpoints:** sm(640px), md(768px), lg(1024px), xl(1280px)
- **Grid:** 1 columna móvil, 2-3 desktop

### **🎭 ESTADOS:**
```typescript
// Estados de asignaciones
'active' → Badge verde
'paused' → Badge amarillo  
'completed' → Badge azul
'cancelled' → Badge rojo

// Estados de sesiones
'pending' → Icono reloj
'completed' → Icono check verde
'skipped' → Icono x amarillo
```

---

## 📋 **TAREAS ESPECÍFICAS**

### **🚀 SPRINT 1 (Días 1-3): Base**
- [ ] Setup proyecto React + TypeScript + Tailwind
- [ ] Configurar React Query y Axios
- [ ] Implementar autenticación (login/logout)
- [ ] Crear layout base con navegación
- [ ] Protección de rutas por roles

### **📊 SPRINT 2 (Días 4-6): Admin Panel**
- [ ] Dashboard con estadísticas (4 tarjetas)
- [ ] Tabla de asignaciones con filtros
- [ ] Modal crear asignación
- [ ] Lista estudiantes sin asignar
- [ ] Funcionalidad editar/eliminar

### **👨‍🏫 SPRINT 3 (Días 7-9): Professor Panel**
- [ ] Grid de estudiantes asignados
- [ ] Modal asignar plantilla (wizard)
- [ ] Calendario semanal
- [ ] Dashboard estadísticas profesor
- [ ] Sesiones del día

### **✨ SPRINT 4 (Día 10): Pulido**
- [ ] Loading states y skeletons
- [ ] Error boundaries
- [ ] Notificaciones toast
- [ ] Responsive final
- [ ] Testing básico

---

## 🧪 **TESTING MÍNIMO REQUERIDO**

```typescript
// Testear componentes críticos:
- AssignmentTable.test.tsx
- CreateAssignmentModal.test.tsx
- StudentCard.test.tsx
- WeeklyCalendar.test.tsx

// Testear hooks:
- useAssignments.test.ts
- useProfessor.test.ts

// Test E2E crítico:
- Flujo completo: Login → Crear asignación → Asignar plantilla
```

---

## 🔍 **DATOS DE PRUEBA**

### **👤 USUARIOS DE TESTING:**
```typescript
// Profesor
dni: "22222222"
password: "profesor123"

// Admin  
dni: "11111111"
password: "admin123"
```

### **📊 DATOS DISPONIBLES:**
- **Profesores:** 2 usuarios
- **Estudiantes:** 19 usuarios
- **Plantillas:** 20 plantillas con ejercicios completos
- **Ejercicios:** 68 ejercicios disponibles

---

## 🆘 **SOPORTE Y CONSULTAS**

### **📞 CONTACTO BACKEND:**
- **Estado:** ✅ 100% completado y testado
- **Documentación:** `/docs/frontend/GUIA_FRONTEND_ASIGNACIONES.md`
- **Tests:** 3 suites completas ejecutadas exitosamente

### **🔧 ENDPOINTS TESTADOS:**
- ✅ Todos los endpoints funcionando
- ✅ Performance < 500ms
- ✅ Validaciones robustas
- ✅ Manejo de errores

### **📋 RECURSOS:**
- **Postman Collection:** Disponible para testing de API
- **Swagger Docs:** Endpoints documentados
- **Database Seeder:** Datos de prueba listos

---

## 🎯 **CRITERIOS DE ÉXITO**

### **✅ FUNCIONALIDAD:**
- [ ] Admin puede crear/gestionar asignaciones
- [ ] Profesor ve sus estudiantes y puede asignar plantillas
- [ ] Sistema genera progreso automáticamente
- [ ] Calendario muestra sesiones correctamente

### **✅ UX/UI:**
- [ ] Diseño responsive y moderno
- [ ] Loading states en todas las operaciones
- [ ] Notificaciones claras de éxito/error
- [ ] Navegación intuitiva

### **✅ PERFORMANCE:**
- [ ] Carga inicial < 3 segundos
- [ ] Navegación fluida
- [ ] Optimización de re-renders
- [ ] Cache efectivo con React Query

---

## 🚀 **¡MANOS A LA OBRA!**

**El backend está 100% listo y esperándote.** 

**Tienes todos los endpoints funcionando, documentación completa y datos de prueba preparados.**

**¡Es hora de crear interfaces increíbles para este sistema de asignaciones!** 🎨

---

**📅 CREADO:** 26/09/2025 12:42 PM  
**🎯 BACKEND:** ✅ LISTO  
**⏰ ESTIMACIÓN:** 8-10 días  
**🚀 PRIORIDAD:** ALTA**
