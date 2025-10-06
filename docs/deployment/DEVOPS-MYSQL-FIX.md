# 🚨 INSTRUCTIVO DEVOPS - FIX CRÍTICO MYSQL

## 📋 RESUMEN DEL PROBLEMA
- **Endpoint afectado:** `/api/auth/login`
- **Error:** HTTP 500 - "could not find driver"
- **Causa:** Driver MySQL faltante en PHP
- **Impacto:** Crash del servidor con credenciales específicas

## 🎯 SOLUCIÓN REQUERIDA

### 1. VERIFICAR ESTADO ACTUAL

```bash
# Conectarse al servidor
ssh usuario@surtekbb.com

# Verificar versión de PHP
php -v

# Verificar extensiones MySQL instaladas
php -m | grep -i mysql
php -m | grep -i pdo

# Verificar estado de MySQL
sudo systemctl status mysql
```

### 2. INSTALAR DRIVERS MYSQL

**Para PHP 8.x:**
```bash
# Actualizar repositorios
sudo apt update

# Instalar drivers MySQL para PHP
sudo apt install php8.1-mysql php8.1-pdo-mysql
# O para PHP 8.2:
sudo apt install php8.2-mysql php8.2-pdo-mysql
# O para PHP 8.3:
sudo apt install php8.3-mysql php8.3-pdo-mysql

# Verificar instalación
php -m | grep -i mysql
# Debe mostrar: mysql, mysqli, pdo_mysql
```

### 3. REINICIAR SERVICIOS

```bash
# Reiniciar Apache
sudo systemctl restart apache2

# Verificar estado
sudo systemctl status apache2

# Reiniciar PHP-FPM (si aplica)
sudo systemctl restart php8.1-fpm
# O la versión correspondiente
```

### 4. VERIFICAR CONFIGURACIÓN PHP

```bash
# Encontrar archivo php.ini
php --ini

# Verificar extensiones en php.ini
sudo nano /etc/php/8.1/apache2/php.ini

# Buscar y asegurar que estén descomentadas:
extension=mysql
extension=mysqli  
extension=pdo_mysql
```

### 5. PROBAR CONECTIVIDAD

```bash
# Test básico de conexión MySQL
mysql -u root -p -h localhost

# Test desde PHP
php -r "try { new PDO('mysql:host=localhost;dbname=vmserver', 'root', ''); echo 'OK'; } catch(Exception \$e) { echo \$e->getMessage(); }"
```

### 6. DESPLEGAR FIX DEL CÓDIGO

```bash
# Ir al directorio del proyecto
cd /var/www/html/vmserver  # O la ruta correspondiente

# Hacer backup del archivo actual
cp app/Http/Controllers/AuthController.php app/Http/Controllers/AuthController.php.backup

# Subir el archivo actualizado desde el repositorio
# (El archivo ya tiene el fix de manejo de excepciones)

# Limpiar cache de Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 7. VERIFICAR EL FIX

**Test 1 - Health Check:**
```bash
curl -X GET http://surtekbb.com/api/health
# Debe retornar JSON con status de BD
```

**Test 2 - Credenciales problemáticas:**
```bash
curl -X POST http://surtekbb.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"dni":"58964605","password":"Zzxx4518688"}'
```

**Resultado esperado:**
- ✅ Status: 401 o 422 (NO 500)
- ✅ Content-Type: application/json
- ✅ Response: JSON válido
- ✅ Connection: keep-alive

### 8. MONITOREAR LOGS

```bash
# Logs de Apache
tail -f /var/log/apache2/error.log

# Logs de PHP
tail -f /var/log/php8.1/error.log

# Logs de Laravel (si configurado)
tail -f /var/www/html/vmserver/storage/logs/laravel.log
```

## 🔧 TROUBLESHOOTING

### Si persiste "could not find driver":

```bash
# Verificar que PHP web use la misma versión que CLI
php -v
sudo apache2ctl -M | grep php

# Reiniciar completamente Apache
sudo systemctl stop apache2
sudo systemctl start apache2

# Verificar configuración Apache
sudo apache2ctl configtest
```

### Si MySQL no conecta:

```bash
# Verificar usuario y permisos
sudo mysql -u root -p
SHOW DATABASES;
USE vmserver;
SHOW TABLES;

# Verificar configuración Laravel
cat /var/www/html/vmserver/.env | grep DB_
```

## ✅ CHECKLIST POST-FIX

- [ ] Drivers MySQL instalados
- [ ] Apache reiniciado
- [ ] Código actualizado con fix
- [ ] Cache Laravel limpio
- [ ] Test de credenciales problemáticas exitoso
- [ ] Logs monitoreados por 24h
- [ ] Health check funcionando

## 📞 CONTACTO

Si hay problemas durante la implementación:
- Documentar error exacto
- Capturar logs relevantes  
- Reportar al equipo de desarrollo

---
**Fecha:** 2025-09-17  
**Prioridad:** CRÍTICA  
**Tiempo estimado:** 15-30 minutos
