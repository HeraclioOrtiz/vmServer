# 🚀 GUÍA DE DEPLOYMENT - VILLA MITRE API

**Servidor:** appvillamitre.surtekbb.com  
**Panel:** FastPanel en Ubuntu 24.02  
**Última actualización:** 2025-10-07

---

## 📋 **ARCHIVOS DE DEPLOYMENT CREADOS**

### **1. ProductionSeeder.php**
**Ubicación:** `database/seeders/ProductionSeeder.php`

**Descripción:** Crea los usuarios mínimos necesarios para producción.

**Usuarios que crea:**
- ✅ Admin Villa Mitre (DNI: 11111111)
- ✅ Profesor Juan Pérez (DNI: 22222222)
- ✅ Estudiante María García (DNI: 55555555)

**Ejecución:**
```bash
php artisan db:seed --class=ProductionSeeder --force
```

---

### **2. .env.production.example**
**Ubicación:** `.env.production.example`

**Descripción:** Template de configuración para producción.

**Uso en servidor:**
```bash
# 1. Copiar a .env
cp .env.production.example .env

# 2. Editar y completar valores reales
nano .env

# 3. Generar APP_KEY
php artisan key:generate
```

**Valores que debes configurar:**
- `DB_DATABASE` = nombre de tu base de datos
- `DB_USERNAME` = usuario MySQL
- `DB_PASSWORD` = contraseña MySQL
- `APP_URL` = https://appvillamitre.surtekbb.com

---

### **3. AssignAllStudentsToProfessor Command**
**Ubicación:** `app/Console/Commands/AssignAllStudentsToProfessor.php`

**Descripción:** Comando para asignar todos los estudiantes al profesor.

**Uso:**
```bash
# Asignar al profesor con DNI 22222222 (default)
php artisan students:assign-to-professor

# Asignar a otro profesor (especificar DNI)
php artisan students:assign-to-professor 33333333
```

**Qué hace:**
- Busca todos los usuarios que NO son profesores ni admins
- Los asigna al profesor especificado
- Omite estudiantes ya asignados
- Muestra progreso y resumen

---

### **4. deploy-inicial.sh**
**Ubicación:** `deploy-inicial.sh`

**Descripción:** Script automatizado de deployment inicial.

**Uso:**
```bash
# Dar permisos de ejecución
chmod +x deploy-inicial.sh

# Ejecutar
./deploy-inicial.sh
```

**Qué hace automáticamente:**
1. ✅ Instala dependencias de Composer
2. ✅ Genera APP_KEY
3. ✅ Ejecuta migraciones
4. ✅ Crea usuarios iniciales
5. ✅ Limpia cache
6. ✅ Optimiza la aplicación
7. ✅ Configura permisos

---

## 🔄 **PROCESO DE DEPLOYMENT PASO A PASO**

### **FASE 1: Preparación en FastPanel**

#### **1.1 Crear Base de Datos**
1. Login en FastPanel: https://38.242.206.48:8888/
2. Ir a **"MySQL"** o **"Databases"**
3. Click en **"Crear nueva base de datos"**
4. Configurar:
   - Nombre: `villamitre_prod`
   - Usuario: `villamitre_user`
   - Password: [generar seguro y guardarlo]
5. Click en **"Crear"**

#### **1.2 Crear Sitio Web**
1. En FastPanel, ir a **"Sitios"** o **"Websites"**
2. Click en **"Añadir sitio"**
3. Configurar:
   - Dominio: `appvillamitre.surtekbb.com`
   - PHP Version: 8.2 o superior
   - Document Root: `/public` (importante para Laravel)
4. Habilitar SSL (Let's Encrypt)
5. Click en **"Crear"**

#### **1.3 Verificar PHP y Extensiones**
1. En FastPanel, ir a **"PHP"**
2. Verificar extensiones instaladas:
   - ✅ OpenSSL
   - ✅ PDO
   - ✅ Mbstring
   - ✅ XML
   - ✅ Ctype
   - ✅ JSON
   - ✅ BCMath
   - ✅ Fileinfo
   - ✅ Redis

---

### **FASE 2: Clonar y Configurar Proyecto**

#### **2.1 Conectar por SSH**
```bash
ssh fastuser@38.242.206.48
```

#### **2.2 Ir al directorio del sitio**
```bash
cd /var/www/appvillamitre  # O la ruta que te indique FastPanel
```

#### **2.3 Clonar repositorio**
```bash
git clone https://github.com/HeraclioOrtiz/vmServer.git .
```

**Nota:** El punto (`.`) al final es importante para clonar en el directorio actual.

#### **2.4 Configurar .env**
```bash
# Copiar template
cp .env.production.example .env

# Editar con nano o vim
nano .env
```

**Valores a configurar:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://appvillamitre.surtekbb.com

DB_DATABASE=villamitre_prod
DB_USERNAME=villamitre_user
DB_PASSWORD=[el password que creaste en FastPanel]
```

Guardar y salir (Ctrl+X, Y, Enter en nano).

---

### **FASE 3: Ejecutar Deployment**

#### **3.1 Dar permisos al script**
```bash
chmod +x deploy-inicial.sh
```

#### **3.2 Ejecutar deployment**
```bash
./deploy-inicial.sh
```

El script hará todo automáticamente. Verás el progreso de cada paso.

#### **3.3 Verificar que finalizó correctamente**
Deberías ver al final:
```
🎉 DEPLOYMENT INICIAL COMPLETADO
```

Y las credenciales de acceso.

---

### **FASE 4: Verificación**

#### **4.1 Verificar que el sitio está accesible**
Abrir en navegador:
```
https://appvillamitre.surtekbb.com
```

Deberías ver una respuesta JSON o mensaje de Laravel (no error 500).

#### **4.2 Probar login**
```bash
# Endpoint de login
POST https://appvillamitre.surtekbb.com/api/auth/login

# Body (JSON):
{
  "dni": "11111111",
  "password": "admin123"
}
```

Deberías recibir un token JWT.

#### **4.3 Verificar usuarios creados**
```bash
php artisan tinker

# En tinker:
User::count()
# Debería mostrar 3

User::all()
# Debería mostrar admin, profesor, estudiante
```

---

## 📊 **COMANDOS ÚTILES POST-DEPLOYMENT**

### **Ver logs en tiempo real:**
```bash
tail -f storage/logs/laravel.log
```

### **Limpiar cache:**
```bash
php artisan optimize:clear
```

### **Re-optimizar:**
```bash
php artisan optimize
```

### **Ver rutas disponibles:**
```bash
php artisan route:list
```

### **Asignar estudiantes al profesor:**
```bash
php artisan students:assign-to-professor
```

### **Verificar conexión a BD:**
```bash
php artisan db:show
```

### **Ejecutar migraciones nuevas:**
```bash
php artisan migrate --force
```

---

## 🔒 **CREDENCIALES INICIALES**

### **Panel FastPanel:**
```
URL: https://38.242.206.48:8888/
Usuario: fastuser
Password: jVe6QUtx2qO784Py
```

### **Usuarios de la Aplicación:**

**Administrador:**
```
Email: admin@villamitre.com
DNI: 11111111
Password: admin123
```

**Profesor:**
```
Email: profesor@villamitre.com
DNI: 22222222
Password: profesor123
```

**Estudiante de Prueba:**
```
Email: maria.garcia@villamitre.com
DNI: 55555555
Password: maria123
```

⚠️ **CAMBIAR ESTAS CONTRASEÑAS DESPUÉS DEL PRIMER LOGIN**

---

## 🔄 **DEPLOYMENT DE ACTUALIZACIONES**

Para deployments futuros (actualizaciones del código):

```bash
# 1. Conectar SSH
ssh fastuser@38.242.206.48

# 2. Ir al directorio
cd /var/www/appvillamitre

# 3. Activar modo mantenimiento
php artisan down

# 4. Pull últimos cambios
git pull origin main

# 5. Instalar/actualizar dependencias
composer install --no-dev --optimize-autoloader

# 6. Ejecutar migraciones (si hay nuevas)
php artisan migrate --force

# 7. Limpiar y optimizar
php artisan optimize:clear
php artisan optimize

# 8. Reiniciar queues (si usas)
php artisan queue:restart

# 9. Desactivar modo mantenimiento
php artisan up
```

Puedes crear un script `deploy-update.sh` con estos comandos.

---

## ⚠️ **TROUBLESHOOTING**

### **Error 500 al acceder:**
```bash
# Ver logs
tail -f storage/logs/laravel.log

# Verificar permisos
chmod -R 775 storage bootstrap/cache

# Limpiar cache
php artisan optimize:clear
```

### **Error de conexión a BD:**
```bash
# Verificar credenciales en .env
cat .env | grep DB_

# Probar conexión
php artisan db:show
```

### **Página en blanco:**
```bash
# Verificar APP_KEY
cat .env | grep APP_KEY

# Regenerar si está vacío
php artisan key:generate --force
```

### **Error de permisos:**
```bash
# Arreglar permisos
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

---

## 📞 **SOPORTE**

Si encuentras problemas:

1. Revisa los logs: `storage/logs/laravel.log`
2. Verifica la configuración de `.env`
3. Confirma que todas las extensiones de PHP estén instaladas
4. Verifica que la base de datos esté creada y accesible

---

## ✅ **CHECKLIST DE DEPLOYMENT**

- [ ] Base de datos creada en FastPanel
- [ ] Sitio web configurado con SSL
- [ ] Repositorio clonado
- [ ] `.env` configurado correctamente
- [ ] `deploy-inicial.sh` ejecutado exitosamente
- [ ] Sitio accesible en https://appvillamitre.surtekbb.com
- [ ] Login funcionando
- [ ] Usuarios creados correctamente
- [ ] Contraseñas iniciales cambiadas
- [ ] Logs monitoreados
- [ ] Backups configurados

---

**¡Listo para producción!** 🚀
