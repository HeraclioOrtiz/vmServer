#!/bin/bash

# ============================================
# SCRIPT DE DEPLOYMENT INICIAL - VILLA MITRE
# ============================================
# Este script configura la aplicación en producción por primera vez
# 
# Uso: ./deploy-inicial.sh
#
# Prerequisitos:
# - Repositorio clonado
# - Composer instalado
# - Base de datos creada
# - .env configurado
# ============================================

set -e  # Detener si hay errores

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🚀 DEPLOYMENT INICIAL - VILLA MITRE API"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo 'artisan'"
    echo "   Asegúrate de estar en el directorio raíz del proyecto Laravel"
    exit 1
fi

echo "📂 Directorio de trabajo: $(pwd)"
echo ""

# 1. Verificar que existe .env
echo "1️⃣  Verificando archivo .env..."
if [ ! -f ".env" ]; then
    echo "❌ Error: No existe el archivo .env"
    echo "   Crea el archivo .env desde .env.production.example:"
    echo "   cp .env.production.example .env"
    exit 1
fi
echo "✅ Archivo .env encontrado"
echo ""

# 2. Instalar dependencias
echo "2️⃣  Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "✅ Dependencias instaladas"
echo ""

# 3. Generar APP_KEY si no existe
echo "3️⃣  Verificando APP_KEY..."
if ! grep -q "APP_KEY=base64:" .env; then
    echo "   Generando APP_KEY..."
    php artisan key:generate --force
    echo "✅ APP_KEY generada"
else
    echo "✅ APP_KEY ya existe"
fi
echo ""

# 4. Ejecutar migraciones
echo "4️⃣  Ejecutando migraciones de base de datos..."
php artisan migrate --force
echo "✅ Migraciones completadas"
echo ""

# 5. Ejecutar seeder de producción
echo "5️⃣  Creando usuarios iniciales..."
php artisan db:seed --class=ProductionSeeder --force
echo "✅ Usuarios creados"
echo ""

# 6. Limpiar cache previo (si existe)
echo "6️⃣  Limpiando cache anterior..."
php artisan optimize:clear 2>/dev/null || true
echo "✅ Cache limpiado"
echo ""

# 7. Optimizar aplicación
echo "7️⃣  Optimizando aplicación para producción..."
php artisan config:cache
echo "   ✓ Config cacheado"
php artisan route:cache
echo "   ✓ Routes cacheadas"
php artisan view:cache
echo "   ✓ Views cacheadas"
php artisan optimize
echo "   ✓ Autoloader optimizado"
echo "✅ Optimizaciones aplicadas"
echo ""

# 8. Configurar permisos
echo "8️⃣  Configurando permisos de archivos..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
echo "✅ Permisos configurados"
echo ""

# 9. Generar JWT secret si es necesario
echo "9️⃣  Verificando JWT secret..."
if command -v php artisan jwt:secret &> /dev/null; then
    if ! grep -q "JWT_SECRET=" .env || [ -z "$(grep JWT_SECRET= .env | cut -d '=' -f2)" ]; then
        echo "   Generando JWT secret..."
        php artisan jwt:secret --force 2>/dev/null || echo "   ⚠️  jwt:secret no disponible (instalar tymon/jwt-auth si es necesario)"
    else
        echo "✅ JWT secret ya existe"
    fi
else
    echo "⚠️  Comando jwt:secret no disponible"
fi
echo ""

# Resumen final
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 DEPLOYMENT INICIAL COMPLETADO"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 CREDENCIALES DE ACCESO:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "👑 ADMINISTRADOR:"
echo "   Email: admin@villamitre.com"
echo "   DNI: 11111111"
echo "   Password: admin123"
echo ""
echo "👨‍🏫 PROFESOR:"
echo "   Email: profesor@villamitre.com"
echo "   DNI: 22222222"
echo "   Password: profesor123"
echo ""
echo "👤 ESTUDIANTE DE PRUEBA:"
echo "   Email: maria.garcia@villamitre.com"
echo "   DNI: 55555555"
echo "   Password: maria123"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "⚠️  IMPORTANTE:"
echo "   - Cambiar las contraseñas después del primer login"
echo "   - Verificar que APP_DEBUG=false en .env"
echo "   - Configurar servicio de email si es necesario"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📌 PRÓXIMOS PASOS:"
echo "   1. Verificar que el sitio esté accesible"
echo "   2. Probar login con las credenciales de arriba"
echo "   3. Crear ejercicios desde el panel admin"
echo "   4. Crear plantillas de entrenamiento"
echo "   5. Registrar estudiantes reales"
echo ""
echo "🔧 COMANDOS ÚTILES:"
echo "   - Ver logs: tail -f storage/logs/laravel.log"
echo "   - Limpiar cache: php artisan optimize:clear"
echo "   - Asignar todos los estudiantes al profesor:"
echo "     php artisan students:assign-to-professor"
echo ""
echo "✅ ¡Listo para producción!"
echo ""
