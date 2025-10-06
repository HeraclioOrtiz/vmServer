# Users Table - Data Dictionary

Este documento describe las columnas de la tabla `users`, sus tipos, nullabilidad, default y propósito funcional.

## Identificación y autenticación
- `id` (bigint, PK, not null)
  - Identificador interno autoincremental.
- `dni` (string(8), unique, not null)
  - Documento; clave de negocio. Índices: `idx_dni_type` (junto a `user_type`).
- `user_type` (enum: `local`|`api`, default `api`, not null)
  - Tipo de usuario: local creado en app o sincronizado desde API del club.
- `promotion_status` (enum: `none`|`pending`|`approved`|`rejected`, default `none`, not null)
  - Estado de promoción a usuario API.
- `promoted_at` (timestamp, null)
  - Momento de promoción.
- `password` (string, not null)
  - Hash de contraseña (cast `hashed`).
- `remember_token` (string, null)
  - Token “remember me”.

## Contacto y verificación
- `email` (string, unique inicialmente, luego nullable)
  - Email del usuario (obligatorio para `local`, opcional para `api`).
- `email_verified_at` (timestamp, null)
  - Fecha verificación email.
- `phone` (string, null)
  - Teléfono editable para usuarios `local`.

## Datos personales (API del club)
- `nombre` (string, null)
- `apellido` (string, null)
- `nacionalidad` (string, null)
- `nacimiento` (date, null)
- `domicilio` (string, null)
- `localidad` (string, null)
- `telefono` (string, null)
- `celular` (string, null)
- `tipo_dni` (string, null)
- `tutor` (string, null)

## Identificadores del socio (API del club)
- `socio_id` (string, null)
  - ID del socio en API externa. Sugerido: índice.
- `socio_n` (string, null)
  - Número de socio adicional.
- `barcode` (string, null)
  - Código de barras de carnet. Sugerido: índice.

## Estado de cuenta / administración (API del club)
- `categoria` (string, null)
- `saldo` (decimal(10,2), default 0.00, not null)
- `deuda` (decimal(10,2), default 0.00, not null)
- `descuento` (decimal(10,2), default 0.00, not null)
- `monto_descuento` (decimal(10,2), null)
- `semaforo` (integer, default 1, not null)
  - 1=al día; 10=deuda no exigible; 99=deuda exigible.
- `estado_socio` (string, null)
- `alta` (date, null)
- `fecha_baja` (date, null)
- `suspendido` (boolean, default false, not null)
- `facturado` (boolean, default true, not null)
- `observaciones` (text, null)

## Sincronización con API externa
- `api_updated_at` (timestamp, null)
  - Última actualización general de datos de API.
- `update_ts` (timestamp, null)
  - Timestamp de la API de terceros (marca propia del proveedor).
- `validmail_st` (boolean, default false, not null)
- `validmail_ts` (timestamp, null)

## Imagen / foto de perfil
- `foto_url` (string, null)
  - URL directa a imagen de socio (prioritaria).
- `avatar_path` (string, null)
  - Ruta local de avatar (fallback/legado).

## Auditoría
- `created_at` (timestamp, not null)
- `updated_at` (timestamp, not null)

## Índices actuales
- `users_dni_unique` (dni)
- `idx_dni_type` (dni, user_type)
- `idx_promotion_status` (promotion_status)
- `idx_user_type` (user_type)

## Índices sugeridos (esta versión)
- `idx_users_socio_id` (socio_id)
- `idx_users_barcode` (barcode)

## Notas de dominio
- Usuarios `api` tienen datos sincronizados con el club; solo algunos campos son editables.
- Usuarios `local` pueden editar nombre/email/phone y luego ser promovidos si existen en la API externa.
- `foto_url` es preferida sobre `avatar_path`.
- El cast `hashed` en `password` garantiza que cualquier asignación persista como hash.
