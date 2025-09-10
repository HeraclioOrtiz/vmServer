@echo off
echo 🚀 Configurando VM Server con Docker...

REM Verificar que Docker esté instalado
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker no está instalado. Por favor instala Docker Desktop primero.
    pause
    exit /b 1
)

docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Compose no está instalado. Por favor instala Docker Compose primero.
    pause
    exit /b 1
)

REM Copiar archivo de entorno
if not exist .env (
    echo 📋 Copiando archivo de configuración...
    copy .env.docker .env
    echo ✅ Archivo .env creado desde .env.docker
) else (
    echo ⚠️  El archivo .env ya existe. Revisa la configuración manualmente.
)

REM Construir e iniciar contenedores
echo 🏗️  Construyendo contenedores...
docker-compose up -d --build

REM Esperar a que la base de datos esté lista
echo ⏳ Esperando que la base de datos esté lista...
timeout /t 30 /nobreak >nul

REM Generar clave de aplicación
echo 🔑 Generando clave de aplicación...
docker-compose exec app php artisan key:generate

REM Ejecutar migraciones
echo 🗄️  Ejecutando migraciones...
docker-compose exec app php artisan migrate --force

REM Instalar dependencias
echo 📦 Instalando dependencias...
docker-compose exec app composer install

REM Configurar permisos
echo 🔐 Configurando permisos...
docker-compose exec app chown -R www:www /var/www/storage
docker-compose exec app chown -R www:www /var/www/bootstrap/cache

REM Limpiar cache
echo 🧹 Limpiando cache...
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

echo.
echo ✅ ¡Setup completado!
echo.
echo 🌐 Aplicación disponible en: http://localhost:8000
echo 🗄️  phpMyAdmin disponible en: http://localhost:8080
echo    Usuario: root ^| Contraseña: root
echo.
echo 📋 Comandos útiles:
echo    docker-compose up -d          # Iniciar servicios
echo    docker-compose down           # Detener servicios
echo    docker-compose logs app       # Ver logs de la aplicación
echo    docker-compose exec app bash  # Acceder al contenedor
echo.
pause
