# ☁️ CONFIGURACIÓN CLOUDFLARE TUNNEL

## PASO 1: Instalar cloudflared

1. Descarga desde: https://github.com/cloudflare/cloudflared/releases
2. Descarga `cloudflared-windows-amd64.exe`
3. Renómbralo a `cloudflared.exe` y ponlo en `C:\cloudflared\`

## PASO 2: Login en Cloudflare

```bash
cloudflared tunnel login
```
- Se abrirá el navegador
- Inicia sesión en Cloudflare
- Selecciona tu dominio (o crea cuenta gratuita)

## PASO 3: Crear tunnel

```bash
cloudflared tunnel create villa-mitre-demo
```

## PASO 4: Configurar tunnel

Crea archivo `config.yml`:
```yaml
tunnel: villa-mitre-demo
credentials-file: C:\Users\TU_USUARIO\.cloudflared\TUNNEL_ID.json

ingress:
  - hostname: villa-mitre-demo.tu-dominio.com
    service: http://localhost:8000
  - service: http_status:404
```

## PASO 5: Ejecutar

```bash
# Terminal 1: Laravel
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Cloudflare
cloudflared tunnel run villa-mitre-demo
```

## 🎯 RESULTADO:
**https://villa-mitre-demo.tu-dominio.com**
