# 🚀 GUÍA TEÓRICA - DEPLOYMENT BACKEND LARAVEL EN PRODUCCIÓN

**Fecha:** 2025-10-06  
**Proyecto:** Villa Mitre API Backend  
**Framework:** Laravel 11.x

---

## 📋 **ÍNDICE**

1. [Conceptos Fundamentales](#conceptos-fundamentales)
2. [Opciones de Hosting](#opciones-de-hosting)
3. [Requisitos del Servidor](#requisitos-del-servidor)
4. [Proceso de Deployment](#proceso-de-deployment)
5. [Configuraciones de Producción](#configuraciones-de-producción)
6. [Optimizaciones](#optimizaciones)
7. [Seguridad](#seguridad)
8. [Monitoreo y Mantenimiento](#monitoreo-y-mantenimiento)
9. [CI/CD (Integración y Deploy Continuo)](#cicd)
10. [Checklist Final](#checklist-final)

---

## 🎯 **CONCEPTOS FUNDAMENTALES**

### **¿Qué significa "Deployment"?**
El deployment es el proceso de llevar tu aplicación desde el entorno de desarrollo local a un servidor de producción accesible por internet, donde los usuarios finales pueden utilizarla.

### **Diferencias entre Desarrollo y Producción:**

| Aspecto | Desarrollo (Local) | Producción (Servidor) |
|---------|-------------------|----------------------|
| **Acceso** | Solo tú (localhost) | Usuarios reales (internet) |
| **Debug** | Activo (muestra errores) | Desactivado (seguridad) |
| **Performance** | No crítico | Optimizado al máximo |
| **Base de datos** | SQLite/MySQL local | MySQL/PostgreSQL robusto |
| **Cache** | Desactivado | Activado (Redis/Memcached) |
| **HTTPS** | Opcional | Obligatorio (SSL) |
| **Logs** | Detallados | Controlados |

### **¿Por qué dejar LocalTunnel?**
LocalTunnel es **solo para desarrollo/testing**:
- ❌ No es confiable para producción
- ❌ Se desconecta frecuentemente
- ❌ No ofrece certificados SSL permanentes
- ❌ No tiene SLA (acuerdo de nivel de servicio)
- ❌ Rendimiento limitado

---

## 🌐 **OPCIONES DE HOSTING**

### **1. VPS Tradicional (Virtual Private Server)**
Un servidor virtual dedicado donde tú controlas todo.

**Proveedores populares:**
- **DigitalOcean** (muy popular para Laravel) - Desde $6/mes
- **Linode/Akamai** - Desde $5/mes
- **Vultr** - Desde $6/mes
- **Hetzner** (Europa, más barato) - Desde €4/mes
- **AWS Lightsail** - Desde $5/mes

**Ventajas:**
- ✅ Control total del servidor
- ✅ Costo predecible
- ✅ Escalable manualmente
- ✅ Buena relación precio/rendimiento

**Desventajas:**
- ❌ Requiere conocimientos de DevOps
- ❌ Mantenimiento manual (actualizaciones, seguridad)
- ❌ Responsabilidad del uptime

### **2. Plataformas Gestionadas (PaaS)**
Servicios que automatizan la gestión del servidor.

**Opciones:**
- **Laravel Forge** ($12-19/mes) + VPS ($6-10/mes) = $18-29/mes total
  - Gestión automática de servidores
  - Deploy con un click
  - SSL automático
  - Backups programados
  
- **Laravel Cloud** (Nuevo, oficial de Laravel)
  - Auto-escalado
  - Completamente gestionado
  - Optimizado para Laravel

- **Ploi.io** (Alternativa a Forge) - $10/mes + VPS

**Ventajas:**
- ✅ Sin conocimientos de DevOps necesarios
- ✅ Configuración automática
- ✅ Deploy simplificado
- ✅ SSL y seguridad automatizados

**Desventajas:**
- ❌ Costo adicional mensual
- ❌ Menos control granular

### **3. Cloud Escalable (Para apps grandes)**
Servicios de nube empresarial con auto-escalado.

**Opciones:**
- **AWS (Amazon Web Services)**
  - EC2 para servidores
  - RDS para base de datos
  - ElastiCache para Redis
  
- **Google Cloud Platform**
- **Microsoft Azure**

**Ventajas:**
- ✅ Auto-escalado automático
- ✅ Alta disponibilidad
- ✅ Infraestructura global

**Desventajas:**
- ❌ Complejidad alta
- ❌ Costos variables (difícil de predecir)
- ❌ Curva de aprendizaje pronunciada

### **🎯 RECOMENDACIÓN PARA VILLA MITRE:**

**Opción 1 (Más económica):** DigitalOcean Droplet ($6/mes) + configuración manual
**Opción 2 (Recomendada):** DigitalOcean ($6/mes) + Laravel Forge ($12/mes) = $18/mes total
**Opción 3 (Más profesional):** AWS Lightsail + Laravel Forge

---

## 💻 **REQUISITOS DEL SERVIDOR**

### **Hardware Mínimo:**
- **CPU:** 1-2 cores
- **RAM:** 2GB mínimo (4GB recomendado)
- **Disco:** 25-50GB SSD
- **Ancho de banda:** 1TB/mes

### **Software Requerido:**

#### **1. Sistema Operativo:**
- **Ubuntu 22.04 LTS** (más común para Laravel)
- Alternativas: Debian 11+, CentOS Stream 9

#### **2. Servidor Web:**
- **Nginx** (recomendado) - Más rápido y eficiente
- Apache con mod_php (alternativa)

#### **3. PHP:**
- **Versión:** PHP 8.2 o superior (tu proyecto usa 8.2+)
- **Extensiones requeridas:**
  - OpenSSL
  - PDO (para base de datos)
  - Mbstring (manejo de strings)
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  - GD (para imágenes)

#### **4. Base de Datos:**
- **MySQL 8.0+** o **MariaDB 10.11+**
- **PostgreSQL** (alternativa robusta)

#### **5. Cache y Queues:**
- **Redis** (recomendado para cache y queues)
- Memcached (alternativa para cache)

#### **6. Otras herramientas:**
- **Composer** (gestor de dependencias PHP)
- **Node.js y NPM** (si tienes assets frontend)
- **Git** (para deployment desde repositorio)
- **Supervisor** (para procesar queues en background)

---

## 🔄 **PROCESO DE DEPLOYMENT**

### **FASE 1: PREPARACIÓN PRE-DEPLOYMENT**

#### **1. Auditoría de Código:**
- ✅ Revisar que no haya credenciales hardcodeadas
- ✅ Verificar que todos los archivos sensibles estén en `.gitignore`
- ✅ Asegurar que `.env` no esté en el repositorio

#### **2. Testing Local:**
- ✅ Ejecutar todos los tests (`php artisan test`)
- ✅ Verificar que no haya errores de sintaxis
- ✅ Probar todas las rutas críticas
- ✅ Verificar migraciones en BD limpia

#### **3. Preparar Configuraciones:**
- ✅ Crear archivo `.env.production` con valores reales
- ✅ Generar nueva `APP_KEY` para producción
- ✅ Configurar credenciales de BD de producción
- ✅ Configurar servicios externos (email, S3, etc.)

### **FASE 2: CONFIGURACIÓN DEL SERVIDOR**

#### **1. Conexión Inicial:**
- Conectarse vía SSH al servidor
- Actualizar sistema operativo
- Configurar firewall (UFW en Ubuntu)

#### **2. Instalación del Stack:**
- Instalar Nginx/Apache
- Instalar PHP 8.2+ con todas las extensiones
- Instalar MySQL/MariaDB
- Instalar Redis
- Instalar Composer
- Instalar Node.js (si es necesario)

#### **3. Configuración de Nginx:**
- Crear virtual host para tu dominio
- Configurar document root a `/public`
- Habilitar compresión gzip
- Configurar límites de upload
- Preparar para SSL

#### **4. Configuración de Base de Datos:**
- Crear base de datos para la aplicación
- Crear usuario con permisos específicos
- Configurar character set UTF8MB4
- Habilitar logs lentos (para debugging)

### **FASE 3: DEPLOYMENT DE LA APLICACIÓN**

#### **1. Clonar Repositorio:**
- Clonar desde GitHub al servidor
- Típicamente en `/var/www/nombre-proyecto`

#### **2. Instalación de Dependencias:**
- Ejecutar `composer install --optimize-autoloader --no-dev`
  - `--no-dev`: No instala dependencias de desarrollo
  - `--optimize-autoloader`: Optimiza el autoloader para producción

#### **3. Configuración del Entorno:**
- Copiar archivo `.env.production` a `.env`
- Generar `APP_KEY` si no existe
- Configurar permisos de archivos:
  - `storage/` debe ser escribible
  - `bootstrap/cache/` debe ser escribible

#### **4. Migraciones y Seeds:**
- Ejecutar migraciones: `php artisan migrate --force`
- Ejecutar seeders si es necesario (solo primera vez)

#### **5. Optimizaciones:**
- Cache de configuración
- Cache de rutas
- Cache de vistas
- Optimización de autoloader

### **FASE 4: CONFIGURACIÓN DE SSL (HTTPS)**

#### **Opción 1: Let's Encrypt (Gratis)**
- Instalar Certbot
- Generar certificados SSL automáticamente
- Renovación automática cada 90 días

#### **Opción 2: Cloudflare (Recomendado para inicio)**
- Apuntar dominio a Cloudflare
- SSL automático y gratuito
- CDN incluido (acelera tu sitio)
- DDoS protection
- Firewall de aplicaciones web (WAF)

### **FASE 5: CONFIGURACIÓN DE DOMINIO**

#### **DNS Records:**
- **A Record:** Apuntar dominio al IP del servidor
  - Ejemplo: `api.villamitre.com` → `167.99.123.45`
- **CNAME (opcional):** Para subdominios adicionales
- **TTL:** Configurar tiempo de caché DNS

#### **Subdominios Sugeridos:**
- `api.villamitre.com` - Para el backend API
- `admin.villamitre.com` - Para el panel de administración
- `app.villamitre.com` - Para el frontend móvil

---

## ⚙️ **CONFIGURACIONES DE PRODUCCIÓN**

### **Archivo .env de Producción:**

#### **Configuraciones Críticas:**

```env
# Entorno
APP_ENV=production
APP_DEBUG=false  # ¡CRÍTICO! Debe estar en false
APP_KEY=[generar nueva key]
APP_URL=https://api.villamitre.com

# Base de Datos Producción
DB_CONNECTION=mysql
DB_HOST=127.0.0.1  # o IP del servidor de BD
DB_PORT=3306
DB_DATABASE=villamitre_prod
DB_USERNAME=villamitre_user
DB_PASSWORD=[contraseña segura]

# Cache (Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=[contraseña redis]
REDIS_PORT=6379

# Email (Configurar servicio real)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # o servicio real
MAIL_PORT=2525
MAIL_USERNAME=[username]
MAIL_PASSWORD=[password]
MAIL_ENCRYPTION=tls

# Logging
LOG_CHANNEL=daily  # Rotar logs diariamente
LOG_LEVEL=error     # Solo errores en producción

# JWT para API
JWT_SECRET=[secret seguro]
JWT_TTL=60  # Tiempo de vida del token
```

### **Diferencias Clave con Desarrollo:**

| Variable | Desarrollo | Producción |
|----------|-----------|------------|
| `APP_ENV` | local | production |
| `APP_DEBUG` | true | **false** |
| `APP_URL` | http://localhost | https://api.dominio.com |
| `DB_*` | BD local | BD producción |
| `CACHE_DRIVER` | file | redis |
| `SESSION_DRIVER` | file | redis |
| `QUEUE_CONNECTION` | sync | redis |
| `LOG_LEVEL` | debug | error |

---

## 🚀 **OPTIMIZACIONES**

### **1. Command: `php artisan optimize`**
Este comando ejecuta múltiples optimizaciones automáticamente:

#### **¿Qué hace?**
- Cachea configuraciones
- Cachea rutas
- Cachea vistas
- Optimiza autoloader de Composer

#### **¿Cuándo ejecutarlo?**
- Después de cada deployment
- Después de cambios en configuración
- Después de cambios en rutas

### **2. Cache de Configuración**
```bash
php artisan config:cache
```
- Lee todos los archivos `config/*` una sola vez
- Genera un archivo cache
- **20-30% más rápido** en carga

### **3. Cache de Rutas**
```bash
php artisan route:cache
```
- Cachea todas las rutas de la aplicación
- **Mejora dramática** en apps con muchas rutas
- ⚠️ No usar si tienes closures en rutas

### **4. Cache de Vistas**
```bash
php artisan view:cache
```
- Pre-compila todas las vistas Blade
- Reduce tiempo de primera carga

### **5. Optimización de Composer**
```bash
composer install --optimize-autoloader --no-dev
composer dump-autoload --optimize --classmap-authoritative
```
- Genera classmap optimizado
- Elimina búsquedas de archivos innecesarias

### **6. OPcache (PHP)**
- Habilitar OPcache en PHP
- Cachea bytecode PHP compilado
- **50-70% mejora de rendimiento**

### **7. Redis para Cache**
- Usar Redis en lugar de file cache
- Mucho más rápido para operaciones de lectura/escritura
- Soporta TTL (time to live) automático

### **8. CDN (Content Delivery Network)**
- Servir assets estáticos desde CDN
- Cloudflare (gratis) o AWS CloudFront
- Reduce latencia global

### **9. Database Query Optimization**
- Usar índices apropiados
- Implementar eager loading (evitar N+1 queries)
- Cachear queries frecuentes

---

## 🔒 **SEGURIDAD**

### **1. Configuraciones Esenciales:**

#### **Debug Mode OFF:**
```env
APP_DEBUG=false  # ¡CRÍTICO!
```
- Si está en `true`, expone:
  - Rutas de archivos del servidor
  - Credenciales de BD en errores
  - Información de versiones
  - Stack traces completos

#### **HTTPS Obligatorio:**
- Forzar HTTPS en todas las rutas
- Configurar `APP_URL` con https://
- Redireccionar HTTP → HTTPS en Nginx

#### **CORS (Cross-Origin Resource Sharing):**
- Configurar qué dominios pueden acceder a la API
- Importante para proteger de ataques XSS
- Laravel tiene middleware CORS integrado

### **2. Protección de Archivos:**

#### **Permisos del Sistema:**
```
- Archivos: 644
- Directorios: 755
- storage/ y bootstrap/cache/: 775
- .env: 600 (solo owner puede leer)
```

#### **Usuario del Servidor:**
- Crear usuario específico para la app
- No usar `root` para ejecutar la aplicación
- Nginx/Apache debe correr con usuario limitado

### **3. Base de Datos:**

#### **Usuario con Permisos Limitados:**
- Crear usuario específico para la app
- Solo permisos SELECT, INSERT, UPDATE, DELETE
- NO otorgar permisos DROP, CREATE DATABASE

#### **Conexiones Seguras:**
- Usar SSL/TLS para conexiones a BD
- No exponer BD directamente a internet
- Solo permitir conexiones desde el servidor de app

### **4. Firewall:**

#### **Puertos Abiertos Mínimos:**
- Puerto 80 (HTTP) - Solo para redirección
- Puerto 443 (HTTPS) - Para tráfico real
- Puerto 22 (SSH) - Solo desde IPs específicas
- Cerrar todos los demás puertos

#### **Fail2Ban:**
- Instalar Fail2Ban
- Protege contra ataques de fuerza bruta
- Banea IPs maliciosas automáticamente

### **5. Rate Limiting:**
- Limitar requests por IP
- Laravel tiene throttling integrado
- Protege contra ataques DDoS básicos

### **6. Headers de Seguridad:**

Configurar en Nginx:
- `X-Frame-Options` (previene clickjacking)
- `X-Content-Type-Options` (previene MIME sniffing)
- `X-XSS-Protection` (protección XSS básica)
- `Strict-Transport-Security` (forzar HTTPS)
- `Content-Security-Policy` (prevenir XSS avanzado)

### **7. Backups Automáticos:**

#### **Qué respaldar:**
- Base de datos (diario)
- Archivos de `storage/` (semanal)
- Archivo `.env` (encriptado)

#### **Dónde guardar:**
- Fuera del servidor (AWS S3, Backblaze)
- Encriptados
- Con retención de 30 días mínimo

### **8. Actualizaciones:**
- Mantener Laravel actualizado
- Actualizar dependencias de Composer mensualmente
- Actualizar PHP y extensiones
- Actualizar sistema operativo

---

## 📊 **MONITOREO Y MANTENIMIENTO**

### **1. Logs:**

#### **Tipos de Logs:**
- **Application logs:** Laravel escribe en `storage/logs/`
- **Nginx access logs:** Quién visita tu sitio
- **Nginx error logs:** Errores del servidor web
- **MySQL slow query log:** Queries lentas

#### **Gestión de Logs:**
- Rotar logs diariamente
- Mantener solo últimos 14 días
- Comprimir logs antiguos
- Monitorear tamaño del disco

### **2. Monitoreo de Uptime:**

#### **Servicios Recomendados:**
- **UptimeRobot** (gratis hasta 50 monitores)
  - Chequea cada 5 minutos
  - Notifica si el sitio cae
  
- **Pingdom** (de pago, más profesional)
- **StatusCake** (gratis con limitaciones)

#### **Health Check Endpoint:**
Laravel incluye `/up` endpoint:
- Retorna 200 si la app está funcionando
- Retorna 500 si hay problemas
- Configurar monitor para chequearlo

### **3. Monitoreo de Performance:**

#### **Application Performance Monitoring (APM):**
- **Laravel Telescope** (para desarrollo/staging)
- **Blackfire.io** (profiling de rendimiento)
- **New Relic** (profesional, de pago)
- **DataDog** (empresarial)

#### **Server Monitoring:**
- **Netdata** (gratis, open source)
  - CPU, RAM, Disco
  - Tráfico de red
  - Processes
  
- **Grafana + Prometheus** (más avanzado)

### **4. Error Tracking:**

#### **Servicios de Error Logging:**
- **Sentry** (recomendado)
  - Captura errores automáticamente
  - Stack traces completos
  - Notificaciones en tiempo real
  - Plan gratis disponible
  
- **Bugsnag** (alternativa)
- **Rollbar** (alternativa)

#### **¿Por qué usar error tracking?**
- No tienes que revisar logs manualmente
- Recibes notificaciones de errores inmediatamente
- Puedes ver cuántas veces ocurre cada error
- Información de contexto (usuario, request, etc.)

### **5. Database Monitoring:**
- Monitorear uso de conexiones
- Revisar queries lentas
- Vigilar tamaño de la base de datos
- Configurar alertas de performance

---

## 🔄 **CI/CD (INTEGRACIÓN Y DEPLOYMENT CONTINUO)**

### **¿Qué es CI/CD?**
Automatizar el proceso de testing y deployment cada vez que haces push a GitHub.

### **Flujo Típico:**

1. **Desarrollas** en local
2. **Push** a GitHub
3. **CI ejecuta tests** automáticamente
4. Si tests pasan → **Deploy automático** a producción
5. Si tests fallan → **No se despliega**, recibes notificación

### **Herramientas Populares:**

#### **1. GitHub Actions (Recomendado - Gratis)**
- Integrado en GitHub
- 2,000 minutos gratis/mes
- Configuras en archivo `.github/workflows/deploy.yml`

**Flujo de trabajo típico:**
```
1. Push a main branch
2. Corre tests automáticamente
3. Si pasan, conecta al servidor vía SSH
4. Hace git pull
5. Instala dependencias
6. Ejecuta migraciones
7. Limpia cache
8. Optimiza aplicación
```

#### **2. GitLab CI/CD**
- Similar a GitHub Actions
- Gratis en GitLab

#### **3. Deployment Manual (Más simple):**
- Conectarte por SSH al servidor
- Ejecutar script de deployment

### **Script de Deployment Manual:**
```bash
# 1. Ir al directorio
cd /var/www/villamitre

# 2. Activar modo mantenimiento
php artisan down

# 3. Pull últimos cambios
git pull origin main

# 4. Instalar dependencias
composer install --no-dev --optimize-autoloader

# 5. Ejecutar migraciones
php artisan migrate --force

# 6. Limpiar y optimizar
php artisan optimize:clear
php artisan optimize

# 7. Reiniciar queues
php artisan queue:restart

# 8. Desactivar modo mantenimiento
php artisan up
```

### **Zero-Downtime Deployment:**
Para sitios críticos que no pueden caer:
- Tener dos versiones de la app
- Desplegar en versión inactiva
- Cambiar symlink cuando esté lista
- Laravel Forge/Cloud lo hacen automáticamente

---

## ✅ **CHECKLIST FINAL PRE-DEPLOYMENT**

### **CÓDIGO:**
- [ ] Todos los tests pasan
- [ ] No hay `dd()`, `dump()` o `var_dump()` en código
- [ ] `.env` está en `.gitignore`
- [ ] No hay credenciales hardcodeadas
- [ ] Rutas de API tienen rate limiting
- [ ] Migraciones probadas en BD limpia
- [ ] Seeders verificados (si los usas)

### **CONFIGURACIÓN:**
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generada para producción
- [ ] Credenciales de BD de producción configuradas
- [ ] Configuración de email lista
- [ ] CORS configurado correctamente
- [ ] URLs en `.env` con dominio real

### **SERVIDOR:**
- [ ] PHP 8.2+ instalado con todas las extensiones
- [ ] Nginx/Apache configurado
- [ ] MySQL/PostgreSQL instalado y configurado
- [ ] Redis instalado
- [ ] Composer instalado
- [ ] Permisos de archivos correctos
- [ ] Firewall configurado
- [ ] SSL/TLS certificado instalado
- [ ] Dominio apuntando al servidor

### **SEGURIDAD:**
- [ ] Fail2Ban instalado
- [ ] SSH con key authentication (sin password)
- [ ] Firewall con solo puertos necesarios abiertos
- [ ] Headers de seguridad configurados
- [ ] Backups automáticos configurados
- [ ] Usuario de BD con permisos limitados

### **OPTIMIZACIÓN:**
- [ ] OPcache habilitado
- [ ] Redis configurado para cache
- [ ] `php artisan optimize` ejecutado
- [ ] Gzip compression habilitada en Nginx
- [ ] CDN configurado (si aplica)

### **MONITOREO:**
- [ ] Uptime monitor configurado
- [ ] Error tracking configurado (Sentry)
- [ ] Logs rotando correctamente
- [ ] Health check endpoint funcionando

---

## 📚 **RECURSOS ADICIONALES**

### **Documentación Oficial:**
- Laravel Deployment: https://laravel.com/docs/deployment
- Laravel Forge: https://forge.laravel.com
- DigitalOcean Laravel Guide: https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-application-with-nginx-on-ubuntu-22-04

### **Herramientas Útiles:**
- **Laravel Envoy:** Deployment tasks automation
- **Deployer:** PHP deployment tool
- **Laravel Horizon:** Dashboard para queues con Redis
- **Laravel Telescope:** Debugging helper (solo dev/staging)

### **Comunidad:**
- Laravel News: https://laravel-news.com
- Laracasts (videos tutoriales): https://laracasts.com
- Laravel subreddit: r/laravel

---

## 🎯 **RECOMENDACIÓN FINAL PARA VILLA MITRE**

### **Configuración Óptima Sugerida:**

**Stack:**
- **Servidor:** DigitalOcean Droplet ($6-12/mes)
- **Gestión:** Laravel Forge ($12/mes) - Opcional pero altamente recomendado
- **Dominio:** Registrar dominio propio (ej: villamitre.com)
- **SSL:** Cloudflare (gratis) o Let's Encrypt (gratis)
- **CDN:** Cloudflare (gratis)
- **Monitoreo:** UptimeRobot (gratis) + Sentry (gratis tier)
- **Backups:** Backblaze B2 o AWS S3 (~$1-5/mes)

**Costo Total Estimado:** $18-32/mes
- Sin Forge: ~$6-12/mes (más trabajo manual)
- Con Forge: ~$18-32/mes (automatizado, recomendado)

**Ventajas de esta configuración:**
- ✅ Profesional y confiable
- ✅ Escalable a medida que creces
- ✅ Fácil de mantener
- ✅ Buena relación costo/beneficio
- ✅ SSL y seguridad incluidos
- ✅ Deployment simplificado

---

## 🚀 **PRÓXIMOS PASOS PRÁCTICOS**

1. **Decidir proveedor de hosting** (recomiendo DigitalOcean + Forge)
2. **Registrar dominio** (si no lo tienes)
3. **Crear cuenta en hosting** y provisionar servidor
4. **Configurar DNS** del dominio
5. **Desplegar aplicación** siguiendo esta guía
6. **Configurar monitoreo** y backups
7. **Apagar LocalTunnel** y localhost
8. **¡Lanzar a producción!** 🎉

---

**¿Dudas sobre algún paso específico? ¿Quieres que profundice en algún tema? ¡Pregúntame!** 💬
