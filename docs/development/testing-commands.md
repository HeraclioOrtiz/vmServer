# 🧪 COMANDOS PARA TESTING LOCAL - VILLA MITRE SERVER

## 📋 Configuración Inicial (Ejecutar una vez)

### **1. Instalar Composer**
```powershell
# Opción A: Ruta completa XAMPP
C:\xampp\php\php.exe -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
C:\xampp\php\php.exe composer-setup.php
C:\xampp\php\php.exe -r "unlink('composer-setup.php');"
move composer.phar C:\xampp\php\composer.phar

# Opción B: XAMPP Shell (recomendado)
# 1. Abrir XAMPP Control Panel → Shell
# 2. Ejecutar: php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
# 3. Ejecutar: php composer-setup.php
# 4. Ejecutar: php -r "unlink('composer-setup.php');"
```

### **2. Verificar Instalación**
```powershell
# Verificar PHP
C:\xampp\php\php.exe --version

# Verificar Composer
C:\xampp\php\php.exe composer.phar --version
```

### **3. Crear Base de Datos**
```
1. Abrir XAMPP Control Panel
2. Start → Apache
3. Start → MySQL
4. Ir a: http://localhost/phpmyadmin
5. Crear base de datos: vmserver
6. Collation: utf8mb4_unicode_ci
```

## 🚀 Configuración del Proyecto

### **4. Instalar Dependencias Laravel**
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

### **5. Configurar Variables de Entorno**
```powershell
# Verificar que .env tiene:
# DB_HOST=localhost
# DB_DATABASE=vmserver
# DB_USERNAME=root
# DB_PASSWORD= (vacío para XAMPP)
```

## 🧪 Testing del Proyecto

### **6. Iniciar Servidor Local**
```powershell
# Servidor integrado Laravel
C:\xampp\php\php.exe artisan serve

# Acceder en: http://localhost:8000
```

### **7. Scripts de Testing**
```powershell
# Test integración API terceros
C:\xampp\php\php.exe test_api_integration.php

# Test flujo de registro completo
C:\xampp\php\php.exe test_registration_flow.php

# Test flujo de login completo
C:\xampp\php\php.exe test_login_flow.php

# Verificar imágenes en base de datos
C:\xampp\php\php.exe check_database_images.php
```

### **8. Testing Manual API**
```powershell
# Test endpoint de salud
curl http://localhost:8000/api/health

# Test registro de usuario
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d "{\"dni\":\"59964604\",\"name\":\"Test User\",\"email\":\"test@test.com\",\"password\":\"password\"}"

# Test login de usuario
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d "{\"dni\":\"59964604\",\"password\":\"password\"}"
```

## 🔧 Comandos de Mantenimiento

### **9. Limpiar Cache**
```powershell
# Limpiar cache de aplicación
C:\xampp\php\php.exe artisan cache:clear

# Limpiar cache de configuración
C:\xampp\php\php.exe artisan config:clear

# Limpiar cache de rutas
C:\xampp\php\php.exe artisan route:clear
```

### **10. Verificar Estado**
```powershell
# Ver migraciones ejecutadas
C:\xampp\php\php.exe artisan migrate:status

# Ver rutas disponibles
C:\xampp\php\php.exe artisan route:list

# Ver configuración actual
C:\xampp\php\php.exe artisan config:show database
```

## 🚨 Troubleshooting

### **Error: 'php' no reconocido**
```powershell
# Usar siempre ruta completa:
C:\xampp\php\php.exe [comando]

# O usar XAMPP Shell donde PHP está en PATH
```

### **Error: Base de datos**
```powershell
# Verificar servicios XAMPP están iniciados
# Verificar credenciales en .env
# Verificar que base de datos 'vmserver' existe
```

### **Error: Permisos**
```powershell
# En Windows, verificar que XAMPP tiene permisos
# Ejecutar XAMPP como Administrador si es necesario
```

## 📊 URLs de Testing

- **Aplicación:** http://localhost:8000
- **API Base:** http://localhost:8000/api
- **phpMyAdmin:** http://localhost/phpmyadmin
- **XAMPP Dashboard:** http://localhost/dashboard

## ✅ Checklist de Verificación

- [ ] XAMPP instalado y funcionando
- [ ] PHP y Composer funcionando
- [ ] Base de datos creada
- [ ] Dependencias instaladas (`composer install`)
- [ ] Key generada (`artisan key:generate`)
- [ ] Migraciones ejecutadas (`artisan migrate`)
- [ ] Servidor iniciado (`artisan serve`)
- [ ] Scripts de testing ejecutados sin errores
- [ ] API endpoints responden correctamente

---

**🎯 Una vez completados todos los pasos, el proyecto estará listo para testing local y deployment.**
