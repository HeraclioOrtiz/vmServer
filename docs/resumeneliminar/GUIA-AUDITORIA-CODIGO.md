# 🔍 Guía de Auditoría del Código - Panel de Administración

## 🎯 **Objetivo de la Auditoría**

Verificar sistemáticamente que todas las funcionalidades del Panel de Administración estén correctamente implementadas en el backend Laravel, siguiendo el flujo de información desde las rutas hasta los servicios.

---

## 📋 **Metodología de Auditoría**

### **Flujo de Verificación por Funcionalidad:**
```
1. RUTAS → 2. MIDDLEWARE → 3. CONTROLLERS → 4. REQUESTS → 5. SERVICES → 6. MODELS → 7. MIGRATIONS
```

### **Criterios de Validación:**
- ✅ **Funcional**: El código existe y funciona
- ⚠️ **Parcial**: Implementación incompleta
- ❌ **Faltante**: No implementado
- 🔧 **Requiere Fix**: Implementado pero con errores

---

## 🗂️ **Auditoría por Carpetas del Backend**

## 📁 **1. ROUTES - Definición de Endpoints**

### **Archivo: `/routes/admin.php`**
**Verificar que existan las rutas para:**

#### **🏋️ Panel de Gimnasio (Profesores)**
```php
// Ejercicios
GET    /api/admin/gym/exercises
POST   /api/admin/gym/exercises
GET    /api/admin/gym/exercises/{id}
PUT    /api/admin/gym/exercises/{id}
DELETE /api/admin/gym/exercises/{id}

// Plantillas Diarias
GET    /api/admin/gym/daily-templates
POST   /api/admin/gym/daily-templates
GET    /api/admin/gym/daily-templates/{id}
PUT    /api/admin/gym/daily-templates/{id}
DELETE /api/admin/gym/daily-templates/{id}

// Plantillas Semanales
GET    /api/admin/gym/weekly-templates
POST   /api/admin/gym/weekly-templates
GET    /api/admin/gym/weekly-templates/{id}
PUT    /api/admin/gym/weekly-templates/{id}
DELETE /api/admin/gym/weekly-templates/{id}

// Asignaciones Semanales
GET    /api/admin/gym/weekly-assignments
POST   /api/admin/gym/weekly-assignments
GET    /api/admin/gym/weekly-assignments/{id}
PUT    /api/admin/gym/weekly-assignments/{id}
DELETE /api/admin/gym/weekly-assignments/{id}
```

#### **🏛️ Panel Villa Mitre (Administradores)**
```php
// Usuarios
GET    /api/admin/users
GET    /api/admin/users/{id}
PUT    /api/admin/users/{id}
POST   /api/admin/users/{id}/assign-professor

// Profesores
GET    /api/admin/professors
GET    /api/admin/professors/{id}/students

// Configuración
GET    /api/admin/settings
PUT    /api/admin/settings

// Reportes
GET    /api/admin/reports/system-usage
GET    /api/admin/reports/professor-activity

// Auditoría
GET    /api/admin/audit-log

// Herramientas
POST   /api/admin/tools/sync
GET    /api/admin/system/health
```

**Checklist de Verificación:**
- [ ] Todas las rutas están definidas
- [ ] Middleware correcto aplicado (`auth:sanctum`, `admin`)
- [ ] Agrupación correcta por prefijo
- [ ] Nombres de rutas consistentes

---

## 📁 **2. MIDDLEWARE - Control de Acceso**

### **Archivo: `/app/Http/Middleware/`**

#### **EnsureAdmin.php**
**Verificar:**
- [ ] Middleware existe y está registrado
- [ ] Verifica `is_admin` o `is_super_admin`
- [ ] Retorna 403 si no tiene permisos
- [ ] Permite acceso a super admins

#### **EnsureProfessor.php**
**Verificar:**
- [ ] Middleware existe y está registrado
- [ ] Verifica `is_professor`
- [ ] Permite acceso a admins también
- [ ] Manejo correcto de errores

**Código esperado:**
```php
// EnsureAdmin.php
if (!$user || (!$user->is_admin && !$user->is_super_admin)) {
    return response()->json(['message' => 'Forbidden'], 403);
}

// EnsureProfessor.php
if (!$user || (!$user->is_professor && !$user->is_admin)) {
    return response()->json(['message' => 'Forbidden'], 403);
}
```

---

## 📁 **3. CONTROLLERS - Lógica de Endpoints**

### **Gimnasio Controllers**

#### **ExerciseController.php**
**Ubicación esperada:** `/app/Http/Controllers/Admin/Gym/ExerciseController.php`
**Métodos requeridos:**
- [ ] `index()` - Lista con filtros y paginación
- [ ] `store()` - Crear ejercicio
- [ ] `show($id)` - Detalle de ejercicio
- [ ] `update($id)` - Actualizar ejercicio
- [ ] `destroy($id)` - Eliminar ejercicio

**Validaciones a verificar:**
```php
// En index()
- Filtros: muscle_group, equipment, difficulty, tags
- Paginación: page, per_page
- Búsqueda: search parameter
- Ordenamiento: sort_by, sort_direction

// En store() y update()
- Validación de campos requeridos
- Validación de enums (difficulty, muscle_group)
- Sanitización de inputs
```

#### **DailyTemplateController.php**
**Métodos requeridos:**
- [ ] `index()` - Lista con filtros
- [ ] `store()` - Crear plantilla (wizard completo)
- [ ] `show($id)` - Detalle con ejercicios
- [ ] `update($id)` - Actualizar plantilla
- [ ] `destroy($id)` - Eliminar plantilla
- [ ] `duplicate($id)` - Duplicar plantilla

#### **WeeklyTemplateController.php**
**Métodos requeridos:**
- [ ] `index()` - Lista con vista de calendario
- [ ] `store()` - Crear plantilla semanal
- [ ] `show($id)` - Detalle con días asignados
- [ ] `update($id)` - Actualizar plantilla
- [ ] `destroy($id)` - Eliminar plantilla

#### **WeeklyAssignmentController.php**
**Métodos requeridos:**
- [ ] `index()` - Lista con filtros de estudiante/fecha
- [ ] `store()` - Crear asignación (wizard)
- [ ] `show($id)` - Detalle de asignación
- [ ] `update($id)` - Actualizar asignación
- [ ] `destroy($id)` - Cancelar asignación

### **Admin Controllers**

#### **UserController.php**
**Ubicación esperada:** `/app/Http/Controllers/Admin/UserController.php`
**Métodos requeridos:**
- [ ] `index()` - Lista avanzada con filtros múltiples
- [ ] `show($id)` - Perfil completo del usuario
- [ ] `update($id)` - Editar usuario
- [ ] `assignProfessor($id)` - Asignar rol profesor

**Filtros complejos a verificar:**
```php
// En index()
- search (nombre, dni, email)
- user_type[] (local, api)
- is_professor (boolean)
- estado_socio[] (ACTIVO, etc.)
- semaforo[] (1, 2, 3)
- date_from, date_to
- has_gym_access (boolean)
```

#### **ProfessorController.php**
**Métodos requeridos:**
- [ ] `index()` - Lista con estadísticas
- [ ] `students($id)` - Estudiantes de un profesor
- [ ] `assignStudent()` - Asignar estudiante a profesor
- [ ] `removeStudent()` - Remover estudiante

#### **SettingsController.php**
**Métodos requeridos:**
- [ ] `index()` - Obtener toda la configuración
- [ ] `update()` - Actualizar configuración por secciones
- [ ] `testConnection()` - Test de API externa

#### **ReportsController.php**
**Métodos requeridos:**
- [ ] `systemUsage()` - Reporte de uso del sistema
- [ ] `professorActivity()` - Actividad de profesores
- [ ] `apiSync()` - Estado de sincronización

#### **AuditController.php**
**Métodos requeridos:**
- [ ] `index()` - Lista de logs con filtros
- [ ] `show($id)` - Detalle de log específico
- [ ] `export()` - Exportar logs filtrados

---

## 📁 **4. REQUESTS - Validación de Datos**

### **Ubicación:** `/app/Http/Requests/Admin/`

#### **Gym Requests**
- [ ] **ExerciseRequest.php** - Validación de ejercicios
- [ ] **DailyTemplateRequest.php** - Validación de plantillas diarias
- [ ] **WeeklyTemplateRequest.php** - Validación de plantillas semanales
- [ ] **WeeklyAssignmentRequest.php** - Validación de asignaciones

#### **Admin Requests**
- [ ] **UserUpdateRequest.php** - Validación de actualización de usuarios
- [ ] **ProfessorAssignmentRequest.php** - Validación de asignación de profesores
- [ ] **SettingsUpdateRequest.php** - Validación de configuración

**Reglas de validación a verificar:**
```php
// ExerciseRequest.php
'name' => 'required|string|max:255',
'muscle_group' => 'required|string|in:chest,back,legs,shoulders,arms,core',
'difficulty' => 'required|in:beginner,intermediate,advanced',
'equipment' => 'required|string',
'instructions' => 'required|string',

// UserUpdateRequest.php
'name' => 'sometimes|string|max:255',
'email' => 'sometimes|email|unique:users,email,' . $this->user,
'is_professor' => 'sometimes|boolean',
'is_admin' => 'sometimes|boolean',
```

---

## 📁 **5. SERVICES - Lógica de Negocio**

### **Ubicación:** `/app/Services/Admin/`

#### **Gym Services**
- [ ] **ExerciseService.php** - Lógica de ejercicios
- [ ] **TemplateService.php** - Lógica de plantillas
- [ ] **AssignmentService.php** - Lógica de asignaciones

#### **Admin Services**
- [ ] **UserManagementService.php** - Gestión de usuarios
- [ ] **ProfessorService.php** - Gestión de profesores
- [ ] **AuditService.php** - Sistema de auditoría
- [ ] **ReportService.php** - Generación de reportes
- [ ] **SettingsService.php** - Configuración del sistema

**Métodos esperados en cada servicio:**

```php
// ExerciseService.php
- getFilteredExercises($filters)
- createExercise($data)
- updateExercise($id, $data)
- deleteExercise($id)
- duplicateExercise($id)

// UserManagementService.php
- getFilteredUsers($filters)
- getUserProfile($id)
- updateUser($id, $data)
- assignProfessorRole($userId, $data)

// AuditService.php
- log($action, $resourceType, $resourceId, $details)
- getFilteredLogs($filters)
- exportLogs($filters, $format)
```

---

## 📁 **6. MODELS - Estructura de Datos**

### **Modelos Existentes a Extender**
- [ ] **User.php** - Agregar campos admin y métodos de permisos
- [ ] **Exercise.php** - Verificar relaciones y scopes
- [ ] **DailyTemplate.php** - Verificar estructura completa
- [ ] **WeeklyTemplate.php** - Verificar relaciones con días
- [ ] **WeeklyAssignment.php** - Verificar relaciones y estados

### **Nuevos Modelos Requeridos**
- [ ] **SystemSetting.php** - Configuración del sistema
- [ ] **AuditLog.php** - Logs de auditoría

**Campos y relaciones a verificar:**

```php
// User.php (nuevos campos)
- is_admin (boolean)
- permissions (json)
- admin_notes (text)
- account_status (enum)
- professor_since (timestamp)
- session_timeout (integer)

// SystemSetting.php
- key (string, unique)
- value (json)
- category (string)
- description (text)
- is_public (boolean)

// AuditLog.php
- user_id (foreign key)
- action (string)
- resource_type (string)
- resource_id (bigint)
- details (json)
- ip_address (ip)
- user_agent (text)
- severity (enum)
```

---

## 📁 **7. MIGRATIONS - Estructura de Base de Datos**

### **Migraciones Requeridas**
- [ ] **add_admin_fields_to_users_table.php**
- [ ] **create_system_settings_table.php**
- [ ] **create_audit_logs_table.php**
- [ ] **add_indexes_for_admin_queries.php**

**Verificar estructura de tablas:**

```sql
-- users (nuevos campos)
ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN permissions JSON NULL;
ALTER TABLE users ADD COLUMN admin_notes TEXT NULL;
ALTER TABLE users ADD COLUMN account_status ENUM('active', 'suspended', 'pending') DEFAULT 'active';
ALTER TABLE users ADD COLUMN professor_since TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN session_timeout INTEGER DEFAULT 480;

-- Índices necesarios
CREATE INDEX idx_users_is_admin ON users(is_admin);
CREATE INDEX idx_users_account_status ON users(account_status);
CREATE INDEX idx_audit_logs_user_created ON audit_logs(user_id, created_at);
```

---

## 🔧 **Checklist de Funcionalidades Críticas**

### **🏋️ Panel de Gimnasio**
- [ ] **CRUD Ejercicios**: Crear, leer, actualizar, eliminar con filtros
- [ ] **Wizard Plantillas Diarias**: 3 pasos funcionales
- [ ] **Calendario Semanal**: Drag & drop de plantillas por día
- [ ] **Wizard Asignaciones**: 4 pasos con validación de conflictos
- [ ] **Reportes Adherencia**: Métricas por alumno y período

### **🏛️ Panel Villa Mitre**
- [ ] **Gestión Usuarios**: Lista avanzada con filtros múltiples
- [ ] **Asignación Profesores**: Wizard con calificaciones y permisos
- [ ] **Configuración Sistema**: Formularios por sección
- [ ] **Auditoría Completa**: Logs con filtros y exportación
- [ ] **Herramientas Admin**: Sincronización y monitoreo

### **🔐 Seguridad y Permisos**
- [ ] **Middleware Admin**: Control de acceso granular
- [ ] **Sistema Auditoría**: Log automático de acciones críticas
- [ ] **Validación Datos**: Sanitización y validación completa
- [ ] **Manejo Errores**: Respuestas consistentes y seguras

---

## 📊 **Proceso de Auditoría Paso a Paso**

### **Fase 1: Verificación de Rutas (30 min)**
1. Revisar `/routes/admin.php`
2. Verificar que todas las rutas estén definidas
3. Comprobar middleware aplicado correctamente
4. Validar nombres y agrupaciones

### **Fase 2: Auditoría de Controllers (2 horas)**
1. Verificar existencia de todos los controllers
2. Comprobar métodos requeridos en cada controller
3. Validar lógica de filtros y paginación
4. Revisar manejo de errores

### **Fase 3: Validación de Requests (1 hora)**
1. Verificar Form Requests para cada endpoint
2. Comprobar reglas de validación
3. Validar mensajes de error personalizados

### **Fase 4: Revisión de Services (1.5 horas)**
1. Verificar lógica de negocio en services
2. Comprobar métodos de cada service
3. Validar integración con modelos

### **Fase 5: Auditoría de Models (1 hora)**
1. Verificar campos agregados a User
2. Comprobar nuevos modelos (SystemSetting, AuditLog)
3. Validar relaciones y métodos

### **Fase 6: Verificación de Migrations (30 min)**
1. Comprobar migraciones ejecutadas
2. Verificar estructura de tablas
3. Validar índices para performance

---

## 📋 **Plantilla de Reporte de Auditoría**

### **Por cada funcionalidad verificar:**
```
FUNCIONALIDAD: [Nombre]
RUTA: [Endpoint]
CONTROLLER: [Archivo y método]
REQUEST: [Validación]
SERVICE: [Lógica de negocio]
MODEL: [Estructura de datos]
ESTADO: ✅ Completo | ⚠️ Parcial | ❌ Faltante | 🔧 Requiere Fix
NOTAS: [Observaciones específicas]
```

---

**Tiempo estimado total de auditoría:** 6-8 horas  
**Prioridad:** Alta - Crítico para funcionamiento del panel  
**Próximo paso:** Ejecutar auditoría siguiendo este checklist sistemático
