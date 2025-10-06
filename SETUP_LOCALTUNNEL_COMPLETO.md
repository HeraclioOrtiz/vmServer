# 🚇 CONFIGURACIÓN COMPLETA LOCALTUNNEL

## ✅ INSTALACIÓN COMPLETADA
- Node.js: v22.13.1 ✅
- LocalTunnel: Instalado ✅

## 🚀 PASOS PARA EXPONER EL SERVIDOR

### PASO 1: Iniciar Laravel
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### PASO 2: Exponer con LocalTunnel (en otra terminal)
```bash
lt --port 8000 --subdomain villamitre
```

## 🌐 URL RESULTANTE:
**https://villamitre.loca.lt**

## ⚙️ CONFIGURACIÓN LARAVEL NECESARIA:

### Actualizar .env:
```env
APP_URL=https://villamitre.loca.lt
SANCTUM_STATEFUL_DOMAINS=villamitre.loca.lt
SESSION_DOMAIN=.villamitre.loca.lt
CORS_ALLOWED_ORIGINS=https://villamitre.loca.lt
```

### Configurar CORS (config/cors.php):
```php
'allowed_origins' => [
    'https://villamitre.loca.lt',
    'http://localhost:3000', // Para desarrollo local
],
'supports_credentials' => true,
```

## 📱 PARA APP MÓVIL:
Cambiar URL base a: `https://villamitre.loca.lt`

## 🖥️ PARA PANEL ADMIN:
Acceder desde: `https://villamitre.loca.lt/admin`

## 🔐 TESTING CON MARÍA GARCÍA:
- **Login:** `POST https://villamitre.loca.lt/api/auth/login`
- **DNI:** 33333333
- **Password:** estudiante123

## ⚠️ NOTAS IMPORTANTES:
1. La primera vez puede pedir verificación en navegador
2. El tunnel se mantiene activo mientras esté ejecutándose
3. Si se desconecta, solo ejecuta `lt --port 8000 --subdomain villamitre` nuevamente
