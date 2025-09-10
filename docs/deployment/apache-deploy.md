# 🚀 GUÍA DE DEPLOYMENT - VILLA MITRE SERVER

**Proyecto:** Club Villa Mitre API Backend  
**Stack:** Laravel 11 + Apache + MySQL + Redis  
**Fecha:** 2025-09-09

## 📋 Información del Proyecto

### **Descripción**
API backend para la aplicación móvil del Club Villa Mitre que maneja:
- Autenticación unificada (local + API terceros)
- Gestión de usuarios y socios
- Integración con API externa del club
- Descarga y gestión de imágenes de perfil
- Sistema de cache multinivel

### **Características Principales**
- ✅ **Registro automático** con promoción a usuario API
- ✅ **Descarga síncrona** de imágenes de perfil
- ✅ **Cache inteligente** con circuit breaker
- ✅ **Integración API terceros** con validación estricta
- ✅ **Logging detallado** para debugging
- ✅ **Testing completo** con scripts automatizados

## 🛠️ Requisitos del Servidor

### **Software Requerido**
```
- PHP 8.2 o superior
- Apache 2.4 o superior
- MySQL 8.0 o superior
- Composer 2.x
- Redis 6.x (opcional, puede usar file cache)
```

### **Extensiones PHP Obligatorias**
```
php-mysql
php-mbstring
php-xml
php-zip
php-gd
php-curl
php-json
php-bcmath
php-tokenizer
php-ctype
php-fileinfo
php-redis (opcional)
```

### **Configuración PHP Recomendada**
```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_vars = 3000
```

## 📁 Estructura del Proyecto

```
vmServer/
├── app/                    # Lógica de la aplicación
│   ├── Http/Controllers/   # Controladores API
│   ├── Services/          # Servicios (Auth, SociosApi)
│   ├── Models/            # Modelos Eloquent
│   └── Resources/         # API Resources
├── config/                # Configuraciones Laravel
├── database/              # Migraciones y seeders
├── public/                # Punto de entrada web
├── storage/               # Archivos y logs
├── apache/                # Configuración Apache
├── test_*.php            # Scripts de testing
└── .env                  # Variables de entorno
```

## 🔧 Proceso de Instalación

### **1. Preparación del Servidor**

#### **Ubuntu/Debian:**
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache, PHP y MySQL
sudo apt install apache2 php8.2 php8.2-fpm mysql-server -y

# Instalar extensiones PHP
sudo apt install php8.2-mysql php8.2-mbstring php8.2-xml php8.2-zip \
                 php8.2-gd php8.2-curl php8.2-json php8.2-bcmath \
                 php8.2-tokenizer php8.2-ctype php8.2-fileinfo -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Redis (opcional)
sudo apt install redis-server php8.2-redis -y
```

#### **CentOS/RHEL:**
```bash
# Habilitar repositorios
sudo dnf install epel-release -y
sudo dnf module enable php:8.2 -y

# Instalar paquetes
sudo dnf install httpd php php-mysqlnd php-mbstring php-xml php-zip \
                 php-gd php-curl php-json php-bcmath mysql-server -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### **2. Configuración de Base de Datos**

```sql
-- Conectar como root
mysql -u root -p

-- Crear base de datos y usuario
CREATE DATABASE vmserver CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vmserver'@'localhost' IDENTIFIED BY 'TU_PASSWORD_SEGURO';
GRANT ALL PRIVILEGES ON vmserver.* TO 'vmserver'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **3. Deployment del Código**

```bash
# Clonar o copiar el proyecto
cd /var/www/
sudo git clone [REPO_URL] vmserver
# O copiar archivos via FTP/SCP

# Establecer permisos
sudo chown -R www-data:www-data /var/www/vmserver
sudo chmod -R 755 /var/www/vmserver
sudo chmod -R 775 /var/www/vmserver/storage
sudo chmod -R 775 /var/www/vmserver/bootstrap/cache

# Instalar dependencias
cd /var/www/vmserver
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### **4. Configuración de Variables de Entorno**

```bash
# Copiar y editar .env
sudo cp .env.example .env
sudo nano .env
```

**Configuración .env crítica:**
```env
APP_NAME="Villa Mitre API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=vmserver
DB_USERNAME=vmserver
DB_PASSWORD=TU_PASSWORD_SEGURO

# API de terceros (CRÍTICO)
SOCIOS_API_BASE=https://clubvillamitre.com/api_back_socios
SOCIOS_API_LOGIN=surtek
SOCIOS_API_TOKEN=4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
SOCIOS_IMG_BASE=https://clubvillamitre.com/images/socios

# Cache (Redis opcional)
CACHE_STORE=file
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Configuración de imágenes
SOCIOS_IMG_TIMEOUT=3
SOCIOS_IMG_VERIFY_SSL=true
```

### **5. Configuración de Apache**

```bash
# Copiar configuración del virtual host
sudo cp apache/vmserver.conf /etc/apache2/sites-available/

# Editar paths en el archivo
sudo nano /etc/apache2/sites-available/vmserver.conf
# Cambiar: F:/Laburo/... por /var/www/vmserver

# Habilitar sitio y módulos
sudo a2ensite vmserver.conf
sudo a2enmod rewrite ssl headers expires
sudo a2dissite 000-default.conf

# Reiniciar Apache
sudo systemctl restart apache2
```

### **6. Inicialización de Laravel**

```bash
cd /var/www/vmserver

# Generar key de aplicación
sudo -u www-data php artisan key:generate

# Ejecutar migraciones
sudo -u www-data php artisan migrate --force

# Optimizar para producción
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Crear enlace simbólico para storage
sudo -u www-data php artisan storage:link
```

## 🧪 Testing Post-Deployment

### **1. Verificación Básica**
```bash
# Test de conectividad
curl -I https://tudominio.com/api/health

# Test de base de datos
php artisan migrate:status

# Test de cache
php artisan cache:clear
```

### **2. Testing de API**
```bash
# Ejecutar scripts de testing
php test_api_integration.php
php test_registration_flow.php
php test_login_flow.php
```

### **3. Verificación de Logs**
```bash
# Logs de Laravel
tail -f storage/logs/laravel.log

# Logs de Apache
tail -f /var/log/apache2/vmserver_error.log
```

## 🔒 Configuración de Seguridad

### **1. SSL/HTTPS (Obligatorio para producción)**
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtener certificado SSL
sudo certbot --apache -d tudominio.com -d www.tudominio.com

# Verificar renovación automática
sudo certbot renew --dry-run
```

### **2. Firewall**
```bash
# Configurar UFW
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### **3. Permisos de Archivos**
```bash
# Asegurar permisos correctos
find /var/www/vmserver -type f -exec chmod 644 {} \;
find /var/www/vmserver -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/vmserver/storage
chmod -R 775 /var/www/vmserver/bootstrap/cache
```

## 📊 Monitoreo y Mantenimiento

### **1. Logs Importantes**
- `storage/logs/laravel.log` - Logs de aplicación
- `/var/log/apache2/vmserver_error.log` - Errores Apache
- `/var/log/mysql/error.log` - Errores MySQL

### **2. Comandos de Mantenimiento**
```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Optimizar para producción
php artisan optimize

# Backup de base de datos
mysqldump -u vmserver -p vmserver > backup_$(date +%Y%m%d).sql
```

### **3. Actualizaciones**
```bash
# Actualizar dependencias
composer update --no-dev

# Ejecutar migraciones
php artisan migrate --force

# Re-optimizar
php artisan optimize
```

## 🚨 Troubleshooting

### **Errores Comunes**

#### **500 Internal Server Error**
```bash
# Verificar permisos
sudo chown -R www-data:www-data /var/www/vmserver
sudo chmod -R 775 storage bootstrap/cache

# Verificar logs
tail -f storage/logs/laravel.log
```

#### **Database Connection Failed**
```bash
# Verificar credenciales en .env
# Verificar que MySQL esté corriendo
sudo systemctl status mysql

# Test de conexión
mysql -u vmserver -p -h localhost vmserver
```

#### **API de Terceros No Responde**
```bash
# Verificar conectividad
curl -H "Authorization: 4fd8fa5840fc5e71d27e46f858f18b4c0cafe220" \
     -H "Login: surtek" \
     -X POST https://clubvillamitre.com/api_back_socios/get_socio \
     -d "dni=59964604"
```

## 📞 Soporte

### **Información de Contacto**
- **Desarrollador:** [Tu información]
- **Documentación:** Ver archivos MD en el proyecto
- **Testing:** Usar scripts test_*.php
- **Logs:** storage/logs/laravel.log

### **Recursos Adicionales**
- `API-MOBILE-CONTRACTS.md` - Contratos de API
- `TESTING-GUIDE.md` - Guía de testing
- `POSTMAN-TESTING-GUIDE.md` - Testing con Postman
- `MIGRATION-DOCKER-TO-APACHE.md` - Historial de migración
- `APACHE-DEPLOY.md` - Documentación de despliegue en Apache

---

**🎉 ¡Deployment completado! El servidor está listo para producción.**
