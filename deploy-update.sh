#!/bin/bash

# ============================================
# SCRIPT DE ACTUALIZACIÓN - VILLA MITRE
# ============================================
# Este script actualiza la aplicación con los últimos cambios de GitHub
# 
# Uso: ./deploy-update.sh
#
# Prerequisitos:
# - Aplicación ya desplegada inicialmente
# - Acceso SSH al servidor
# - Git configurado
# ============================================

set -e  # Detener si hay errores

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔄 ACTUALIZACIÓN - VILLA MITRE API"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo 'artisan'"
    echo "   Asegúrate de estar en el directorio raíz del proyecto Laravel"
    exit 1
fi

echo "📂 Directorio: $(pwd)"
echo "🌿 Branch actual: $(git branch --show-current)"
echo ""

# 1. Activar modo mantenimiento
echo "1️⃣  Activando modo mantenimiento..."
php artisan down --retry=60 --secret="update-secret-token"
echo "✅ Modo mantenimiento activado"
echo "   ℹ️  Para acceder durante el mantenimiento:"
echo "   https://appvillamitre.surtekbb.com/update-secret-token"
echo ""

# 2. Hacer backup de .env
echo "2️⃣  Respaldando configuración..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup de .env creado"
echo ""

# 3. Pull de cambios
echo "3️⃣  Obteniendo últimos cambios de GitHub..."
git fetch origin
echo "   Cambios disponibles:"
git log HEAD..origin/main --oneline --no-decorate | head -5
echo ""

read -p "   ¿Continuar con el pull? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Actualización cancelada"
    php artisan up
    exit 1
fi

git pull origin main
echo "✅ Código actualizado"
echo ""

# 4. Instalar/actualizar dependencias
echo "4️⃣  Actualizando dependencias..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "✅ Dependencias actualizadas"
echo ""

# 5. Ejecutar migraciones nuevas (si existen)
echo "5️⃣  Verificando migraciones..."
if php artisan migrate:status | grep -q "Pending"; then
    echo "   ⚠️  Hay migraciones pendientes"
    read -p "   ¿Ejecutar migraciones? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate --force
        echo "✅ Migraciones ejecutadas"
    else
        echo "⏭️  Migraciones omitidas"
    fi
else
    echo "✅ No hay migraciones pendientes"
fi
echo ""

# 6. Limpiar cache
echo "6️⃣  Limpiando cache anterior..."
php artisan optimize:clear
echo "✅ Cache limpiado"
echo ""

# 7. Optimizar aplicación
echo "7️⃣  Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
echo "✅ Optimizaciones aplicadas"
echo ""

# 8. Reiniciar queues (si se usan)
echo "8️⃣  Reiniciando queues..."
if pgrep -f "queue:work" > /dev/null; then
    php artisan queue:restart
    echo "✅ Queues reiniciadas"
else
    echo "ℹ️  No hay queues corriendo"
fi
echo ""

# 9. Desactivar modo mantenimiento
echo "9️⃣  Desactivando modo mantenimiento..."
php artisan up
echo "✅ Aplicación de vuelta online"
echo ""

# Resumen
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 ACTUALIZACIÓN COMPLETADA"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 RESUMEN:"
echo "   ✅ Código actualizado desde GitHub"
echo "   ✅ Dependencias actualizadas"
echo "   ✅ Migraciones ejecutadas (si había)"
echo "   ✅ Cache regenerado"
echo "   ✅ Aplicación optimizada"
echo "   ✅ Sitio online"
echo ""
echo "🌐 URL: https://appvillamitre.surtekbb.com"
echo ""
echo "📝 PRÓXIMOS PASOS:"
echo "   1. Verificar que el sitio funcione correctamente"
echo "   2. Revisar logs por si hay errores:"
echo "      tail -f storage/logs/laravel.log"
echo "   3. Probar funcionalidades críticas"
echo ""
echo "💾 Backup de .env guardado en:"
echo "   .env.backup.* (con timestamp)"
echo ""
echo "✅ ¡Actualización exitosa!"
echo ""
