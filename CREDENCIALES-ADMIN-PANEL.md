# 🔐 Credenciales de Acceso - Admin Panel Villa Mitre

## 📋 **CUENTAS DE PRUEBA DISPONIBLES**

### **👨‍💼 ADMINISTRADOR COMPLETO**
```
Email: admin@villamitre.com
DNI: 11111111
Password: admin123
Rol: Super Admin
```
**Accesos:**
- ✅ Panel Villa Mitre (gestión usuarios, profesores, auditoría)
- ✅ Panel Gimnasio (ejercicios, plantillas, asignaciones)
- ✅ Configuración del sistema
- ✅ Reportes y estadísticas
- ✅ Logs de auditoría

---

### **👨‍🏫 PROFESOR DE GIMNASIO**
```
Email: profesor@villamitre.com
DNI: 22222222
Password: profesor123
Rol: Profesor
```
**Accesos:**
- ❌ Panel Villa Mitre (sin acceso)
- ✅ Panel Gimnasio (ejercicios, plantillas, asignaciones)
- ✅ Crear y gestionar rutinas
- ✅ Asignar entrenamientos a estudiantes

---

### **👨‍🎓 ESTUDIANTE**
```
Email: estudiante@villamitre.com
DNI: 33333333
Password: estudiante123
Rol: Estudiante
```
**Accesos:**
- ❌ Panel Villa Mitre (sin acceso)
- ❌ Panel Gimnasio (sin acceso)
- ✅ Solo API móvil para ver rutinas asignadas

---

### **👤 USUARIO DE PRUEBA**
```
Email: test@example.com
DNI: 12345678
Password: password123
Rol: Profesor (para testing)
```
**Accesos:**
- ❌ Panel Villa Mitre (sin permisos admin)
- ✅ Panel Gimnasio (acceso profesor)

---

## 🚀 **CÓMO USAR LAS CREDENCIALES**

### **1. Ejecutar Seeders (Primera vez)**
```bash
# Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# O solo seeders si ya tienes la BD
php artisan db:seed
```

### **2. Login en la API**
```bash
# Endpoint de login
POST /api/auth/login

# Body (JSON):
{
    "dni": "11111111",
    "password": "admin123"
}

# Respuesta esperada:
{
    "user": { ... },
    "token": "Bearer_token_aquí"
}
```

### **3. Usar Token en Requests Admin**
```bash
# Headers para requests admin:
Authorization: Bearer {token}
Content-Type: application/json

# Ejemplo - Listar usuarios:
GET /api/admin/users
Authorization: Bearer {token}
```

---

## 🧪 **TESTING CON POSTMAN/INSOMNIA**

### **Collection de Endpoints Admin:**

#### **🔐 Autenticación**
```
POST /api/auth/login
Body: {"dni": "11111111", "password": "admin123"}
```

#### **👥 Gestión Usuarios (Admin)**
```
GET    /api/admin/users              # Listar usuarios
GET    /api/admin/users/{id}         # Ver usuario
PUT    /api/admin/users/{id}         # Actualizar usuario
POST   /api/admin/users/{id}/suspend # Suspender usuario
GET    /api/admin/users/stats        # Estadísticas
```

#### **👨‍🏫 Gestión Profesores (Admin)**
```
GET    /api/admin/professors         # Listar profesores
POST   /api/admin/professors/assign  # Asignar profesor
DELETE /api/admin/professors/{id}    # Remover profesor
GET    /api/admin/professors/{id}/students # Estudiantes del profesor
```

#### **🏋️ Panel Gimnasio (Profesor/Admin)**
```
GET    /api/admin/gym/exercises      # Listar ejercicios
POST   /api/admin/gym/exercises      # Crear ejercicio
GET    /api/admin/gym/daily-templates # Plantillas diarias
POST   /api/admin/gym/weekly-assignments # Crear asignación
```

#### **📊 Auditoría (Admin)**
```
GET    /api/admin/audit              # Logs de auditoría
GET    /api/admin/audit/stats        # Estadísticas auditoría
POST   /api/admin/audit/export       # Exportar logs
```

---

## ⚠️ **NOTAS IMPORTANTES**

### **Middleware de Seguridad:**
- Todas las rutas `/api/admin/*` requieren `auth:sanctum`
- Rutas admin requieren middleware `admin` (is_admin = true)
- Rutas gym requieren middleware `professor` (is_professor = true O is_admin = true)

### **Permisos Granulares:**
- El usuario admin tiene array de permisos específicos
- Los profesores tienen permisos limitados al gimnasio
- Los estudiantes no tienen acceso a panels admin

### **Tokens de Sanctum:**
- Los tokens se generan en `/api/auth/login`
- Incluir en header: `Authorization: Bearer {token}`
- Los tokens persisten hasta logout o expiración

---

## 🔧 **COMANDOS ÚTILES**

```bash
# Ver usuarios en la base de datos
php artisan tinker
>>> App\Models\User::all(['name', 'email', 'dni', 'is_admin', 'is_professor'])

# Crear usuario admin manualmente
>>> App\Models\User::create([
    'name' => 'Nuevo Admin',
    'email' => 'nuevo@admin.com', 
    'dni' => '99999999',
    'password' => bcrypt('password'),
    'is_admin' => true,
    'account_status' => 'active'
])

# Verificar rutas admin
php artisan route:list --name=admin

# Limpiar cache
php artisan cache:clear
php artisan route:clear
```

---

## 🎯 **FLUJO DE TESTING RECOMENDADO**

1. **Login como Admin** → Probar gestión usuarios
2. **Login como Profesor** → Probar panel gimnasio  
3. **Verificar middleware** → Intentar acceso sin permisos
4. **Probar CRUD completo** → Crear, leer, actualizar, eliminar
5. **Verificar auditoría** → Ver logs de acciones realizadas

---

**¡Listo para probar el Admin Panel!** 🚀
