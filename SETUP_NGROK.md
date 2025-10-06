# 🚀 CONFIGURACIÓN NGROK PARA PRESENTACIÓN

## PASO 1: Descargar e instalar ngrok

1. Ve a: https://ngrok.com/download
2. Descarga la versión para Windows
3. Extrae el archivo `ngrok.exe` en una carpeta (ej: `C:\ngrok\`)

## PASO 2: Crear cuenta gratuita (opcional pero recomendado)

1. Registrate en: https://dashboard.ngrok.com/signup
2. Copia tu authtoken desde: https://dashboard.ngrok.com/get-started/your-authtoken
3. Ejecuta: `ngrok config add-authtoken TU_TOKEN_AQUI`

## PASO 3: Iniciar tu servidor Laravel

```bash
# En tu terminal de Laravel
cd f:\Laburo\Programacion\Laburo-Javi\VILLAMITRE\vmServer
php artisan serve --host=0.0.0.0 --port=8000
```

## PASO 4: Exponer con ngrok

```bash
# En otra terminal
ngrok http 8000
```

## RESULTADO:
```
ngrok by @inconshreveable

Session Status                online
Account                       tu-email@gmail.com
Version                       3.x.x
Region                        United States (us)
Latency                       45ms
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123.ngrok.io -> http://localhost:8000

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

## 🎯 URL PARA CLIENTES:
**https://abc123.ngrok.io**

## ⚙️ CONFIGURACIÓN LARAVEL:

Agrega a tu `.env`:
```env
APP_URL=https://abc123.ngrok.io
SANCTUM_STATEFUL_DOMAINS=abc123.ngrok.io
SESSION_DOMAIN=.abc123.ngrok.io
```

## 📱 PARA APP MÓVIL:
Cambia la URL base de tu app móvil a: `https://abc123.ngrok.io`

## 🖥️ PARA PANEL ADMIN:
Accede desde: `https://abc123.ngrok.io/admin`
