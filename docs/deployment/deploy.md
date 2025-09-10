# 🚀 Guía de Deploy - VM Server

## 📋 Configuración Actual (Desarrollo/Testing)

### Credenciales de API de Terceros
```bash
SOCIOS_API_BASE=https://clubvillamitre.com/api_back_socios
SOCIOS_API_LOGIN=surtek
SOCIOS_API_TOKEN=4fd8fa5840fc5e71d27e46f858f18b4c0cafe220
SOCIOS_IMG_BASE=https://clubvillamitre.com/images/socios
```

### Base de Datos (Docker)
```bash
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=vmserver
DB_USERNAME=vmserver
DB_PASSWORD=password
```

## 🔒 Para Deploy en Producción

### 1. Configurar Repositorio Privado
- Cambiar repositorio a privado
- Agregar colaborador para deploy
- Restaurar `.env` en `.gitignore`

### 2. Variables de Entorno Seguras
Crear `.env.production` con:
```bash
# Usar variables de entorno del servidor
SOCIOS_API_TOKEN=${SOCIOS_API_TOKEN}
DB_PASSWORD=${DB_PASSWORD}
APP_KEY=${APP_KEY}
```

### 3. Comandos de Deploy
```bash
# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate --force

# Limpiar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🧪 Testing con API Real

### Comando de Diagnóstico
```bash
php artisan socios:test 59964604
```

### Endpoints Principales
- `POST /api/auth/register` - Registro con promoción automática
- `POST /api/auth/login` - Login unificado
- `GET /api/users` - Gestión de usuarios
- `POST /api/promotions/promote` - Promoción manual

## 📊 Arquitectura Implementada

### Registro Unificado
1. Usuario se registra → Crear como "local"
2. Validación automática con API de terceros
3. Si existe → Promover a "api" + sincronizar datos
4. Si no existe → Mantener como "local"

### Cache y Performance
- Cache multinivel (Redis/Files)
- Circuit breaker para fallos de API
- Cache negativo para DNIs no encontrados
- Descarga de avatar asíncrona

### Seguridad
- Contraseña local para todos los usuarios
- Tokens JWT para autenticación
- Validación completa con Form Requests
- Logging de auditoría
