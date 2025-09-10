# 🚀 GUÍA COMPLETA DE DEPLOYMENT EN PRODUCCIÓN

## 👋 **Para quien va a hacer el deploy**

¡Hola! Esta guía está diseñada para que puedas deployar el proyecto Villa Mitre Server en un servidor de producción con Apache, **incluso si eres nuevo en el mundo tech**. Te explico todo paso a paso.

---

## 🤔 **PREGUNTAS FRECUENTES ANTES DE EMPEZAR**

### **"¿Qué datos necesito del servidor?"**
Necesitas estos datos **OBLIGATORIOS** del proveedor de hosting:

```
✅ IP del servidor (ej: 192.168.1.100)
✅ Usuario SSH (ej: root o ubuntu)
✅ Contraseña SSH o clave privada
✅ Dominio que vas a usar (ej: api.villamitre.com)
✅ Acceso a panel de control del dominio (para DNS)
```

### **"¿Apache lo monta automáticamente?"**
**NO**. Apache necesita configuración manual:
- ✅ Instalar Apache + PHP + MySQL
- ✅ Configurar Virtual Host para tu dominio
- ✅ Configurar SSL/HTTPS
- ✅ Configurar permisos de archivos

### **"¿Hay que configurar el dominio?"**
**SÍ, es OBLIGATORIO**. Necesitas:
- ✅ Apuntar el dominio a la IP del servidor (DNS)
- ✅ Configurar Virtual Host en Apache
- ✅ Instalar certificado SSL

### **"¿Qué hay que cambiar del código local?"**
Principalmente el archivo `.env`:
- ✅ Cambiar `APP_URL` por tu dominio real
- ✅ Cambiar credenciales de base de datos
- ✅ Poner `APP_DEBUG=false`
- ✅ Poner `APP_ENV=production`

---

## 📋 **CHECKLIST PRE-DEPLOYMENT**

### **🏢 Información del Servidor**
- [ ] **IP del servidor**: ________________
- [ ] **Usuario SSH**: ________________
- [ ] **Contraseña/Clave**: ________________
- [ ] **Sistema operativo**: Ubuntu/CentOS/Debian
- [ ] **Acceso root**: Sí/No

### **🌐 Información del Dominio**
- [ ] **Dominio principal**: ________________
- [ ] **Subdominio API**: ________________ (ej: api.villamitre.com)
- [ ] **Acceso panel DNS**: Sí/No
- [ ] **Proveedor DNS**: Cloudflare/GoDaddy/Otro

### **🗄️ Base de Datos**
- [ ] **Nombre BD**: vmserver (recomendado)
- [ ] **Usuario BD**: vmserver_user (recomendado)
- [ ] **Contraseña BD**: ________________ (¡SEGURA!)

---

## 🛠️ **PASO 1: PREPARAR EL SERVIDOR**

### **Conectar al servidor**
```bash
# Conectar por SSH
ssh usuario@IP_DEL_SERVIDOR
# Ejemplo: ssh root@192.168.1.100
```

### **Instalar software necesario (Ubuntu/Debian)**
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache, PHP 8.2, MySQL
sudo apt install apache2 php8.2 php8.2-fpm mysql-server -y

# Instalar extensiones PHP OBLIGATORIAS
sudo apt install php8.2-mysql php8.2-mbstring php8.2-xml php8.2-zip \
                 php8.2-gd php8.2-curl php8.2-json php8.2-bcmath \
                 php8.2-tokenizer php8.2-ctype php8.2-fileinfo -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verificar instalaciones
php --version
composer --version
mysql --version
```

---

## 🗄️ **PASO 2: CONFIGURAR BASE DE DATOS**

```bash
# Entrar a MySQL como root
sudo mysql -u root

# Crear base de datos y usuario
CREATE DATABASE vmserver CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vmserver_user'@'localhost' IDENTIFIED BY 'TU_PASSWORD_SUPER_SEGURO';
GRANT ALL PRIVILEGES ON vmserver.* TO 'vmserver_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**⚠️ IMPORTANTE**: Cambia `TU_PASSWORD_SUPER_SEGURO` por una contraseña real y fuerte.

---

## 📁 **PASO 3: SUBIR EL CÓDIGO**

### **Opción A: Git (Recomendado)**
```bash
cd /var/www/
sudo git clone https://github.com/tu-usuario/vmServer.git
sudo mv vmServer villamitre-api
```

### **Opción B: FTP/SCP**
```bash
# Crear directorio
sudo mkdir /var/www/villamitre-api

# Subir archivos via SCP desde tu PC
scp -r ./vmServer/* usuario@IP_SERVIDOR:/var/www/villamitre-api/
```

### **Configurar permisos**
```bash
# Cambiar propietario a Apache
sudo chown -R www-data:www-data /var/www/villamitre-api

# Permisos generales
sudo chmod -R 755 /var/www/villamitre-api

# Permisos especiales para Laravel
sudo chmod -R 775 /var/www/villamitre-api/storage
sudo chmod -R 775 /var/www/villamitre-api/bootstrap/cache
```

---

## ⚙️ **PASO 4: CONFIGURAR VARIABLES DE ENTORNO**

```bash
cd /var/www/villamitre-api

# Copiar archivo de ejemplo
sudo cp .env.example .env

# Editar configuración
sudo nano .env
```

**Configuración .env para PRODUCCIÓN:**
```env
# APLICACIÓN
APP_NAME="Villa Mitre API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.villamitre.com  # ⚠️ CAMBIAR POR TU DOMINIO

# BASE DE DATOS
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=vmserver
DB_USERNAME=vmserver_user
DB_PASSWORD=TU_PASSWORD_SUPER_SEGURO  # ⚠️ LA MISMA DEL PASO 2

# API EXTERNA (NO CAMBIAR - SON DATOS REALES)
SOCIOS_API_BASE=https://clubvillamitre.com/api_back_socios
SOCIOS_API_LOGIN=surtek
SOCIOS_API_TOKEN=4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
SOCIOS_IMG_BASE=https://clubvillamitre.com/images/socios

# CACHE (USAR FILE EN PRODUCCIÓN SIMPLE)
CACHE_STORE=file
CACHE_USER_TTL=3600

# LOGS
LOG_CHANNEL=stack
LOG_LEVEL=error  # Solo errores en producción
```

---

## 🌐 **PASO 5: CONFIGURAR DOMINIO Y DNS**

### **5.1 Configurar DNS**
En tu panel de control del dominio (Cloudflare, GoDaddy, etc.):

```
Tipo: A
Nombre: api (o @)
Valor: IP_DE_TU_SERVIDOR
TTL: 300 (5 minutos)
```

### **5.2 Configurar Apache Virtual Host**
```bash
# Crear configuración del sitio
sudo nano /etc/apache2/sites-available/villamitre-api.conf
```

**Contenido del archivo:**
```apache
<VirtualHost *:80>
    ServerName api.villamitre.com  # ⚠️ CAMBIAR POR TU DOMINIO
    ServerAlias www.api.villamitre.com
    DocumentRoot /var/www/villamitre-api/public
    
    <Directory /var/www/villamitre-api/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/villamitre_error.log
    CustomLog ${APACHE_LOG_DIR}/villamitre_access.log combined
</VirtualHost>
```

### **5.3 Activar sitio**
```bash
# Habilitar sitio y módulos necesarios
sudo a2ensite villamitre-api.conf
sudo a2enmod rewrite
sudo a2dissite 000-default.conf

# Reiniciar Apache
sudo systemctl restart apache2
```

---

## 🔧 **PASO 6: INSTALAR Y CONFIGURAR LARAVEL**

```bash
cd /var/www/villamitre-api

# Instalar dependencias de producción
sudo -u www-data composer install --no-dev --optimize-autoloader

# Generar clave de aplicación
sudo -u www-data php artisan key:generate

# Ejecutar migraciones
sudo -u www-data php artisan migrate --force

# Optimizar para producción
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Crear enlace para storage
sudo -u www-data php artisan storage:link
```

---

## 🔒 **PASO 7: CONFIGURAR SSL/HTTPS (OBLIGATORIO)**

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtener certificado SSL GRATIS
sudo certbot --apache -d api.villamitre.com

# Verificar renovación automática
sudo certbot renew --dry-run
```

**⚠️ IMPORTANTE**: El SSL es OBLIGATORIO para APIs en producción.

---

## 🧪 **PASO 8: TESTING POST-DEPLOYMENT**

### **8.1 Verificar que el sitio carga**
```bash
# Test básico
curl -I https://api.villamitre.com

# Debería devolver: HTTP/2 200
```

### **8.2 Test de API**
```bash
# Test de login (desde el servidor)
curl -X POST https://api.villamitre.com/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"dni":"59964604","password":"123456789"}'

# Debería devolver JSON con token
```

### **8.3 Verificar logs**
```bash
# Ver logs de Laravel
tail -f /var/www/villamitre-api/storage/logs/laravel.log

# Ver logs de Apache
tail -f /var/log/apache2/villamitre_error.log
```

---

## 🚨 **TROUBLESHOOTING - PROBLEMAS COMUNES**

### **"Error 500 - Internal Server Error"**
```bash
# 1. Verificar permisos
sudo chown -R www-data:www-data /var/www/villamitre-api
sudo chmod -R 775 /var/www/villamitre-api/storage
sudo chmod -R 775 /var/www/villamitre-api/bootstrap/cache

# 2. Ver logs específicos
tail -f /var/www/villamitre-api/storage/logs/laravel.log
```

### **"No se conecta a la base de datos"**
```bash
# 1. Verificar que MySQL esté corriendo
sudo systemctl status mysql

# 2. Test de conexión manual
mysql -u vmserver_user -p -h localhost vmserver

# 3. Verificar credenciales en .env
cat /var/www/villamitre-api/.env | grep DB_
```

### **"El dominio no resuelve"**
```bash
# 1. Verificar DNS
nslookup api.villamitre.com

# 2. Verificar configuración Apache
sudo apache2ctl -S

# 3. Verificar que Apache esté corriendo
sudo systemctl status apache2
```

### **"SSL no funciona"**
```bash
# 1. Verificar certificado
sudo certbot certificates

# 2. Renovar si es necesario
sudo certbot renew

# 3. Reiniciar Apache
sudo systemctl restart apache2
```

---

## 📋 **CHECKLIST FINAL**

- [ ] ✅ Servidor configurado con Apache + PHP + MySQL
- [ ] ✅ Base de datos creada y usuario configurado
- [ ] ✅ Código subido a `/var/www/villamitre-api/`
- [ ] ✅ Permisos configurados correctamente
- [ ] ✅ Archivo `.env` configurado para producción
- [ ] ✅ DNS apuntando a la IP del servidor
- [ ] ✅ Virtual Host de Apache configurado
- [ ] ✅ Laravel instalado y optimizado
- [ ] ✅ SSL/HTTPS funcionando
- [ ] ✅ API respondiendo correctamente
- [ ] ✅ Logs sin errores críticos

---

## 🎉 **¡FELICITACIONES!**

Si llegaste hasta aquí y todos los checks están ✅, tu API está **LISTA PARA PRODUCCIÓN**.

### **URLs importantes:**
- **API Base**: `https://api.villamitre.com/api/`
- **Login**: `https://api.villamitre.com/api/auth/login`
- **Register**: `https://api.villamitre.com/api/auth/register`

### **Para el equipo de desarrollo:**
La API está lista para ser consumida por la aplicación móvil. Todos los endpoints documentados en `docs/api/mobile-contracts.md` están funcionando.

---

## 📞 **¿NECESITAS AYUDA?**

Si algo no funciona:

1. **Revisa los logs**: `tail -f /var/www/villamitre-api/storage/logs/laravel.log`
2. **Verifica el troubleshooting** de esta guía
3. **Contacta al equipo de desarrollo** con los logs específicos

**¡El deployment está completo y funcionando!** 🚀
