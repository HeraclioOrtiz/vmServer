# ✅ CHECKLIST DE DEPLOYMENT - VILLA MITRE SERVER

## 📋 Pre-Deployment (Local)

### **🧪 Testing Completo**
- [ ] `test_api_integration.php` - Pasa sin errores
- [ ] `test_registration_flow.php` - Registro + promoción OK
- [ ] `test_login_flow.php` - Login + refresh OK
- [ ] `check_database_images.php` - Imágenes funcionando
- [ ] Postman collection - Todos los endpoints OK
- [ ] Manual testing - Flujos principales OK

### **🔧 Configuración Local**
- [ ] `.env` configurado correctamente
- [ ] Base de datos creada y migrada
- [ ] `composer install` ejecutado
- [ ] `php artisan key:generate` ejecutado
- [ ] `storage:link` creado
- [ ] Permisos storage/ correctos
- [ ] Logs sin errores críticos

### **📁 Archivos Preparados**
- [ ] `apache/vmserver.conf` - Virtual host configurado
- [ ] `.htaccess` - Security rules aplicadas
- [ ] Documentación actualizada
- [ ] Scripts de testing incluidos

## 🚀 Deployment (Servidor)

### **🛠️ Instalación Servidor**
- [ ] PHP 8.2+ instalado
- [ ] Apache 2.4+ instalado
- [ ] MySQL 8.0+ instalado
- [ ] Composer instalado
- [ ] Extensiones PHP requeridas

### **📂 Subida de Archivos**
- [ ] Código fuente subido
- [ ] Permisos archivos configurados (644/755)
- [ ] `storage/` con permisos 775
- [ ] `bootstrap/cache/` con permisos 775

### **⚙️ Configuración Servidor**
- [ ] Virtual host Apache configurado
- [ ] Módulos Apache habilitados (rewrite, ssl, headers)
- [ ] Base de datos creada
- [ ] Usuario MySQL configurado
- [ ] `.env` producción configurado

### **🔐 Variables .env Producción**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_HOST=localhost
DB_DATABASE=vmserver
DB_USERNAME=vmserver_user
DB_PASSWORD=PASSWORD_SEGURO

# API terceros (CRÍTICO)
SOCIOS_API_BASE=https://clubvillamitre.com/api_back_socios
SOCIOS_API_LOGIN=surtek
SOCIOS_API_TOKEN=4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
SOCIOS_IMG_BASE=https://clubvillamitre.com/images/socios
```

### **🔧 Comandos Post-Deploy**
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan storage:link`

## 🧪 Testing Post-Deploy

### **🌐 Verificación Web**
- [ ] Sitio web carga correctamente
- [ ] SSL/HTTPS funcionando
- [ ] Logs Apache sin errores
- [ ] phpMyAdmin accesible (si aplica)

### **🔌 Testing API**
- [ ] `GET /api/health` - Responde OK
- [ ] `POST /api/register` - Funciona
- [ ] `POST /api/login` - Funciona
- [ ] Imágenes se descargan correctamente
- [ ] Respuestas incluyen `foto_url`

### **📊 Performance**
- [ ] Tiempo respuesta < 2 segundos
- [ ] Memoria PHP suficiente
- [ ] Espacio disco disponible
- [ ] Logs rotan correctamente

## 🔒 Seguridad

### **🛡️ Configuración Seguridad**
- [ ] SSL/TLS configurado
- [ ] Certificado válido
- [ ] Headers seguridad aplicados
- [ ] Archivos sensibles protegidos (.env, composer.json)
- [ ] Firewall configurado
- [ ] Acceso SSH restringido

### **🔐 Credenciales**
- [ ] Passwords seguros
- [ ] API tokens válidos
- [ ] Permisos base datos mínimos
- [ ] Backup credenciales seguro

## 📈 Monitoreo

### **📊 Logs a Monitorear**
- [ ] `storage/logs/laravel.log` - Errores aplicación
- [ ] Apache error.log - Errores servidor
- [ ] MySQL error.log - Errores base datos

### **🚨 Alertas Configurar**
- [ ] Errores 500 frecuentes
- [ ] API terceros no disponible
- [ ] Espacio disco bajo
- [ ] Memoria alta

## 🔄 Mantenimiento

### **📅 Tareas Regulares**
- [ ] Backup base datos (diario)
- [ ] Rotación logs (semanal)
- [ ] Actualización dependencias (mensual)
- [ ] Monitoreo performance (continuo)

### **🆙 Actualizaciones**
- [ ] Proceso rollback definido
- [ ] Testing en staging
- [ ] Backup pre-actualización
- [ ] Comunicación downtime

---

## 🎯 Comandos Rápidos Post-Deploy

```bash
# Verificación rápida
curl -I https://tudominio.com/api/health
php artisan migrate:status
tail -f storage/logs/laravel.log

# Optimización
php artisan optimize
php artisan queue:restart

# Backup
mysqldump -u user -p vmserver > backup_$(date +%Y%m%d).sql
```

**✅ Deployment completado cuando todos los items están marcados.**
