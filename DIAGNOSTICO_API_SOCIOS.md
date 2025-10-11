# 🔍 DIAGNÓSTICO: PROBLEMA PROMOCIÓN DE USUARIOS

**Fecha:** 09 de Octubre 2025  
**Problema:** Usuarios no se promocionan a API al registrarse

---

## ✅ **LÓGICA DEL SISTEMA (VERIFICADA):**

### **Flujo Correcto:**
1. Usuario se registra con DNI
2. Sistema crea usuario como LOCAL
3. **Automáticamente consulta API de terceros** con el DNI
4. Si DNI existe en API → **Promueve a API**
5. Si DNI NO existe → Queda como LOCAL

### **Archivos Involucrados:**
- `app/Services/Auth/UserRegistrationService.php` (línea 56)
- `app/Services/User/UserPromotionService.php` (línea 26-50)
- `app/Services/External/SociosApi.php` (línea 31-77)
- `config/services.php` (línea 30-37)

---

## 🔑 **CONFIGURACIÓN REQUERIDA:**

### **Variables en .env (CRÍTICAS):**

```env
SOCIOS_API_BASE=https://clubvillamitre.com/api_back_socios
SOCIOS_API_LOGIN=surtek
SOCIOS_API_TOKEN=4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
SOCIOS_API_TIMEOUT=15
SOCIOS_API_VERIFY=true
```

---

## 🚨 **POSIBLES CAUSAS DEL PROBLEMA:**

### **1. Variables NO configuradas en producción** ⚠️ **MÁS PROBABLE**

El `.env` del servidor **NO tiene** las variables `SOCIOS_API_*`

**Resultado:**
- `SociosApi` no puede conectarse
- Usuario queda como LOCAL
- NO se promueve a API

---

### **2. API de terceros no responde**

La API externa está caída o bloqueando solicitudes.

**Resultado:**
- Catch exception (línea 43-49 en UserPromotionService)
- Log de error
- Usuario queda como LOCAL

---

### **3. DNI no existe en API de terceros**

El DNI que probaste realmente no existe en la base de datos del club.

**Resultado:**
- API responde pero sin datos
- Usuario queda correctamente como LOCAL

---

## 🔍 **PLAN DE DIAGNÓSTICO:**

### **PASO 1: Verificar .env en producción**

```bash
# Conectar SSH
ssh root@38.242.206.48

# Ver .env
cd /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com
cat .env | grep SOCIOS
```

**Esperado:**
```
SOCIOS_API_BASE=https://clubvillamitre.com/api_back_socios
SOCIOS_API_LOGIN=surtek
SOCIOS_API_TOKEN=4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
```

**Si NO aparecen → ESTE ES EL PROBLEMA** ✅

---

### **PASO 2: Verificar logs de Laravel**

```bash
# Ver logs en tiempo real
tail -f /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com/storage/logs/laravel.log

# O ver últimas líneas
tail -100 /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com/storage/logs/laravel.log | grep -i "socios"
```

**Buscar mensajes como:**
- `SociosApi: config incompleta`
- `Error verificando usuario en API`
- `Usuario no encontrado en API durante verificación`

---

### **PASO 3: Test manual de API**

```bash
cd /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com

# Probar conexión con API de socios
php artisan tinker

# En tinker:
$api = app(\App\Services\External\SociosApi::class);
$result = $api->getSocioPorDni('TU_DNI_DE_PRUEBA');
dd($result);
```

---

## ✅ **SOLUCIÓN:**

### **Si falta configuración en .env:**

```bash
# 1. Conectar SSH
ssh root@38.242.206.48

# 2. Editar .env
cd /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com
nano .env

# 3. Agregar al final:
SOCIOS_API_BASE=https://clubvillamitre.com/api_back_socios
SOCIOS_API_LOGIN=surtek
SOCIOS_API_TOKEN=4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
SOCIOS_API_TIMEOUT=15
SOCIOS_API_VERIFY=true
SOCIOS_API_RETRY=2

SOCIOS_IMG_BASE=https://clubvillamitre.com/images/socios
SOCIOS_IMG_TIMEOUT=10
SOCIOS_IMG_VERIFY=true

SOCIOS_REFRESH_HOURS=24
SOCIOS_AUTO_REFRESH=true

SOCIOS_CACHE_ENABLED=true
SOCIOS_CACHE_TTL=3600

# 4. Guardar: Ctrl+O, Enter, Ctrl+X

# 5. Limpiar cache de configuración
php artisan config:clear
php artisan config:cache

# 6. Probar de nuevo el registro
```

---

## 🧪 **VERIFICACIÓN POST-FIX:**

### **1. Test desde app móvil:**
- Registrar usuario con DNI conocido del club
- Verificar que se promueva a API

### **2. Verificar en base de datos:**
```sql
SELECT id, dni, name, user_type, promotion_status, promoted_at 
FROM users 
WHERE dni = 'DNI_DE_PRUEBA';
```

**Esperado:**
- `user_type` = 'api'
- `promotion_status` = 'approved'
- `promoted_at` = fecha actual

---

## 📝 **COMANDOS ÚTILES:**

```bash
# Ver configuración actual
php artisan tinker
config('services.socios');

# Ver logs de registro
tail -f storage/logs/laravel.log | grep -i "registro\|socios\|promot"

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan config:cache

# Ver usuarios recientes
php artisan tinker
User::latest()->take(5)->get(['id','dni','name','user_type','promotion_status']);
```

---

## 🎯 **RESUMEN:**

### **Problema más probable:**
❌ Variables `SOCIOS_API_*` NO configuradas en `.env` de producción

### **Solución:**
✅ Agregar variables al `.env` del servidor
✅ Limpiar cache de configuración
✅ Probar registro nuevamente

### **Tiempo estimado:** 5 minutos

---

## 📞 **SIGUIENTE PASO:**

**EJECUTAR:**
```bash
ssh root@38.242.206.48
cd /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com
cat .env | grep SOCIOS
```

Si NO sale nada → **Agregar variables al .env**

---

**¡Ejecuta los comandos de diagnóstico y pégame el resultado!** 🔍
