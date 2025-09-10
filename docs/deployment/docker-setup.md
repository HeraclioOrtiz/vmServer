# VM Server - Setup con Docker

Este proyecto incluye una configuración completa de Docker para desarrollo local sin necesidad de instalar PHP, Laravel, MySQL o Redis en tu sistema.

## 📋 Requisitos

- **Docker Desktop** (Windows/Mac) o **Docker Engine** (Linux)
- **Docker Compose** (incluido en Docker Desktop)

## 🚀 Instalación Rápida

### Windows
```bash
# Ejecutar el script de setup
setup.bat
```

### Linux/Mac
```bash
# Dar permisos de ejecución
chmod +x setup.sh

# Ejecutar el script de setup
./setup.sh
```

### Manual
```bash
# 1. Copiar configuración
cp .env.docker .env

# 2. Construir e iniciar contenedores
docker-compose up -d --build

# 3. Generar clave de aplicación
docker-compose exec app php artisan key:generate

# 4. Ejecutar migraciones
docker-compose exec app php artisan migrate --force

# 5. Instalar dependencias
docker-compose exec app composer install
```

## 🌐 Acceso a la Aplicación

- **API Laravel**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080 (usuario: `root`, contraseña: `root`)

## 📊 Servicios Incluidos

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| **app** | - | Laravel con PHP 8.2 |
| **webserver** | 8000 | Nginx |
| **db** | 3306 | MySQL 8.0 |
| **redis** | 6379 | Redis 7 |
| **phpmyadmin** | 8080 | Administrador de BD |

## 🛠️ Comandos Útiles

```bash
# Iniciar servicios
docker-compose up -d

# Detener servicios
docker-compose down

# Ver logs
docker-compose logs app
docker-compose logs -f app  # Seguir logs en tiempo real

# Acceder al contenedor de la aplicación
docker-compose exec app bash

# Ejecutar comandos de Laravel
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:list

# Ejecutar tests
docker-compose exec app php artisan test

# Instalar nuevas dependencias
docker-compose exec app composer require package/name
```

## 🧪 Testing de la API

### Endpoints Principales

**Autenticación:**
```bash
# Registro de usuario local
POST http://localhost:8000/api/auth/register
{
    "dni": "12345678",
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

# Login
POST http://localhost:8000/api/auth/login
{
    "dni": "12345678",
    "password": "password123"
}
```

**Gestión de Usuarios:**
```bash
# Listar usuarios (requiere token)
GET http://localhost:8000/api/users
Authorization: Bearer {token}

# Crear usuario (admin)
POST http://localhost:8000/api/users
Authorization: Bearer {token}
```

**Promociones:**
```bash
# Promover usuario local a API
POST http://localhost:8000/api/promotions/promote
Authorization: Bearer {token}
{
    "club_password": "password_del_club"
}
```

## 🔧 Configuración

### Variables de Entorno (.env)

Las principales configuraciones están en `.env`:

```env
# Base de datos
DB_HOST=db
DB_DATABASE=vmserver
DB_USERNAME=vmserver
DB_PASSWORD=password

# Cache (Redis)
CACHE_STORE=redis
REDIS_HOST=redis

# API Externa del Club
SOCIOS_API_BASE_URL=https://api.clubvillamitre.com
```

### Personalización

- **PHP**: Editar `docker/php/local.ini`
- **Nginx**: Editar `docker/nginx/default.conf`
- **MySQL**: Editar `docker/mysql/init.sql`

## 🐛 Troubleshooting

### Problemas Comunes

**Puerto ocupado:**
```bash
# Cambiar puertos en docker-compose.yml
ports:
  - "8001:80"  # Cambiar 8000 por 8001
```

**Permisos en Windows:**
```bash
# Ejecutar como administrador
docker-compose exec app chown -R www:www /var/www/storage
```

**Base de datos no conecta:**
```bash
# Reiniciar servicios
docker-compose down
docker-compose up -d
```

**Cache Redis no funciona:**
```bash
# Verificar conexión Redis
docker-compose exec app php artisan tinker
>>> Cache::store('redis')->put('test', 'value');
>>> Cache::store('redis')->get('test');
```

### Logs de Debug

```bash
# Ver todos los logs
docker-compose logs

# Logs específicos
docker-compose logs app
docker-compose logs db
docker-compose logs redis
```

## 🔄 Actualización

```bash
# Actualizar código y reconstruir
git pull
docker-compose down
docker-compose up -d --build

# Ejecutar nuevas migraciones
docker-compose exec app php artisan migrate
```

## 🧹 Limpieza

```bash
# Detener y eliminar contenedores
docker-compose down

# Eliminar volúmenes (¡CUIDADO! Borra la BD)
docker-compose down -v

# Eliminar imágenes
docker-compose down --rmi all
```
