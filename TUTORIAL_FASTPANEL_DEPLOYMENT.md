# 🚀 TUTORIAL COMPLETO: DEPLOYMENT EN FASTPANEL

**Proyecto:** Villa Mitre API Backend  
**Servidor:** appvillamitre.surtekbb.com  
**Panel:** FastPanel en Ubuntu 24.02  
**Fecha:** 2025-10-07

---

## 📋 **INFORMACIÓN DE ACCESO**

```
FastPanel:
URL: https://38.242.206.48:8888/
Usuario: fastuser
Password: jVe6QUtx2qO784Py

Dominio:
URL: appvillamitre.surtekbb.com
IP Servidor: 38.242.206.48

SSH:
ssh fastuser@38.242.206.48
Password: [mismo que FastPanel]
```

---

## 🎯 **PASO 1: ACCEDER A FASTPANEL**

### **1.1 Abrir FastPanel**

1. **Abrir navegador** (Chrome o Firefox recomendado)

2. **Ir a la URL del panel:**
   ```
   https://38.242.206.48:8888/
   ```

3. **Advertencia de seguridad:**
   - Verás "Su conexión no es privada" o "Your connection is not private"
   - Esto es NORMAL (estás usando IP en vez de dominio)
   - Click en **"Avanzado"** o **"Advanced"**
   - Click en **"Continuar a 38.242.206.48"** o **"Proceed to..."**

4. **Login:**
   ```
   Usuario: fastuser
   Password: jVe6QUtx2qO784Py
   ```
   - Click en **"Login"** o **"Iniciar sesión"**

### **1.2 Navegar por el Dashboard**

Una vez dentro verás:

```
┌─────────────────────────────────────────────┐
│  FASTPANEL DASHBOARD                        │
├─────────────────────────────────────────────┤
│  📊 Recursos del Servidor                   │
│  - CPU: % uso                               │
│  - RAM: GB usado / GB total                 │
│  - Disco: GB usado / GB total               │
├─────────────────────────────────────────────┤
│  🌐 Sitios Web (Websites)                   │
│  🗄️  Bases de Datos (Databases)             │
│  🐘 PHP                                      │
│  📦 Aplicaciones (Applications)             │
│  👤 Usuarios (Users)                        │
│  🔒 Firewall                                │
│  ⚙️  Configuración (Settings)               │
└─────────────────────────────────────────────┘
```

---

## 🗄️ **PASO 2: CREAR BASE DE DATOS**

### **2.1 Ir a sección de Bases de Datos**

1. En el menú lateral izquierdo, click en **"Databases"** o **"Bases de Datos"**
2. Verás una lista de bases de datos (puede estar vacía)

### **2.2 Crear nueva base de datos**

1. Click en botón **"+ Add Database"** o **"+ Añadir Base de Datos"**
   - Ubicación: Esquina superior derecha

2. **Formulario de creación:**

   ```
   ┌─────────────────────────────────────────────┐
   │  CREATE DATABASE                            │
   ├─────────────────────────────────────────────┤
   │  Database Name: ⬜ villamitre_prod          │
   │  Database User: ⬜ villamitre_user          │
   │  Password:      ⬜ [generar automático]     │
   │  Charset:       ⬜ utf8mb4                  │
   │  Collation:     ⬜ utf8mb4_unicode_ci       │
   ├─────────────────────────────────────────────┤
   │  [Cancel]  [Create Database]                │
   └─────────────────────────────────────────────┘
   ```

3. **Configuración recomendada:**
   - **Database Name:** `villamitre_prod`
   - **Database User:** `villamitre_user`
   - **Password:** Click en icono de "generar" 🔄 o crear uno seguro
   - **Charset:** `utf8mb4` (default)
   - **Collation:** `utf8mb4_unicode_ci` (default)

4. **Click en "Create Database"**

5. **⚠️ IMPORTANTE - GUARDAR CREDENCIALES:**
   ```
   DB_DATABASE=villamitre_prod
   DB_USERNAME=villamitre_user
   DB_PASSWORD=[el password generado - COPIARLO]
   ```
   
   **Guarda este password en un lugar seguro** (lo necesitarás para el .env)

### **2.3 Verificar que se creó**

- Deberías ver la nueva BD en la lista
- Estado: **Active** o **Activo**

---

## 🌐 **PASO 3: VERIFICAR/CREAR SITIO WEB**

### **3.1 Ir a sección de Sitios**

1. En el menú lateral, click en **"Sites"** o **"Sitios Web"**
2. Buscar si ya existe: **appvillamitre.surtekbb.com**

### **3.2 Si el sitio YA EXISTE:**

1. **Click en el nombre del sitio** (appvillamitre.surtekbb.com)
2. **Verificar configuración:**
   - **Status:** Debe estar **Active**
   - **PHP Version:** Debe ser **8.2** o superior
   - **Document Root:** Debe terminar en `/public`

3. **Editar si es necesario:**
   - Click en ⚙️ **"Settings"** o **"Configuración"**
   - Buscar **"Site Directory"** o **"Directorio del sitio"**
   - **Debe ser:** `/var/www/fastuser/data/www/appvillamitre.surtekbb.com/public`
   - **El `/public` al final es CRÍTICO para Laravel**

### **3.3 Si el sitio NO EXISTE:**

1. Click en **"+ Add Site"** o **"+ Añadir Sitio"**

2. **Formulario de creación:**
   ```
   Domain: appvillamitre.surtekbb.com
   PHP Version: 8.2 (seleccionar la más alta)
   Document Root: /public  (agregar esto al final)
   ```

3. Click en **"Create"**

### **3.4 Configurar SSL (HTTPS)**

1. Dentro del sitio, buscar sección **"SSL Certificate"**
2. Click en **"Install Let's Encrypt"** o **"Instalar certificado gratuito"**
3. Esperar 1-2 minutos
4. Verificar que aparezca: **"Certificate installed"**

---

## 🐘 **PASO 4: VERIFICAR PHP Y EXTENSIONES**

### **4.1 Ir a sección de PHP**

1. En el menú lateral, click en **"PHP"**
2. Verás las versiones de PHP instaladas

### **4.2 Verificar PHP 8.2+**

1. Buscar **PHP 8.2** o **PHP 8.3** en la lista
2. Si no existe, instalar:
   - Click en **"+ Install PHP"**
   - Seleccionar versión 8.2 o superior

### **4.3 Verificar extensiones críticas**

1. Click en la versión de PHP que vas a usar (8.2)
2. Buscar sección **"Extensions"** o **"Extensiones"**
3. **Verificar que estén ACTIVADAS** (✅):

   ```
   Extensiones CRÍTICAS para Laravel:
   ✅ bcmath
   ✅ ctype
   ✅ curl
   ✅ dom
   ✅ fileinfo
   ✅ json
   ✅ mbstring
   ✅ openssl
   ✅ pdo
   ✅ pdo_mysql
   ✅ tokenizer
   ✅ xml
   ✅ zip
   
   Extensiones RECOMENDADAS:
   ✅ redis (para cache)
   ✅ imagick o gd (para imágenes)
   ```

4. **Si falta alguna:**
   - Buscar en la lista de extensiones disponibles
   - Click en el checkbox para activarla
   - Click en **"Save"** o **"Guardar"**

---

## 📦 **PASO 5: VERIFICAR COMPOSER**

### **5.1 Ir a sección Applications**

1. En el menú lateral, click en **"Applications"** o **"Aplicaciones"**

### **5.2 Verificar Composer**

1. Buscar **"Composer"** en la lista
2. **Estado:** Debe estar **Installed** o **Instalado**

### **5.3 Si NO está instalado:**

1. Click en **"+ Install"** junto a Composer
2. Esperar 1-2 minutos
3. Verificar que aparezca como instalado

---

## 🔴 **PASO 6: VERIFICAR/INSTALAR REDIS**

### **6.1 Verificar Redis**

1. En **"Applications"**, buscar **"Redis"**
2. **Estado:** Debe estar **Installed** y **Running**

### **6.2 Si NO está instalado:**

1. Click en **"+ Install"** junto a Redis
2. Esperar instalación
3. Verificar estado: **Running**

### **6.3 Si está instalado pero no corriendo:**

1. Click en el botón **"Start"**
2. Esperar a que cambie a **Running**

---

## 🔐 **PASO 7: CONFIGURAR ACCESO SSH**

### **7.1 Verificar acceso SSH actual**

1. Abrir terminal local
2. Probar conexión:
   ```bash
   ssh fastuser@38.242.206.48
   ```

3. Ingresar password: `jVe6QUtx2qO784Py`

4. **Si funciona:** ✅ Continuar al Paso 8

### **7.2 Si NO funciona - Configurar en FastPanel:**

1. En FastPanel, ir a **"Users"** o **"Usuarios"**
2. Click en **"fastuser"**
3. Buscar sección **"SSH Access"**
4. **Verificar que esté ENABLED**
5. Si no está, activarlo y guardar

---

## 📂 **PASO 8: PREPARAR DIRECTORIO DEL SITIO**

### **8.1 Obtener ruta del sitio**

1. En FastPanel, ir a **"Sites"**
2. Click en **appvillamitre.surtekbb.com**
3. Buscar **"Site Path"** o **"Ruta del sitio"**
4. Copiar la ruta (ejemplo: `/var/www/fastuser/data/www/appvillamitre.surtekbb.com`)

### **8.2 Identificar usuario del sitio**

1. En la misma página del sitio
2. Buscar **"Owner"** o **"Propietario"**
3. Anotar el usuario (probablemente: `fastuser`)

---

## 🎯 **PASO 9: CLONAR PROYECTO VÍA SSH**

### **9.1 Conectar por SSH**

```bash
ssh fastuser@38.242.206.48
# Ingresar password: jVe6QUtx2qO784Py
```

### **9.2 Ir al directorio del sitio**

```bash
# Cambiar 'fastuser' si el usuario es diferente
cd /var/www/fastuser/data/www/appvillamitre.surtekbb.com
```

### **9.3 Verificar contenido actual**

```bash
ls -la
```

**Posibles escenarios:**

**A) Directorio vacío o con archivos de prueba:**
```bash
# Limpiar todo
rm -rf * .[^.]*

# Verificar que esté vacío
ls -la
```

**B) Ya tiene contenido importante:**
```bash
# Hacer backup
mv index.html index.html.backup
```

### **9.4 Clonar repositorio GitHub**

```bash
# Clonar el proyecto
git clone https://github.com/HeraclioOrtiz/vmServer.git .

# El punto (.) al final es IMPORTANTE - clona en directorio actual
```

**Output esperado:**
```
Cloning into '.'...
remote: Enumerating objects...
Receiving objects: 100%
Resolving deltas: 100%
```

### **9.5 Verificar que se clonó correctamente**

```bash
# Listar archivos
ls -la

# Deberías ver:
# - app/
# - bootstrap/
# - config/
# - database/
# - public/
# - artisan
# - composer.json
# - etc.
```

---

## ⚙️ **PASO 10: CONFIGURAR .ENV**

### **10.1 Copiar template**

```bash
cp .env.production.example .env
```

### **10.2 Editar .env**

```bash
nano .env
```

### **10.3 Configurar valores críticos**

Buscar y modificar estas líneas:

```env
# APLICACIÓN
APP_ENV=production
APP_DEBUG=false              # ¡VERIFICAR QUE SEA FALSE!
APP_URL=https://appvillamitre.surtekbb.com

# BASE DE DATOS
DB_DATABASE=villamitre_prod
DB_USERNAME=villamitre_user
DB_PASSWORD=[EL PASSWORD QUE GUARDASTE EN EL PASO 2]

# CACHE
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### **10.4 Guardar y salir**

- **Guardar:** `Ctrl + O` (Enter para confirmar)
- **Salir:** `Ctrl + X`

### **10.5 Verificar cambios**

```bash
# Ver que APP_DEBUG esté en false
cat .env | grep APP_DEBUG

# Debe mostrar: APP_DEBUG=false
```

---

## 🔨 **PASO 11: EJECUTAR DEPLOYMENT INICIAL**

### **11.1 Dar permisos al script**

```bash
chmod +x deploy-inicial.sh
```

### **11.2 Ejecutar deployment**

```bash
./deploy-inicial.sh
```

### **11.3 Seguir el progreso**

El script mostrará cada paso:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 DEPLOYMENT INICIAL - VILLA MITRE API
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1️⃣  Verificando archivo .env...
✅ Archivo .env encontrado

2️⃣  Instalando dependencias de Composer...
✅ Dependencias instaladas

3️⃣  Verificando APP_KEY...
✅ APP_KEY generada

4️⃣  Ejecutando migraciones de base de datos...
✅ Migraciones completadas

5️⃣  Creando usuarios iniciales...
✅ Usuarios creados

6️⃣  Limpiando cache anterior...
✅ Cache limpiado

7️⃣  Optimizando aplicación para producción...
✅ Optimizaciones aplicadas

8️⃣  Configurando permisos de archivos...
✅ Permisos configurados

9️⃣  Verificando JWT secret...
✅ JWT secret ya existe

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎉 DEPLOYMENT INICIAL COMPLETADO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📋 CREDENCIALES DE ACCESO:
...
```

### **11.4 Verificar credenciales mostradas**

El script mostrará:

```
👑 ADMINISTRADOR:
   Email: admin@villamitre.com
   DNI: 11111111
   Password: admin123

👨‍🏫 PROFESOR:
   Email: profesor@villamitre.com
   DNI: 22222222
   Password: profesor123

👤 ESTUDIANTE DE PRUEBA:
   Email: maria.garcia@villamitre.com
   DNI: 55555555
   Password: maria123
```

**📸 Tomar captura o copiar estas credenciales**

---

## ✅ **PASO 12: VERIFICACIÓN FINAL**

### **12.1 Verificar sitio en navegador**

1. Abrir navegador
2. Ir a: `https://appvillamitre.surtekbb.com`
3. **Debe mostrar:**
   - Página de Laravel por defecto, O
   - Respuesta JSON de la API, O
   - No debe mostrar error 500 o 404

### **12.2 Test de endpoint de API**

Usar Postman o curl:

```bash
# Test endpoint de health/status
curl https://appvillamitre.surtekbb.com

# Test login
curl -X POST https://appvillamitre.surtekbb.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "11111111",
    "password": "admin123"
  }'
```

**Respuesta esperada:**
```json
{
  "status": "success",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "Admin Villa Mitre",
      ...
    }
  }
}
```

### **12.3 Verificar logs**

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Buscar errores
grep -i error storage/logs/laravel.log
```

**No debe haber errores críticos**

### **12.4 Verificar base de datos**

```bash
php artisan tinker

# En tinker:
>>> User::count()
# Debe mostrar: 3

>>> User::all()->pluck('name')
# Debe mostrar los 3 usuarios creados

>>> exit
```

---

## 🎨 **PASO 13: CONFIGURACIONES FINALES EN FASTPANEL**

### **13.1 Configurar tareas programadas (Cron)**

1. En FastPanel, ir a **"Cron"** o **"Tareas programadas"**
2. Click en **"+ Add Cron Job"**
3. **Configurar:**
   ```
   Command: php /var/www/fastuser/data/www/appvillamitre.surtekbb.com/artisan schedule:run
   Schedule: * * * * * (cada minuto)
   User: fastuser
   ```
4. Click en **"Create"**

### **13.2 Configurar supervisor (Queue Worker)**

Si usas queues:

1. En FastPanel, ir a **"Supervisor"**
2. Click en **"+ Add Process"**
3. **Configurar:**
   ```
   Command: php /var/www/fastuser/data/www/appvillamitre.surtekbb.com/artisan queue:work redis --sleep=3 --tries=3
   Process Name: laravel-worker
   Number of Processes: 1
   User: fastuser
   ```
4. Click en **"Create"** y **"Start"**

---

## 📊 **CHECKLIST FINAL**

Verificar que todo esté completado:

### **En FastPanel:**
- [x] Base de datos `villamitre_prod` creada
- [x] Usuario MySQL `villamitre_user` creado
- [x] Sitio `appvillamitre.surtekbb.com` configurado
- [x] Document root apunta a `/public`
- [x] SSL instalado (HTTPS funcionando)
- [x] PHP 8.2+ instalado
- [x] Todas las extensiones PHP activadas
- [x] Composer instalado
- [x] Redis instalado y corriendo
- [x] Cron job configurado
- [x] Supervisor configurado (opcional)

### **En el Servidor (SSH):**
- [x] Proyecto clonado desde GitHub
- [x] `.env` configurado correctamente
- [x] `APP_DEBUG=false` verificado
- [x] Script `deploy-inicial.sh` ejecutado
- [x] Migraciones completadas
- [x] Usuarios creados
- [x] Permisos configurados
- [x] Aplicación optimizada

### **Verificación Funcional:**
- [x] Sitio accesible en HTTPS
- [x] API respondiendo correctamente
- [x] Login funcionando
- [x] Sin errores en logs
- [x] Base de datos poblada

---

## 🚨 **TROUBLESHOOTING**

### **Error 500 Internal Server Error**

```bash
# Ver logs
tail -50 storage/logs/laravel.log

# Verificar permisos
chmod -R 775 storage bootstrap/cache
chown -R fastuser:fastuser storage bootstrap/cache

# Limpiar cache
php artisan optimize:clear
```

### **Error de conexión a base de datos**

```bash
# Verificar credenciales en .env
cat .env | grep DB_

# Test conexión
php artisan db:show
```

### **Página en blanco**

```bash
# Verificar APP_KEY
cat .env | grep APP_KEY

# Regenerar si está vacío
php artisan key:generate --force
```

### **Error 404 en rutas**

```bash
# Verificar document root en FastPanel
# Debe apuntar a /public

# Limpiar cache de rutas
php artisan route:clear
php artisan route:cache
```

---

## 📝 **COMANDOS ÚTILES POST-DEPLOYMENT**

### **Ver logs:**
```bash
tail -f storage/logs/laravel.log
```

### **Limpiar todo el cache:**
```bash
php artisan optimize:clear
```

### **Re-optimizar:**
```bash
php artisan optimize
```

### **Asignar estudiantes al profesor:**
```bash
php artisan students:assign-to-professor
```

### **Ver estado de base de datos:**
```bash
php artisan db:show
```

### **Acceder a tinker:**
```bash
php artisan tinker
```

---

## 🎉 **¡FELICITACIONES!**

Tu aplicación Villa Mitre ya está desplegada en producción en:

**🌐 https://appvillamitre.surtekbb.com**

### **Credenciales de acceso:**

```
👑 ADMIN:
Email: admin@villamitre.com
DNI: 11111111
Password: admin123

👨‍🏫 PROFESOR:
Email: profesor@villamitre.com
DNI: 22222222
Password: profesor123
```

### **Próximos pasos:**

1. ✅ Cambiar contraseñas de usuarios
2. ✅ Crear ejercicios desde panel admin
3. ✅ Crear plantillas de entrenamiento
4. ✅ Registrar estudiantes reales
5. ✅ Configurar email (SMTP)
6. ✅ Configurar backups automáticos
7. ✅ Monitorear logs regularmente

---

**¿Problemas durante el deployment? Revisa la sección de Troubleshooting o consulta los logs.** 🚀
