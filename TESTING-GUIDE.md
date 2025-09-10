# Guía de Testing - API de Terceros Actualizada

## Resumen de Testing

Esta guía proporciona los pasos para verificar que la integración con la API de terceros funciona correctamente con la nueva estructura de respuesta y los campos adicionales implementados.

## Archivos de Testing Creados

### 1. `test_api_integration.php`
Script independiente para probar la conexión directa con la API de terceros.

**Uso:**
```bash
php test_api_integration.php
```

**Qué verifica:**
- Conexión con la API de terceros
- Estructura de respuesta correcta (`estado: "0"`)
- Presencia de campos críticos (`barcode`, `saldo`, `semaforo`)
- Descarga de imagen de perfil
- Validación de tipo de imagen

### 2. `test_registration_flow.php`
Simulación completa del flujo de registro con la nueva estructura.

**Uso:**
```bash
php test_registration_flow.php
```

**Qué simula:**
- Proceso completo de registro
- Validación de DNI
- Consulta a API de terceros
- Promoción LOCAL → API
- Descarga síncrona de imagen
- Respuesta final con todos los campos

## Datos de Prueba

### DNI de Testing: `20562964`
**Usuario:** ADRIAN HERNAN GONZALEZ

**Datos esperados:**
```json
{
  "Id": "29219",
  "nombre": "ADRIAN HERNAN",
  "apellido": "GONZALEZ",
  "dni": "20562964",
  "mail": "agonzalez.lacoope@gmail.com",
  "barcode": "73858850140000115123200000008",
  "saldo": "0.00",
  "semaforo": "1",
  "socio_n": "18305"
}
```

## Testing Manual con Postman

### 1. Registro de Usuario
**Endpoint:** `POST /api/auth/register`

**Request Body:**
```json
{
  "dni": "20562964",
  "password": "test123",
  "password_confirmation": "test123",
  "name": "Test User"
}
```

**Respuesta Esperada:**
```json
{
  "message": "Usuario registrado exitosamente",
  "user": {
    "id": 1,
    "dni": "20562964",
    "name": "GONZALEZ, ADRIAN HERNAN",
    "email": "agonzalez.lacoope@gmail.com",
    "barcode": "73858850140000115123200000008",
    "saldo": 0.00,
    "semaforo": 1,
    "foto_url": "http://localhost:8000/storage/avatars/29219.jpg",
    "user_type": "api"
  },
  "token": "..."
}
```

### 2. Login de Usuario
**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
  "dni": "20562964",
  "password": "test123"
}
```

### 3. Información de Usuario
**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

## Verificaciones Críticas

### ✅ Campos Obligatorios en Respuesta
- `foto_url` - URL completa de imagen
- `barcode` - Código de pago digital
- `saldo` - Saldo cuenta corriente
- `semaforo` - Estado de deuda

### ✅ Validación de Estados
- `semaforo: 1` = "Al día" ✅
- `semaforo: 99` = "Con deuda exigible" ⚠️
- `semaforo: 10` = "Con deuda no exigible" 🔶

### ✅ Imagen de Perfil
- Descarga síncrona durante registro/login
- `foto_url` disponible inmediatamente
- Archivo guardado en `storage/app/public/avatars/`

### ✅ Mapeo de Campos
- `mail` → `email`
- `Id` → `socio_id`
- Todos los campos adicionales mapeados correctamente

## Comandos de Testing

### Verificar Base de Datos
```bash
# Verificar migraciones
php artisan migrate:status

# Ver estructura de tabla users
php artisan tinker
>>> Schema::getColumnListing('users')
```

### Limpiar Cache y Testing
```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear

# Ejecutar tests
php artisan test

# Ver logs
tail -f storage/logs/laravel.log
```

### Docker (si aplica)
```bash
# Verificar servicios
docker-compose ps

# Ejecutar comandos en container
docker-compose exec app php artisan migrate:status
docker-compose exec app php test_api_integration.php
```

## Debugging y Logs

### Logs Importantes a Verificar

1. **Conexión API exitosa:**
```
SociosApi form response: {"estado":"0","result":{...},"msg":"Proceso OK"}
```

2. **Descarga de imagen:**
```
✅ Avatar descargado síncronamente: socio_id=29219, path=avatars/29219.jpg
```

3. **Promoción de usuario:**
```
Usuario promovido automáticamente: dni=20562964, tipo=api
```

### Errores Comunes

1. **Estado != "0":**
```
SociosApi error response: estado=1, msg="Error message"
```

2. **Imagen no encontrada:**
```
❌ No hay datos de imagen para descargar: socio_id=29219
```

3. **Timeout de API:**
```
💥 Error al descargar avatar síncronamente: timeout
```

## Checklist de Verificación

### Backend ✅
- [ ] API responde con `estado: "0"`
- [ ] Campos `barcode`, `saldo`, `semaforo` mapeados
- [ ] Campo `mail` → `email` correctamente
- [ ] Imagen descarga síncronamente
- [ ] `foto_url` en respuesta JSON
- [ ] Usuario promociona LOCAL → API
- [ ] Logs detallados funcionando

### Frontend (Pendiente)
- [ ] Interface `UserData` actualizada
- [ ] Campo `foto_url` utilizado para imagen
- [ ] Estados de `semaforo` manejados correctamente
- [ ] `barcode` disponible para pagos
- [ ] `saldo` mostrado en UI
- [ ] Validaciones de campos nuevos

## Próximos Pasos

1. **Ejecutar tests de integración** con DNI real
2. **Verificar logs** durante registro/login
3. **Confirmar descarga de imágenes**
4. **Validar respuestas JSON** completas
5. **Actualizar frontend** según documentación
6. **Testing end-to-end** con app móvil

## Contacto y Soporte

Para issues o dudas sobre la integración:
- Revisar logs en `storage/logs/laravel.log`
- Verificar configuración en `.env`
- Confirmar conectividad con API de terceros
- Validar estructura de base de datos
