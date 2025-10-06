# 👥 Resumen de Usuarios - Villa Mitre Server

## ✅ **Usuarios Creados Exitosamente**

Se han configurado **4 usuarios de prueba** con diferentes niveles de acceso para facilitar el desarrollo y testing del sistema.

### **📊 Tabla de Usuarios**

| 🆔 DNI | 👤 Nombre | 🎭 Rol | 🔑 Password | 📧 Email | 🚪 Accesos |
|---------|-----------|--------|-------------|----------|-------------|
| `11111111` | Admin User | 👨‍💼 Administrador | `admin123` | admin@villamitre.com | **TODO** |
| `22222222` | Profesor Juan Pérez | 👨‍🏫 Profesor | `profesor123` | profesor@villamitre.com | **Gimnasio** |
| `33333333` | Estudiante María García | 👨‍🎓 Estudiante | `estudiante123` | estudiante@villamitre.com | **API Móvil** |
| `12345678` | Test User | 🧪 Test | `password123` | test@example.com | **Gimnasio** |

## 🎯 **Permisos por Usuario**

### **👨‍💼 ADMINISTRADOR (DNI: 11111111)**
```json
{
  "is_admin": true,
  "is_professor": false,
  "permissions": [
    "user_management",    // ✅ Gestión de usuarios
    "gym_admin",         // ✅ Administración del gimnasio  
    "system_settings",   // ✅ Configuración del sistema
    "reports_access",    // ✅ Acceso a reportes
    "audit_logs",        // ✅ Logs de auditoría
    "super_admin"        // ✅ Permisos de super administrador
  ]
}
```

**Endpoints Disponibles:**
- ✅ `/api/admin/users` - Gestión completa de usuarios
- ✅ `/api/admin/professors` - Gestión de profesores
- ✅ `/api/admin/audit` - Logs de auditoría
- ✅ `/api/admin/gym/*` - Panel completo del gimnasio

### **👨‍🏫 PROFESOR (DNI: 22222222)**
```json
{
  "is_admin": false,
  "is_professor": true,
  "permissions": [
    "gym_admin",         // ✅ Acceso al panel del gimnasio
    "create_templates",  // ✅ Crear plantillas
    "assign_routines"    // ✅ Asignar rutinas a estudiantes
  ]
}
```

**Endpoints Disponibles:**
- ✅ `/api/admin/gym/exercises` - Gestionar ejercicios
- ✅ `/api/admin/gym/daily-templates` - Plantillas diarias
- ✅ `/api/admin/gym/weekly-templates` - Plantillas semanales
- ✅ `/api/admin/gym/weekly-assignments` - Asignaciones a estudiantes
- ❌ `/api/admin/users` - NO puede gestionar usuarios
- ❌ `/api/admin/audit` - NO puede ver logs de auditoría

### **👨‍🎓 ESTUDIANTE (DNI: 33333333)**
```json
{
  "is_admin": false,
  "is_professor": false,
  "permissions": []
}
```

**Endpoints Disponibles:**
- ✅ `/api/gym/my-week` - Ver rutina semanal propia
- ✅ `/api/gym/my-day` - Ver rutina del día
- ✅ `/api/auth/me` - Ver perfil propio
- ❌ `/api/admin/*` - NO puede acceder a paneles de administración

### **🧪 TEST USER (DNI: 12345678)**
```json
{
  "is_admin": false,
  "is_professor": true,
  "permissions": []
}
```

**Propósito:**
- ✅ Mantener compatibilidad con tests existentes
- ✅ Usado en documentación y ejemplos
- ✅ Acceso al panel del gimnasio para pruebas

## 🚀 **Comandos de Configuración**

### **Crear Usuarios con Seeder:**
```bash
# Recrear base de datos completa con usuarios
php artisan migrate:fresh --seed
```

### **Crear Usuarios Manualmente:**
```bash
# Ejecutar script de creación
php create_users.php
```

### **Verificar Usuarios Creados:**
```bash
# Listar usuarios en la base de datos
php artisan tinker
>>> User::select('dni', 'name', 'is_admin', 'is_professor')->get()
```

## 🔐 **Ejemplos de Login**

### **Login Rápido para Testing:**
```bash
# Administrador
curl -X POST localhost:8000/api/auth/login -H "Content-Type: application/json" -d '{"dni":"11111111","password":"admin123"}'

# Profesor  
curl -X POST localhost:8000/api/auth/login -H "Content-Type: application/json" -d '{"dni":"22222222","password":"profesor123"}'

# Estudiante
curl -X POST localhost:8000/api/auth/login -H "Content-Type: application/json" -d '{"dni":"33333333","password":"estudiante123"}'
```

### **Testing de Permisos:**
```bash
# Con token de administrador (debe funcionar)
curl -X GET localhost:8000/api/admin/users -H "Authorization: Bearer {admin_token}"

# Con token de profesor (debe fallar con 403)
curl -X GET localhost:8000/api/admin/users -H "Authorization: Bearer {profesor_token}"

# Con token de estudiante (debe funcionar)
curl -X GET localhost:8000/api/gym/my-week -H "Authorization: Bearer {estudiante_token}"
```

## 📋 **Casos de Uso por Usuario**

### **👨‍💼 Administrador - Casos de Uso:**
1. **Gestión de Usuarios**
   - Crear nuevos usuarios
   - Asignar roles de profesor
   - Suspender/activar cuentas
   - Ver estadísticas de usuarios

2. **Supervisión del Sistema**
   - Ver logs de auditoría
   - Monitorear actividad del gimnasio
   - Generar reportes
   - Configurar parámetros del sistema

### **👨‍🏫 Profesor - Casos de Uso:**
1. **Gestión de Ejercicios**
   - Crear nuevos ejercicios
   - Organizar por grupos musculares
   - Definir dificultades y equipamiento

2. **Creación de Rutinas**
   - Diseñar plantillas diarias
   - Crear plantillas semanales
   - Asignar rutinas a estudiantes específicos
   - Hacer seguimiento de adherencia

### **👨‍🎓 Estudiante - Casos de Uso:**
1. **Consulta de Rutinas**
   - Ver rutina de la semana actual
   - Consultar ejercicios del día
   - Revisar instrucciones de ejercicios
   - Ver progreso personal

## ⚠️ **Notas Importantes**

### **Seguridad:**
- 🔒 **Passwords simples** solo para desarrollo
- 🔒 **Cambiar en producción** todas las credenciales
- 🔒 **No usar datos reales** con estos usuarios

### **Testing:**
- ✅ **Usuarios listos** para testing inmediato
- ✅ **Permisos diferenciados** para probar autorización
- ✅ **Compatibilidad** con tests existentes mantenida

### **Desarrollo:**
- 🛠️ **Fácil recreación** con `migrate:fresh --seed`
- 🛠️ **Scripts auxiliares** para gestión manual
- 🛠️ **Documentación completa** en `docs/USER-CREDENTIALS.md`

---

**Los usuarios están configurados y listos para usar en desarrollo y testing del sistema Villa Mitre Server.** 👥✅
