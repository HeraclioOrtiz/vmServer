# 🔐 MANUAL DE CONEXIÓN SSH - VILLA MITRE SERVER

---

## 📋 INFORMACIÓN DEL SERVIDOR

**IP:** `38.242.206.48`  
**Usuario:** `root`  
**Password:** `n381CG1XTqeI0d`  
**Proyecto API:** `/var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com`  
**Proyecto Panel:** `/var/www/appvillamitr_usr/data/www/panel.appvillamitre.surtekbb.com`

---

## 🚀 OPCIÓN 1: CONEXIÓN RÁPIDA (CON PASSWORD)

### Desde PowerShell o CMD:

```powershell
ssh root@38.242.206.48
```

Cuando pida password, pegar: `n381CG1XTqeI0d`

---

## 🔑 OPCIÓN 2: GENERAR Y CONFIGURAR SSH KEY (MÁS SEGURO)

### PASO 1: GENERAR SSH KEY EN TU PC

Abre **PowerShell** y ejecuta:

```powershell
# Crear directorio .ssh si no existe
mkdir $HOME\.ssh -ErrorAction SilentlyContinue

# Generar SSH key
ssh-keygen -t rsa -b 4096 -C "tu_email@ejemplo.com" -f $HOME\.ssh\villamitre_rsa

# Presiona Enter 3 veces (sin passphrase para acceso automático)
```

Esto crea 2 archivos:
- `C:\Users\TU_USUARIO\.ssh\villamitre_rsa` (clave privada - NO COMPARTIR)
- `C:\Users\TU_USUARIO\.ssh\villamitre_rsa.pub` (clave pública)

---

### PASO 2: COPIAR CLAVE PÚBLICA AL SERVIDOR

#### Opción A - Automática (Windows 10+):

```powershell
type $HOME\.ssh\villamitre_rsa.pub | ssh root@38.242.206.48 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys"
```

#### Opción B - Manual:

1. Ver tu clave pública:
```powershell
type $HOME\.ssh\villamitre_rsa.pub
```

2. Copiar todo el contenido (empieza con `ssh-rsa`)

3. Conectar al servidor:
```powershell
ssh root@38.242.206.48
```

4. En el servidor ejecutar:
```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
# Pegar la clave pública copiada
# Guardar: Ctrl+O, Enter, Ctrl+X

chmod 600 ~/.ssh/authorized_keys
```

---

### PASO 3: CONECTAR CON SSH KEY (SIN PASSWORD)

```powershell
ssh -i $HOME\.ssh\villamitre_rsa root@38.242.206.48
```

---

### PASO 4: CONFIGURAR CONEXIÓN AUTOMÁTICA (OPCIONAL)

Crear/editar archivo de configuración SSH:

```powershell
notepad $HOME\.ssh\config
```

Agregar:

```
Host villamitre
    HostName 38.242.206.48
    User root
    IdentityFile ~/.ssh/villamitre_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

Guardar y cerrar.

**Ahora puedes conectar simplemente con:**

```powershell
ssh villamitre
```

---

## 📂 COMANDOS ÚTILES AL CONECTAR

### Ir al proyecto API:
```bash
cd /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com
```

### Ir al proyecto Panel:
```bash
cd /var/www/appvillamitr_usr/data/www/panel.appvillamitre.surtekbb.com
```

### Ver logs de Laravel:
```bash
tail -f /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com/storage/logs/laravel.log
```

### Ver logs de Nginx:
```bash
tail -f /var/log/nginx/error.log
```

### Actualizar código desde Git:
```bash
cd /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com
git pull origin main
```

### Limpiar cache de Laravel:
```bash
cd /var/www/appvillamitr_usr/data/www/appvillamitre.surtekbb.com
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Rebuild del Panel Frontend:
```bash
cd /var/www/appvillamitr_usr/data/www/panel.appvillamitre.surtekbb.com
git pull origin main
npm install
npm run build
```

### Ver espacio en disco:
```bash
df -h
```

### Ver procesos de Nginx:
```bash
systemctl status nginx
```

### Reiniciar Nginx (si es necesario):
```bash
systemctl restart nginx
```

---

## 🔍 SOLUCIÓN DE PROBLEMAS

### Error: "Permission denied (publickey,password)"

**Causa:** Contraseña incorrecta o conexión bloqueada

**Solución:**
1. Verificar que copias la contraseña SIN espacios
2. Esperar 5 minutos (puede estar bloqueado por intentos fallidos)
3. Usar consola web de FastPanel

---

### Error: "Connection refused"

**Causa:** Servidor apagado o firewall bloqueando

**Solución:**
1. Verificar que el servidor esté encendido en Contabo
2. Verificar firewall del servidor
3. Contactar soporte de Contabo

---

### Error: "Host key verification failed"

**Causa:** Fingerprint del servidor cambió

**Solución:**
```powershell
ssh-keygen -R 38.242.206.48
```

Luego conectar nuevamente.

---

## 🌐 ACCESOS ALTERNATIVOS

### FastPanel (Consola Web):

Si no puedes conectar por SSH, usa la consola web:

1. Ir a: `https://38.242.206.48:8888`
2. Login con credenciales de FastPanel
3. Buscar opción **"Terminal"** o **"Console"**
4. Ya estarás conectado como root

---

## 📞 INFORMACIÓN DE EMERGENCIA

**Servidor:** Contabo VPS  
**Panel de Control:** https://my.contabo.com  
**FastPanel:** https://38.242.206.48:8888  

**Dominios:**
- API: https://appvillamitre.surtekbb.com
- Panel: https://panel.appvillamitre.surtekbb.com

**DNS:** Configurado en IONOS (panel.ionos.es)

---

## ✅ CHECKLIST DE CONEXIÓN RÁPIDA

```powershell
# 1. Abrir PowerShell

# 2. Conectar
ssh root@38.242.206.48

# 3. Pegar password cuando lo pida
# n381CG1XTqeI0d

# 4. Ya estás dentro ✅
```

---

## 🎯 RESUMEN

**Conexión más rápida:** `ssh root@38.242.206.48`  
**Password:** `n381CG1XTqeI0d`  
**Alternativa:** Consola web de FastPanel  

**¡Listo para mañana!** 🚀
