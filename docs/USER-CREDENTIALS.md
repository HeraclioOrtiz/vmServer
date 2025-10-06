# 🔐 Credenciales de Usuario - Villa Mitre Server

## 👥 **Usuarios de Prueba Configurados**

El sistema incluye usuarios predefinidos para testing y desarrollo con diferentes niveles de acceso.

### **📊 Resumen de Usuarios**

| DNI | Nombre | Rol | Accesos | Password |
|-----|--------|-----|---------|----------|
| `11111111` | Admin User | 👨‍💼 Administrador | TODO | `admin123` |
| `22222222` | Profesor Juan Pérez | 👨‍🏫 Profesor | Gimnasio | `profesor123` |
| `33333333` | Estudiante María García | 👨‍🎓 Estudiante | API Móvil | `estudiante123` |
| `12345678` | Test User | 🧪 Test | Gimnasio | `password123` |

## 🔑 **Credenciales Detalladas**

### **👨‍💼 ADMINISTRADOR - Acceso Completo**
```json
{
  "dni": "11111111",
  "password": "admin123",
  "email": "admin@villamitre.com"
}
```

**Permisos:**
- ✅ **Gestión de Usuarios** (`/api/admin/users`)
- ✅ **Gestión de Profesores** (`/api/admin/professors`)
- ✅ **Logs de Auditoría** (`/api/admin/audit`)
- ✅ **Panel del Gimnasio** (`/api/admin/gym/*`)
- ✅ **Configuración del Sistema**
- ✅ **Reportes y Estadísticas**

**Endpoints Disponibles:**
```bash
GET  /api/admin/users              # Gestionar usuarios
POST /api/admin/users              # Crear usuarios
GET  /api/admin/professors         # Gestionar profesores
GET  /api/admin/audit              # Ver logs de auditoría
GET  /api/admin/gym/exercises      # Gestionar ejercicios
POST /api/admin/gym/weekly-assignments  # Crear asignaciones
```

### **👨‍🏫 PROFESOR - Solo Panel del Gimnasio**
```json
{
  "dni": "22222222",
  "password": "profesor123",
  "email": "profesor@villamitre.com"
}
```

**Permisos:**
- ✅ **Panel del Gimnasio** (`/api/admin/gym/*`)
- ✅ **Crear Plantillas** de ejercicios
- ✅ **Asignar Rutinas** a estudiantes
- ✅ **Ver Estadísticas** de estudiantes propios
- ❌ **NO** puede gestionar usuarios
- ❌ **NO** puede ver logs de auditoría

**Endpoints Disponibles:**
```bash
GET  /api/admin/gym/exercises           # Ver/crear ejercicios
POST /api/admin/gym/exercises           # Crear ejercicios
GET  /api/admin/gym/daily-templates     # Gestionar plantillas diarias
POST /api/admin/gym/daily-templates     # Crear plantillas diarias
GET  /api/admin/gym/weekly-templates    # Gestionar plantillas semanales
POST /api/admin/gym/weekly-assignments  # Crear asignaciones a estudiantes
```

### **👨‍🎓 ESTUDIANTE - Solo API Móvil**
```json
{
  "dni": "33333333",
  "password": "estudiante123",
  "email": "estudiante@villamitre.com"
}
```

**Permisos:**
- ✅ **API Móvil** (`/api/gym/*`)
- ✅ **Ver Rutinas Propias** semanales y diarias
- ✅ **Consultar Ejercicios** asignados
- ❌ **NO** puede acceder a paneles de administración
- ❌ **NO** puede crear rutinas o ejercicios

**Endpoints Disponibles:**
```bash
GET /api/gym/my-week               # Ver rutina semanal propia
GET /api/gym/my-day                # Ver rutina del día
GET /api/auth/me                   # Ver perfil propio
```

### **🧪 TEST USER - Compatibilidad**
```json
{
  "dni": "12345678",
  "password": "password123",
  "email": "test@example.com"
}
```

**Permisos:**
- ✅ **Panel del Gimnasio** (para tests de compatibilidad)
- ✅ Usado en tests automatizados
- ✅ Mantiene compatibilidad con tests existentes

## 🚀 **Ejemplos de Login**

### **Login como Administrador**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"dni":"11111111","password":"admin123"}'
```

**Response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "dni": "11111111",
    "name": "Admin User",
    "is_admin": true,
    "is_professor": false,
    "permissions": [
      "user_management",
      "gym_admin",
      "system_settings",
      "reports_access",
      "audit_logs",
      "super_admin"
    ]
  }
}
```

### **Login como Profesor**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"dni":"22222222","password":"profesor123"}'
```

**Response:**
```json
{
  "token": "2|def456...",
  "user": {
    "id": 2,
    "dni": "22222222",
    "name": "Profesor Juan Pérez",
    "is_admin": false,
    "is_professor": true,
    "permissions": [
      "gym_admin",
      "create_templates",
      "assign_routines"
    ]
  }
}
```

### **Login como Estudiante**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"dni":"33333333","password":"estudiante123"}'
```

**Response:**
```json
{
  "token": "3|ghi789...",
  "user": {
    "id": 3,
    "dni": "33333333",
    "name": "Estudiante María García",
    "is_admin": false,
    "is_professor": false,
    "permissions": []
  }
}
```

## 🔒 **Testing de Permisos**

### **Verificar Acceso de Administrador**
```bash
# Debe funcionar ✅
curl -X GET http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer {admin_token}"

# Debe funcionar ✅
curl -X GET http://localhost:8000/api/admin/gym/exercises \
  -H "Authorization: Bearer {admin_token}"
```

### **Verificar Acceso de Profesor**
```bash
# Debe funcionar ✅
curl -X GET http://localhost:8000/api/admin/gym/exercises \
  -H "Authorization: Bearer {profesor_token}"

# Debe fallar ❌ (403 Forbidden)
curl -X GET http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer {profesor_token}"
```

### **Verificar Acceso de Estudiante**
```bash
# Debe funcionar ✅
curl -X GET http://localhost:8000/api/gym/my-week \
  -H "Authorization: Bearer {estudiante_token}"

# Debe fallar ❌ (403 Forbidden)
curl -X GET http://localhost:8000/api/admin/gym/exercises \
  -H "Authorization: Bearer {estudiante_token}"
```

## 🛠️ **Creación de Usuarios**

### **Usando el Seeder**
```bash
# Recrear base de datos con usuarios predefinidos
php artisan migrate:fresh --seed
```

### **Usando el Script Manual**
```bash
# Crear usuarios individualmente
php create_users.php
```

### **Crear Usuario Manualmente**
```bash
# Usando Tinker
php artisan tinker

# Crear administrador
User::create([
    'name' => 'Nuevo Admin',
    'dni' => '99999999',
    'password' => Hash::make('password'),
    'is_admin' => true,
    'permissions' => ['super_admin']
]);
```

## ⚠️ **Notas de Seguridad**

### **Passwords de Desarrollo**
- ⚠️ **Solo para desarrollo/testing**
- ⚠️ **Cambiar en producción**
- ⚠️ **No usar en datos reales**

### **Permisos Granulares**
- `user_management` - Gestión de usuarios
- `gym_admin` - Panel del gimnasio
- `system_settings` - Configuración
- `reports_access` - Reportes
- `audit_logs` - Logs de auditoría
- `super_admin` - Permisos completos

### **Middleware de Protección**
- `auth:sanctum` - Autenticación requerida
- `admin` - Solo administradores
- `professor` - Solo profesores

---

**Estos usuarios están configurados para facilitar el desarrollo y testing del sistema Villa Mitre Server.** 🔐
