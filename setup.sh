#!/bin/bash

echo "🚀 Configurando VM Server con Docker..."

# Verificar que Docker esté instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker no está instalado. Por favor instala Docker Desktop primero."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose no está instalado. Por favor instala Docker Compose primero."
    exit 1
fi

# Copiar archivo de entorno
if [ ! -f .env ]; then
    echo "📋 Copiando archivo de configuración..."
    cp .env.docker .env
    echo "✅ Archivo .env creado desde .env.docker"
else
    echo "⚠️  El archivo .env ya existe. Revisa la configuración manualmente."
fi

# Generar clave de aplicación
echo "🔑 Generando clave de aplicación..."
docker-compose run --rm app php artisan key:generate

# Construir e iniciar contenedores
echo "🏗️  Construyendo contenedores..."
docker-compose up -d --build

# Esperar a que la base de datos esté lista
echo "⏳ Esperando que la base de datos esté lista..."
sleep 30

# Ejecutar migraciones
echo "🗄️  Ejecutando migraciones..."
docker-compose exec app php artisan migrate --force

# Instalar dependencias
echo "📦 Instalando dependencias..."
docker-compose exec app composer install

# Configurar permisos
echo "🔐 Configurando permisos..."
docker-compose exec app chown -R www:www /var/www/storage
docker-compose exec app chown -R www:www /var/www/bootstrap/cache

# Limpiar cache
echo "🧹 Limpiando cache..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

echo ""
echo "✅ ¡Setup completado!"
echo ""
echo "🌐 Aplicación disponible en: http://localhost:8000"
echo "🗄️  phpMyAdmin disponible en: http://localhost:8080"
echo "   Usuario: root | Contraseña: root"
echo ""
echo "📋 Comandos útiles:"
echo "   docker-compose up -d          # Iniciar servicios"
echo "   docker-compose down           # Detener servicios"
echo "   docker-compose logs app       # Ver logs de la aplicación"
echo "   docker-compose exec app bash  # Acceder al contenedor"
echo ""
