# 🛠️ CONFIGURACIÓN LOCAL - VILLA MITRE SERVER

## 📋 Prerrequisitos Instalados
- ✅ XAMPP con PHP 8.2+
- ✅ Apache Web Server
- ✅ MySQL Database

## 🚀 Comandos de Configuración Local

### **1. Instalar Composer**
```powershell
# Opción A: Usar ruta completa de XAMPP
C:\xampp\php\php.exe -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
C:\xampp\php\php.exe composer-setup.php
C:\xampp\php\php.exe -r "unlink('composer-setup.php');"
move composer.phar C:\xampp\php\composer.phar

# Opción B: Usar XAMPP Shell (más fácil)
# 1. Abrir XAMPP Control Panel
# 2. Click "Shell"
# 3. Ejecutar comandos normales de PHP
```

### **2. Configurar PATH (Opcional)**
```powershell
# Agregar PHP al PATH permanentemente
$env:PATH += ";C:\xampp\php"
[Environment]::SetEnvironmentVariable("PATH", $env:PATH, "User")
```

### **3. Verificar Instalación**
```powershell
# Verificar PHP
C:\xampp\php\php.exe --version

# Verificar Composer
C:\xampp\php\php.exe composer.phar --version
```

### **4. Configurar Base de Datos**
```sql
-- Abrir http://localhost/phpmyadmin
-- Crear base de datos:
CREATE DATABASE vmserver CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **5. Configurar Proyecto Laravel**
```powershell
# Navegar al proyecto
cd "F:\Laburo\Programacion\Laburo-Javi\VILLAMITRE\vmServer"

# Instalar dependencias
C:\xampp\php\php.exe composer.phar install

# Generar key de aplicación
C:\xampp\php\php.exe artisan key:generate

# Ejecutar migraciones
C:\xampp\php\php.exe artisan migrate

# Crear enlace de storage
C:\xampp\php\php.exe artisan storage:link
```

### **6. Iniciar Servidor Local**
```powershell
# Opción A: Servidor integrado Laravel
C:\xampp\php\php.exe artisan serve
# Acceder: http://localhost:8000

# Opción B: Apache Virtual Host
# Ver: docs/installation/APACHE-VHOST.md
```

## 🧪 Testing Local

### **Scripts de Verificación**
```powershell
# Test API integration
C:\xampp\php\php.exe test_api_integration.php

# Test registration flow
C:\xampp\php\php.exe test_registration_flow.php

# Test login flow
C:\xampp\php\php.exe test_login_flow.php

# Check database images
C:\xampp\php\php.exe check_database_images.php
```

### **URLs de Testing**
- **API Base:** http://localhost:8000/api
- **phpMyAdmin:** http://localhost/phpmyadmin
- **XAMPP Dashboard:** http://localhost/dashboard

## ⚠️ Troubleshooting

### **Error: 'php' no reconocido**
```powershell
# Usar ruta completa
C:\xampp\php\php.exe [comando]

# O usar XAMPP Shell
```

### **Error: Base de datos**
```powershell
# Verificar servicios XAMPP
# Apache: Started
# MySQL: Started
```

### **Error: Permisos**
```powershell
# Verificar permisos storage/
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

---
**Próximo paso:** [Testing Guide](../testing/TESTING-GUIDE.md)
