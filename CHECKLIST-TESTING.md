# ✅ Checklist de Testing - Admin Panel Villa Mitre

## 🚀 **PREPARACIÓN DEL ENTORNO**

### **Servicios Requeridos:**
- [ ] **Apache** iniciado y funcionando
- [ ] **MySQL** iniciado y funcionando  
- [ ] **PHP 8.1+** disponible en PATH
- [ ] **Composer** instalado

### **Configuración Laravel:**
- [ ] Archivo `.env` configurado con credenciales MySQL
- [ ] Base de datos `villa_mitre_gym` creada
- [ ] Permisos de escritura en `storage/` y `bootstrap/cache/`

---

## 🔧 **SETUP INICIAL**

### **Ejecutar Setup Automático:**
```bash
# Ejecutar script de configuración completa
php setup_admin_panel.php
```

### **O Paso a Paso Manual:**
- [ ] `php artisan migrate:fresh --seed` (crear BD y usuarios)
- [ ] `php artisan route:clear` (limpiar cache rutas)
- [ ] `php artisan config:clear` (limpiar cache config)
- [ ] Verificar que aparezcan 47+ rutas admin: `php artisan route:list --name=admin`

---

## 🧪 **TESTS AUTOMATIZADOS**

### **Ejecutar Suite Completa:**
```bash
# Tests automatizados completos
php run_admin_tests.php

# O tests específicos:
php artisan test tests/Feature/AdminPanelTest.php
```

### **Tests Individuales:**
- [ ] **Test Login:** Admin y profesor pueden loguearse
- [ ] **Test Middleware:** Estudiantes no pueden acceder a admin
- [ ] **Test CRUD:** Crear, leer, actualizar ejercicios
- [ ] **Test Validaciones:** Form requests funcionan
- [ ] **Test Auditoría:** Se crean logs de acciones

---

## 🌐 **TESTING MANUAL CON SERVIDOR**

### **1. Iniciar Servidor:**
```bash
php artisan serve
# Servidor disponible en: http://localhost:8000
```

### **2. Testing con Postman/Insomnia:**

#### **🔐 Login (OBLIGATORIO PRIMERO):**
```
POST http://localhost:8000/api/auth/login
Content-Type: application/json

Body:
{
    "dni": "11111111",
    "password": "admin123"
}

Respuesta esperada:
{
    "user": {...},
    "token": "1|xxxxx..."
}
```

#### **👥 Panel Admin (con token):**
```
GET http://localhost:8000/api/admin/users
Authorization: Bearer 1|xxxxx...

Respuesta esperada: 200 OK con lista de usuarios
```

#### **🏋️ Panel Gimnasio (con token):**
```
GET http://localhost:8000/api/admin/gym/exercises
Authorization: Bearer 1|xxxxx...

Respuesta esperada: 200 OK con lista de ejercicios
```

#### **🚫 Test Seguridad (sin token):**
```
GET http://localhost:8000/api/admin/users
# SIN Authorization header

Respuesta esperada: 401 Unauthorized
```

---

## 📋 **CHECKLIST DE FUNCIONALIDADES**

### **✅ Autenticación y Autorización:**
- [ ] Login admin funciona (admin@villamitre.com / admin123)
- [ ] Login profesor funciona (profesor@villamitre.com / profesor123)
- [ ] Token Bearer se genera correctamente
- [ ] Middleware bloquea acceso sin token (401)
- [ ] Middleware bloquea acceso sin permisos (403)

### **✅ Panel Villa Mitre (Admin):**
- [ ] `GET /api/admin/users` - Lista usuarios
- [ ] `GET /api/admin/users/{id}` - Ver usuario específico
- [ ] `PUT /api/admin/users/{id}` - Actualizar usuario
- [ ] `GET /api/admin/professors` - Lista profesores
- [ ] `POST /api/admin/professors/assign` - Asignar profesor
- [ ] `GET /api/admin/audit` - Ver logs auditoría

### **✅ Panel Gimnasio (Profesor/Admin):**
- [ ] `GET /api/admin/gym/exercises` - Lista ejercicios
- [ ] `POST /api/admin/gym/exercises` - Crear ejercicio
- [ ] `PUT /api/admin/gym/exercises/{id}` - Actualizar ejercicio
- [ ] `POST /api/admin/gym/exercises/{id}/duplicate` - Duplicar ejercicio
- [ ] `GET /api/admin/gym/daily-templates` - Lista plantillas diarias
- [ ] `GET /api/admin/gym/weekly-templates` - Lista plantillas semanales

### **✅ Validaciones y Errores:**
- [ ] Form validation funciona (422 con errores específicos)
- [ ] Datos requeridos se validan
- [ ] Formatos de datos se validan (email, DNI, etc.)
- [ ] Respuestas JSON consistentes

### **✅ Base de Datos:**
- [ ] Usuarios se crean correctamente
- [ ] SystemSettings funciona
- [ ] Migraciones aplicadas sin errores
- [ ] Relaciones entre modelos funcionan

---

## 🎯 **SCENARIOS DE TESTING AVANZADO**

### **Scenario 1: Flujo Admin Completo**
1. Login como admin
2. Ver lista de usuarios
3. Crear nuevo ejercicio
4. Ver logs de auditoría
5. Gestionar profesores

### **Scenario 2: Flujo Profesor**
1. Login como profesor
2. Intentar acceder panel admin (debe fallar 403)
3. Acceder panel gimnasio (debe funcionar)
4. Crear y gestionar ejercicios
5. Crear plantillas diarias

### **Scenario 3: Testing de Seguridad**
1. Intentar acceso sin token (401)
2. Intentar acceso con token inválido (401)
3. Login como estudiante e intentar acceso admin (403)
4. Verificar que tokens expiren correctamente

---

## 📊 **MÉTRICAS DE ÉXITO**

### **✅ Criterios de Aceptación:**
- [ ] **100% endpoints** responden correctamente
- [ ] **0 errores 500** en logs
- [ ] **Middleware** funciona en todas las rutas
- [ ] **Validaciones** bloquean datos inválidos
- [ ] **Performance** < 200ms por request
- [ ] **Seguridad** tokens y permisos funcionan

### **🎉 Testing Completado Cuando:**
- [ ] Todos los checkboxes marcados ✅
- [ ] Tests automatizados pasan 100%
- [ ] Testing manual exitoso
- [ ] No hay errores en logs Laravel
- [ ] Frontend puede conectarse sin problemas

---

## 🚨 **TROUBLESHOOTING**

### **Error: "Target machine actively refused"**
- Verificar que MySQL esté iniciado
- Verificar credenciales en `.env`
- Probar conexión: `php artisan tinker` → `DB::connection()->getPdo()`

### **Error: "Route not found"**
- Ejecutar: `php artisan route:clear`
- Verificar que `bootstrap/app.php` incluya `routes/admin.php`

### **Error: "Class not found"**
- Ejecutar: `composer dump-autoload`
- Verificar namespaces en controllers

### **Error: "Unauthenticated"**
- Verificar que el token Bearer esté en headers
- Verificar formato: `Authorization: Bearer {token}`

---

**¡Listo para testing completo del Admin Panel!** 🚀
