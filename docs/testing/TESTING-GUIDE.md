# 🧪 GUÍA COMPLETA DE TESTING - VILLA MITRE SERVER

## 📋 Scripts de Testing Disponibles

### **🔌 API Integration Testing**
```powershell
# Test conexión API terceros
C:\xampp\php\php.exe test_api_integration.php
```
**Verifica:**
- Conexión con API de Club Villa Mitre
- Respuesta de datos de socio
- Mapeo de campos nuevos
- Descarga de imágenes

### **👤 Registration Flow Testing**
```powershell
# Test flujo completo de registro
C:\xampp\php\php.exe test_registration_flow.php
```
**Verifica:**
- Registro local de usuario
- Promoción automática a API
- Sincronización de datos
- Descarga síncrona de imagen

### **🔐 Login Flow Testing**
```powershell
# Test flujo completo de login
C:\xampp\php\php.exe test_login_flow.php
```
**Verifica:**
- Autenticación de usuario
- Refresh de datos desde API
- Cache de usuario
- Imagen en respuesta

### **🖼️ Database Images Check**
```powershell
# Verificar imágenes almacenadas
C:\xampp\php\php.exe check_database_images.php
```
**Verifica:**
- Usuarios con avatar_path
- Archivos en storage/
- URLs de imágenes válidas

## 🌐 Testing Manual con Postman

### **Configuración Postman**
Ver: `docs/testing/POSTMAN-COLLECTION.json`

### **Endpoints Principales**
```
POST /api/register
POST /api/login
GET /api/user
POST /api/refresh
```

### **Testing de Imágenes**
```
# API terceros - Datos usuario
POST https://clubvillamitre.com/api_back_socios/get_socio
Headers: Authorization: 4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
Body: dni=59964604

# API terceros - Imagen directa
GET https://clubvillamitre.com/images/socios/43675.jpg
```

## 🔍 Testing de Funcionalidades Específicas

### **1. Autenticación Unificada**
```powershell
# Test con usuario local
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"dni":"12345678","password":"password"}'

# Test con usuario API
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"dni":"59964604","password":"password"}'
```

### **2. Descarga Síncrona de Imágenes**
```powershell
# Verificar que foto_url está en respuesta
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"dni":"59964604","name":"Test","email":"test@test.com","password":"password"}'
```

### **3. Cache y Circuit Breaker**
```powershell
# Test cache hit
C:\xampp\php\php.exe artisan tinker
>>> $user = App\Models\User::where('dni', '59964604')->first();
>>> Cache::get("user_59964604");
```

## 📊 Métricas de Testing

### **Tiempos Esperados**
- Registro con API: < 5 segundos
- Login con refresh: < 3 segundos
- Descarga imagen: < 3 segundos

### **Respuestas Esperadas**
```json
{
  "success": true,
  "data": {
    "user": {
      "dni": "59964604",
      "name": "MUNAFO, JUSTINA",
      "foto_url": "http://localhost:8000/storage/avatars/43675.jpg",
      "user_type": "api",
      "saldo": 0,
      "semaforo": 1
    },
    "token": "..."
  }
}
```

## 🚨 Casos de Error a Testear

### **1. API Terceros No Disponible**
```powershell
# Simular falla de API
# Modificar temporalmente SOCIOS_API_BASE en .env
```

### **2. Usuario No Existe en API**
```powershell
# Test con DNI inexistente
curl -X POST http://localhost:8000/api/register \
  -d '{"dni":"00000000","name":"Test","email":"test@test.com","password":"password"}'
```

### **3. Imagen No Disponible**
```powershell
# Verificar comportamiento con imagen faltante
# foto_url debe ser null, no error
```

## 📝 Checklist de Testing Pre-Deploy

- [ ] ✅ API integration test pasa
- [ ] ✅ Registration flow completo
- [ ] ✅ Login flow completo
- [ ] ✅ Imágenes se descargan correctamente
- [ ] ✅ Cache funciona
- [ ] ✅ Circuit breaker activo
- [ ] ✅ Logs sin errores críticos
- [ ] ✅ Base de datos migrada
- [ ] ✅ Storage con permisos correctos
- [ ] ✅ Variables .env configuradas

## 🔧 Comandos de Limpieza

```powershell
# Limpiar cache
C:\xampp\php\php.exe artisan cache:clear

# Limpiar logs
C:\xampp\php\php.exe artisan log:clear

# Reset base de datos (solo testing)
C:\xampp\php\php.exe artisan migrate:fresh

# Limpiar storage avatars
rm -rf storage/app/public/avatars/*
```

---
**Próximo paso:** [Deployment Guide](../deployment/APACHE-DEPLOY.md)
