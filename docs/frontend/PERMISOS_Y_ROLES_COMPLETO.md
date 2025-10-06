# 🔐 **PERMISOS Y ROLES - DOCUMENTACIÓN COMPLETA PARA FRONTEND**

## 📋 **INFORMACIÓN CRÍTICA**
- **Fecha:** 26 de Septiembre, 2025
- **Versión:** 1.0 - Documentación Oficial
- **Estado:** ✅ **VERIFICADO EN CÓDIGO FUENTE**

---

## 🎯 **SISTEMA DE ROLES IMPLEMENTADO**

### **👤 ESTRUCTURA DE USUARIO:**
```typescript
interface User {
  id: number;
  name: string;
  email: string;
  dni: string;
  
  // CAMPOS CRÍTICOS DE ROLES
  is_admin: boolean;           // ⭐ CAMPO PRINCIPAL ADMIN
  is_professor: boolean;       // ⭐ CAMPO PRINCIPAL PROFESOR
  permissions: string[] | null; // ⭐ PERMISOS GRANULARES
  account_status: string;      // ⭐ ESTADO DE CUENTA
  
  // Campos adicionales
  avatar_path?: string;
  professor_since?: string;
}
```

---

## 🏛️ **JERARQUÍA DE ROLES**

### **🔴 NIVEL 1: SUPER ADMIN**
```typescript
// Identificación
user.is_admin === true && user.permissions.includes('super_admin')

// Acceso TOTAL a todo el sistema
```

### **🟠 NIVEL 2: ADMIN**
```typescript
// Identificación
user.is_admin === true

// Acceso completo a administración
```

### **🟡 NIVEL 3: PROFESOR**
```typescript
// Identificación
user.is_professor === true

// Acceso a gestión de gimnasio y estudiantes
```

### **🟢 NIVEL 4: ESTUDIANTE**
```typescript
// Identificación
user.is_professor === false && user.is_admin === false

// Acceso solo a sus entrenamientos
```

---

## 🔐 **VALIDACIONES DE MIDDLEWARE**

### **🛡️ MIDDLEWARE `admin`:**
```typescript
// Archivo: app/Http/Middleware/EnsureAdmin.php
// Validaciones que realiza:

1. ✅ Usuario autenticado
2. ✅ user.isAdmin() === true (is_admin === true)
3. ✅ Permiso específico (si se requiere)
4. ✅ account_status === 'active'

// Respuestas de error:
401: "Unauthenticated" (sin token)
403: "Forbidden. Admin role required." (no es admin)
403: "Forbidden. Permission 'X' required." (sin permiso específico)
403: "Account suspended or inactive." (cuenta inactiva)
```

### **🛡️ MIDDLEWARE `professor`:**
```typescript
// Archivo: app/Http/Middleware/EnsureProfessor.php
// Validaciones que realiza:

1. ✅ Usuario autenticado
2. ✅ user.is_professor === true || user.is_admin === true || user.is_super_admin === true

// Respuesta de error:
403: "Forbidden: professor role required."

// NOTA IMPORTANTE: Los admins también pueden acceder a rutas de profesor
```

---

## 📊 **PERMISOS GRANULARES DISPONIBLES**

### **🔧 PERMISOS DEL SISTEMA:**
```typescript
// Basado en el modelo User.php - método hasPermission()

interface AvailablePermissions {
  // Super administración
  'super_admin': 'Acceso total al sistema';
  
  // Gestión de usuarios
  'user_management': 'Gestionar usuarios del sistema';
  
  // Administración de gimnasio
  'gym_admin': 'Administrar ejercicios y plantillas';
  
  // Reportes y estadísticas
  'reports_access': 'Ver reportes del sistema';
  
  // Logs de auditoría
  'audit_logs': 'Ver logs de auditoría';
}
```

### **🔍 MÉTODOS DE VERIFICACIÓN:**
```typescript
// Métodos disponibles en el modelo User:

user.isAdmin(): boolean                    // Es administrador
user.isSuperAdmin(): boolean              // Es super administrador
user.hasPermission(permission: string): boolean  // Tiene permiso específico
user.canManageUsers(): boolean            // Puede gestionar usuarios
user.canManageGym(): boolean              // Puede gestionar gimnasio
user.canViewReports(): boolean            // Puede ver reportes
user.canViewAuditLogs(): boolean          // Puede ver logs
```

---

## 🚪 **ACCESO POR ENDPOINTS**

### **👑 ENDPOINTS DE ADMINISTRADOR (`/admin/*`):**
```typescript
// Middleware: ['auth:sanctum', 'admin']
// Requiere: user.is_admin === true

// ASIGNACIONES (Sistema Jerárquico)
GET    /admin/assignments              // ✅ Listar asignaciones
POST   /admin/assignments              // ✅ Crear asignación
PUT    /admin/assignments/{id}         // ✅ Actualizar asignación
DELETE /admin/assignments/{id}         // ✅ Eliminar asignación
GET    /admin/assignments-stats        // ✅ Estadísticas generales
GET    /admin/students/unassigned      // ✅ Estudiantes sin asignar
GET    /admin/professors/{id}/students // ✅ Estudiantes de un profesor

// ACCIONES DE ASIGNACIONES
POST   /admin/assignments/{id}/pause      // ✅ Pausar asignación
POST   /admin/assignments/{id}/reactivate // ✅ Reactivar asignación
POST   /admin/assignments/{id}/complete   // ✅ Completar asignación

// GESTIÓN DE USUARIOS
GET    /admin/users                    // ✅ Listar usuarios
POST   /admin/users                    // ✅ Crear usuario
PUT    /admin/users/{id}               // ✅ Actualizar usuario
DELETE /admin/users/{id}               // ✅ Eliminar usuario
GET    /admin/users/stats              // ✅ Estadísticas de usuarios

// GESTIÓN DE PROFESORES
GET    /admin/professors               // ✅ Listar profesores
POST   /admin/professors/{id}/assign   // ✅ Asignar rol profesor
DELETE /admin/professors/{id}/remove   // ✅ Remover rol profesor

// AUDITORÍA Y LOGS
GET    /admin/audit                    // ✅ Ver logs de auditoría
GET    /admin/audit/stats              // ✅ Estadísticas de auditoría

// CONFIGURACIÓN
GET    /admin/settings                 // ✅ Ver configuración
POST   /admin/settings                 // ✅ Crear configuración
PUT    /admin/settings/{key}           // ✅ Actualizar configuración
```

### **👨‍🏫 ENDPOINTS DE PROFESOR (`/professor/*`):**
```typescript
// Middleware: ['auth:sanctum', 'professor']
// Requiere: user.is_professor === true || user.is_admin === true

// MIS ESTUDIANTES
GET    /professor/my-students          // ✅ Ver mis estudiantes asignados
GET    /professor/my-stats             // ✅ Mis estadísticas

// ASIGNACIÓN DE PLANTILLAS
POST   /professor/assign-template      // ✅ Asignar plantilla a estudiante
GET    /professor/assignments/{id}     // ✅ Ver asignación específica
PUT    /professor/assignments/{id}     // ✅ Actualizar asignación

// PROGRESO Y FEEDBACK
GET    /professor/students/{id}/progress    // ✅ Ver progreso de estudiante
POST   /professor/progress/{id}/feedback    // ✅ Agregar feedback

// CALENDARIO Y SESIONES
GET    /professor/today-sessions       // ✅ Sesiones de hoy
GET    /professor/weekly-calendar      // ✅ Calendario semanal
```

### **🏋️ ENDPOINTS DE GIMNASIO (`/admin/gym/*`):**
```typescript
// Middleware: ['auth:sanctum', 'professor']
// Requiere: user.is_professor === true || user.is_admin === true
// NOTA: Los admins también pueden acceder

// EJERCICIOS
GET    /admin/gym/exercises            // ✅ Listar ejercicios
POST   /admin/gym/exercises            // ✅ Crear ejercicio
PUT    /admin/gym/exercises/{id}       // ✅ Actualizar ejercicio
DELETE /admin/gym/exercises/{id}       // ✅ Eliminar ejercicio
POST   /admin/gym/exercises/{id}/duplicate // ✅ Duplicar ejercicio

// PLANTILLAS DIARIAS
GET    /admin/gym/daily-templates      // ✅ Listar plantillas
POST   /admin/gym/daily-templates      // ✅ Crear plantilla
PUT    /admin/gym/daily-templates/{id} // ✅ Actualizar plantilla
DELETE /admin/gym/daily-templates/{id} // ✅ Eliminar plantilla
POST   /admin/gym/daily-templates/{id}/duplicate // ✅ Duplicar plantilla

// PLANTILLAS SEMANALES
GET    /admin/gym/weekly-templates     // ✅ Listar plantillas semanales
POST   /admin/gym/weekly-templates     // ✅ Crear plantilla semanal
PUT    /admin/gym/weekly-templates/{id} // ✅ Actualizar plantilla
DELETE /admin/gym/weekly-templates/{id} // ✅ Eliminar plantilla

// ASIGNACIONES LEGACY
GET    /admin/gym/weekly-assignments   // ✅ Sistema legacy (coexiste)
POST   /admin/gym/weekly-assignments   // ✅ Crear asignación legacy
```

### **🎓 ENDPOINTS DE ESTUDIANTE (`/gym/*`):**
```typescript
// Middleware: ['auth:sanctum']
// Requiere: Solo autenticación (cualquier usuario)

GET    /gym/my-week                    // ✅ Mi semana de entrenamientos
GET    /gym/my-day                     // ✅ Mi día de entrenamiento
```

---

## 🔒 **VALIDACIONES DE SEGURIDAD**

### **🛡️ VALIDACIONES AUTOMÁTICAS:**
```typescript
// En TODOS los endpoints protegidos:

1. ✅ Token válido (Authorization: Bearer {token})
2. ✅ Usuario existe y está activo
3. ✅ Rol requerido según endpoint
4. ✅ Permisos específicos (si aplica)
5. ✅ Estado de cuenta activo (account_status === 'active')
```

### **⚠️ CASOS ESPECIALES:**
```typescript
// ADMINS pueden acceder a rutas de PROFESOR
if (user.is_admin === true) {
  // ✅ Puede acceder a /professor/*
  // ✅ Puede acceder a /admin/gym/*
  // ✅ Puede acceder a /admin/*
}

// PROFESORES pueden acceder a gimnasio
if (user.is_professor === true || user.is_admin === true) {
  // ✅ Puede acceder a /professor/*
  // ✅ Puede acceder a /admin/gym/*
}

// ESTUDIANTES solo a sus datos
if (user.is_professor === false && user.is_admin === false) {
  // ✅ Puede acceder a /gym/* (sus entrenamientos)
  // ❌ NO puede acceder a /professor/*
  // ❌ NO puede acceder a /admin/*
}
```

---

## 🎨 **IMPLEMENTACIÓN EN FRONTEND**

### **🔐 HOOK DE AUTENTICACIÓN:**
```typescript
// hooks/useAuth.ts
export const useAuth = () => {
  const { data: user } = useQuery(['auth-user'], getProfile);
  
  return {
    user,
    isAdmin: user?.is_admin === true,
    isProfessor: user?.is_professor === true,
    isStudent: user?.is_professor === false && user?.is_admin === false,
    isSuperAdmin: user?.is_admin === true && user?.permissions?.includes('super_admin'),
    
    // Métodos de verificación
    canManageUsers: user?.is_admin === true || user?.permissions?.includes('user_management'),
    canManageGym: user?.is_professor === true || user?.permissions?.includes('gym_admin'),
    canViewReports: user?.is_admin === true || user?.permissions?.includes('reports_access'),
    canViewAuditLogs: user?.is_admin === true || user?.permissions?.includes('audit_logs'),
  };
};
```

### **🛡️ COMPONENTE DE PROTECCIÓN:**
```typescript
// components/ProtectedRoute.tsx
interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRole?: 'admin' | 'professor' | 'student';
  requiredPermission?: string;
}

export const ProtectedRoute: React.FC<ProtectedRouteProps> = ({
  children,
  requiredRole,
  requiredPermission
}) => {
  const { user, isLoading } = useAuth();
  
  if (isLoading) return <LoadingSpinner />;
  
  if (!user) {
    return <Navigate to="/login" />;
  }
  
  // Verificar rol requerido
  if (requiredRole === 'admin' && !user.is_admin) {
    return <Navigate to="/unauthorized" />;
  }
  
  if (requiredRole === 'professor' && !user.is_professor && !user.is_admin) {
    return <Navigate to="/unauthorized" />;
  }
  
  // Verificar permiso específico
  if (requiredPermission && !user.permissions?.includes(requiredPermission)) {
    return <Navigate to="/unauthorized" />;
  }
  
  // Verificar estado de cuenta
  if (user.account_status !== 'active') {
    return <Navigate to="/account-suspended" />;
  }
  
  return <>{children}</>;
};
```

### **🎯 NAVEGACIÓN CONDICIONAL:**
```typescript
// components/Navigation.tsx
export const Navigation: React.FC = () => {
  const { user, isAdmin, isProfessor } = useAuth();
  
  return (
    <nav>
      {/* Siempre visible para usuarios autenticados */}
      <NavLink to="/dashboard">Dashboard</NavLink>
      
      {/* Solo para administradores */}
      {isAdmin && (
        <>
          <NavLink to="/admin/assignments">Asignaciones</NavLink>
          <NavLink to="/admin/users">Usuarios</NavLink>
          <NavLink to="/admin/professors">Profesores</NavLink>
          <NavLink to="/admin/audit">Auditoría</NavLink>
        </>
      )}
      
      {/* Para profesores y admins */}
      {(isProfessor || isAdmin) && (
        <>
          <NavLink to="/professor/my-students">Mis Estudiantes</NavLink>
          <NavLink to="/professor/calendar">Calendario</NavLink>
          <NavLink to="/admin/gym/exercises">Ejercicios</NavLink>
          <NavLink to="/admin/gym/templates">Plantillas</NavLink>
        </>
      )}
      
      {/* Solo para estudiantes */}
      {!isProfessor && !isAdmin && (
        <>
          <NavLink to="/gym/my-week">Mi Semana</NavLink>
          <NavLink to="/gym/my-day">Hoy</NavLink>
        </>
      )}
    </nav>
  );
};
```

---

## 📊 **DATOS DE PRUEBA**

### **👤 USUARIOS DE TESTING:**
```typescript
// ADMIN
{
  dni: "11111111",
  password: "admin123",
  // Resultado esperado:
  is_admin: true,
  is_professor: false,
  permissions: ["user_management", "gym_admin", "reports_access"]
}

// PROFESOR
{
  dni: "22222222", 
  password: "profesor123",
  // Resultado esperado:
  is_admin: false,
  is_professor: true,
  permissions: null
}

// ESTUDIANTE (cualquier otro usuario)
{
  // Resultado esperado:
  is_admin: false,
  is_professor: false,
  permissions: null
}
```

---

## ⚠️ **CONSIDERACIONES IMPORTANTES**

### **🔴 CRÍTICO:**
1. **Los ADMINS pueden acceder a rutas de PROFESOR** (middleware permite ambos)
2. **El campo `account_status` debe ser 'active'** para acceder
3. **Los permisos son opcionales** - no todos los admins los tienen
4. **El sistema legacy coexiste** con el nuevo sistema

### **🟡 IMPORTANTE:**
1. **Validar siempre en frontend Y backend**
2. **Manejar estados de carga** durante verificación
3. **Mostrar mensajes claros** de permisos insuficientes
4. **Implementar logout automático** si token expira

### **🟢 RECOMENDADO:**
1. **Cachear información de usuario** con React Query
2. **Actualizar permisos** después de cambios de rol
3. **Implementar refresh automático** de tokens
4. **Logging de accesos** para auditoría

---

**📋 DOCUMENTO VERIFICADO:** 26/09/2025 15:05 PM  
**🔍 FUENTE:** Código fuente del backend  
**✅ ESTADO:** Información 100% precisa y actualizada  
**📞 CONTACTO:** Equipo Backend para consultas**
