# 🛠️ CONFIGURACIÓN DESARROLLO LOCAL - VILLA MITRE SERVER

**Estado Actual:** ❌ **NO hay stack de desarrollo instalado**  
**Acción Requerida:** Instalar XAMPP o WAMP para desarrollo local

## 📋 Verificación Realizada

### ❌ **Componentes Faltantes**
- **PHP:** No encontrado en PATH del sistema
- **Apache:** No instalado localmente  
- **MySQL:** No verificado (requiere PHP primero)
- **Composer:** No encontrado
- **XAMPP:** No instalado en ubicaciones estándar

## 🚀 Instalación Recomendada: XAMPP

### **1. Descargar XAMPP**
- **URL:** https://www.apachefriends.org/download.html
- **Versión:** XAMPP for Windows con PHP 8.2+
- **Tamaño:** ~150MB

### **2. Instalación XAMPP**
```
1. Ejecutar instalador como Administrador
2. Seleccionar componentes:
   ✅ Apache
   ✅ MySQL  
   ✅ PHP
   ✅ phpMyAdmin
   ❌ Mercury (no necesario)
   ❌ Tomcat (no necesario)
3. Instalar en: C:\xampp
4. Permitir acceso en Firewall de Windows
```

### **3. Configuración Post-Instalación**

#### **Agregar PHP al PATH:**
```powershell
# Abrir PowerShell como Administrador
$env:PATH += ";C:\xampp\php"
[Environment]::SetEnvironmentVariable("PATH", $env:PATH, "Machine")
```

#### **Instalar Composer:**
```powershell
# Descargar e instalar Composer
Invoke-WebRequest -Uri "https://getcomposer.org/Composer-Setup.exe" -OutFile "composer-setup.exe"
.\composer-setup.exe
```

### **4. Configuración del Proyecto**

#### **Crear Base de Datos:**
```sql
-- Abrir http://localhost/phpmyadmin
-- Crear nueva base de datos: vmserver
-- Collation: utf8mb4_unicode_ci
```

#### **Configurar Virtual Host:**
```apache
# Editar: C:\xampp\apache\conf\extra\httpd-vhosts.conf
# Agregar al final:

<VirtualHost *:80>
    ServerName vmserver.local
    DocumentRoot "F:/Laburo/Programacion/Laburo-Javi/VILLAMITRE/vmServer/public"
    <Directory "F:/Laburo/Programacion/Laburo-Javi/VILLAMITRE/vmServer/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### **Configurar hosts:**
```
# Editar: C:\Windows\System32\drivers\etc\hosts
# Agregar línea:
127.0.0.1    vmserver.local
```

#### **Habilitar mod_rewrite:**
```apache
# Editar: C:\xampp\apache\conf\httpd.conf
# Descomentar línea:
LoadModule rewrite_module modules/mod_rewrite.so
```

### **5. Inicializar Proyecto Laravel**

```powershell
# Navegar al proyecto
cd "F:\Laburo\Programacion\Laburo-Javi\VILLAMITRE\vmServer"

# Instalar dependencias
composer install

# Generar key de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Crear enlace de storage
php artisan storage:link
```

## 🧪 Verificación de Instalación

### **Comandos de Verificación:**
```powershell
# Verificar PHP
php --version

# Verificar Composer
composer --version

# Verificar extensiones PHP requeridas
php -m | findstr -i "mysql mbstring xml zip gd curl json"

# Test del servidor
php artisan serve
```

### **URLs de Testing:**
- **Aplicación:** http://vmserver.local
- **phpMyAdmin:** http://localhost/phpmyadmin
- **XAMPP Control:** http://localhost/dashboard

## ⚡ Alternativa Rápida: Servidor Integrado

Si solo necesitas testing rápido:

```powershell
# Instalar solo PHP y Composer
# Usar servidor integrado de Laravel
php artisan serve --host=0.0.0.0 --port=8000

# Acceder en: http://localhost:8000
```

## 🔧 Configuración .env para Local

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://vmserver.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vmserver
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=file
REDIS_HOST=127.0.0.1
```

## 📊 Próximos Pasos

1. **Instalar XAMPP** (30 minutos)
2. **Configurar Virtual Host** (10 minutos)
3. **Instalar dependencias** con `composer install` (5 minutos)
4. **Ejecutar migraciones** con `php artisan migrate` (2 minutos)
5. **Testing** con scripts existentes (5 minutos)

## 🚨 Notas Importantes

- **Firewall:** XAMPP puede requerir permisos de firewall
- **Antivirus:** Algunos antivirus bloquean Apache/MySQL
- **Puertos:** Verificar que puertos 80 y 3306 estén libres
- **Permisos:** Ejecutar XAMPP como Administrador si hay problemas

---

**🎯 Una vez instalado XAMPP, el proyecto estará listo para desarrollo local.**
