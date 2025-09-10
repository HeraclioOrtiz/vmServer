# 📱 Af:\Laburo\Programacion\Laburo-Javi\VILLAMITRE\vmServer\docs\api\API-CONTRACTS.mdPP

## 🌐 BASE URL
```
Production: https://your-domain.com/api
Development: http://localhost:8000/api
```

## 🔐 AUTHENTICATION
Todas las rutas protegidas requieren header:
```
Authorization: Bearer {token}
```

---

## 📋 ENDPOINTS

### 🔑 AUTHENTICATION

#### POST /auth/register
Registra un nuevo usuario con promoción automática.

**Request:**
```json
{
    "dni": "12345678",
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+54911123456"
}
```

**Response (201):**
```json
{
    "data": {
        "token": "1|abc123...",
        "user": {
            "id": 1,
            "dni": "12345678",
            "user_type": "api",
            "type_label": "Usuario API",
            "name": "PÉREZ, JUAN",
            "display_name": "PÉREZ, JUAN",
            "email": null,
            "phone": "+54911123456",
            "promotion_status": "none",
            "promotion_label": "Sin promoción",
            "promoted_at": null,
            "can_promote": false,
            "is_complete": true,
            "nombre": "JUAN",
            "apellido": "PÉREZ",
            "full_name": "PÉREZ, JUAN",
            "nacionalidad": "Argentina",
            "nacimiento": "1990-01-15",
            "domicilio": "Av. Principal 123",
            "localidad": "Buenos Aires",
            "telefono": "011-4567-8900",
            "celular": "11-2345-6789",
            "categoria": "1",
            "socio_id": "12345",
            "barcode": "12345",
            "estado_socio": "1",
            "foto_url": "https://your-domain.com/storage/avatars/12345.jpg",
            "api_updated_at": "2025-01-01T10:00:00.000000Z",
            "created_at": "2025-01-01T10:00:00.000000Z",
            "updated_at": "2025-01-01T10:00:00.000000Z"
        },
        "fetched_from_api": true,
        "refreshed": true
    }
}
```

#### POST /auth/login
Autentica usuario por DNI y contraseña.

**Request:**
```json
{
    "dni": "12345678",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "data": {
        "token": "2|def456...",
        "user": {
            // Mismo formato que register
        },
        "fetched_from_api": false,
        "refreshed": false
    }
}
```

#### GET /auth/me
Obtiene información del usuario autenticado.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "dni": "12345678",
        "user_type": "api",
        "type_label": "Usuario API",
        "name": "PÉREZ, JUAN",
        "display_name": "PÉREZ, JUAN",
        "email": null,
        "phone": "+54911123456",
        "promotion_status": "none",
        "promotion_label": "Sin promoción",
        "promoted_at": null,
        "can_promote": false,
        "is_complete": true,
        "nombre": "JUAN",
        "apellido": "PÉREZ",
        "full_name": "PÉREZ, JUAN",
        "nacionalidad": "Argentina",
        "nacimiento": "1990-01-15",
        "domicilio": "Av. Principal 123",
        "localidad": "Buenos Aires",
        "telefono": "011-4567-8900",
        "celular": "11-2345-6789",
        "categoria": "1",
        "socio_id": "12345",
        "barcode": "12345",
        "estado_socio": "1",
        "foto_url": "https://your-domain.com/storage/avatars/12345.jpg",
        "api_updated_at": "2025-01-01T10:00:00.000000Z",
        "created_at": "2025-01-01T10:00:00.000000Z",
        "updated_at": "2025-01-01T10:00:00.000000Z"
    }
}
```

#### POST /auth/logout
Cierra sesión del usuario.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "message": "Sesión cerrada exitosamente"
}
```

---

### 👥 USERS

#### GET /users
Lista usuarios con paginación (requiere autenticación).

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
```
?page=1&per_page=15&search=juan&type=api
```

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "dni": "12345678",
            "user_type": "api",
            "type_label": "Usuario API",
            "name": "PÉREZ, JUAN",
            "display_name": "PÉREZ, JUAN",
            "email": null,
            "phone": "+54911123456",
            "is_complete": true,
            "created_at": "2025-01-01T10:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/users?page=1",
        "last": "http://localhost:8000/api/users?page=10",
        "prev": null,
        "next": "http://localhost:8000/api/users?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

#### GET /users/{id}
Obtiene un usuario específico.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "data": {
        // Mismo formato que /auth/me
    }
}
```

#### PUT /users/{id}
Actualiza un usuario.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
    "name": "Juan Carlos Pérez",
    "email": "juan.carlos@example.com",
    "phone": "+54911987654"
}
```

**Response (200):**
```json
{
    "data": {
        // Usuario actualizado
    }
}
```

#### DELETE /users/{id}
Elimina un usuario (soft delete).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "message": "Usuario eliminado exitosamente"
}
```

---

### 🚀 PROMOTIONS

#### POST /promotions/promote
Promueve un usuario local a API.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
    "user_id": 1
}
```

**Response (200):**
```json
{
    "data": {
        "user": {
            // Usuario promovido
        },
        "promoted": true,
        "message": "Usuario promovido exitosamente"
    }
}
```

---

## 📊 DATA MODELS

### User Object
```typescript
interface User {
    id: number;
    dni: string;
    user_type: "local" | "api";
    type_label: string;
    name: string;
    display_name: string;
    email: string | null;
    phone: string | null;
    promotion_status: "none" | "pending" | "approved" | "rejected";
    promotion_label: string;
    promoted_at: string | null;
    can_promote: boolean;
    is_complete: boolean;
    
    // Campos específicos de socios (solo para user_type: "api")
    nombre?: string;
    apellido?: string;
    full_name?: string;
    nacionalidad?: string;
    nacimiento?: string;
    domicilio?: string;
    localidad?: string;
    telefono?: string;
    celular?: string;
    categoria?: string;
    socio_id?: string;
    barcode?: string;
    estado_socio?: string;
    foto_url?: string;
    api_updated_at?: string;
    
    created_at: string;
    updated_at: string;
}
```

### Auth Response
```typescript
interface AuthResponse {
    token: string;
    user: User;
    fetched_from_api: boolean;
    refreshed: boolean;
}
```

---

## ⚠️ ERROR RESPONSES

### Validation Errors (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "dni": ["El DNI ya está registrado."],
        "email": ["El email es requerido."],
        "password": ["La contraseña debe tener al menos 8 caracteres."]
    }
}
```

### Authentication Errors (401)
```json
{
    "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
    "message": "Usuario no encontrado"
}
```

### Server Error (500)
```json
{
    "message": "Error interno del servidor"
}
```

---

## 🔧 VALIDATION RULES

### Register
- `dni`: required, string, 8 digits, unique
- `name`: required, string, max:255
- `email`: required, email, max:255, unique
- `password`: required, string, min:8, confirmed
- `phone`: optional, string, max:20

### Login
- `dni`: required, string, 8 digits
- `password`: required, string

### User Update
- `name`: optional, string, max:255
- `email`: optional, email, max:255, unique (except current user)
- `phone`: optional, string, max:20

---

## 🚀 IMPLEMENTATION NOTES

### User Types
- **local**: Usuario registrado localmente, datos básicos
- **api**: Usuario sincronizado con API de terceros, datos completos

### Automatic Promotion
- Al registrarse, el sistema consulta automáticamente la API de terceros
- Si el DNI existe, el usuario se promociona automáticamente a "api"
- Los datos de la API reemplazan los del registro (excepto password y DNI)

### Authentication Flow
1. Usuario se registra con DNI y datos básicos
2. Sistema valida con API de terceros automáticamente
3. Si existe → promoción a "api" + datos completos
4. Si no existe → mantiene como "local"
5. Token JWT generado para autenticación

### Cache Strategy
- Cache de usuarios por DNI (1 hora)
- Cache negativo para DNIs no encontrados (15 minutos)
- Circuit breaker para fallos de API (5 minutos)

---

## 📱 MOBILE APP INTEGRATION

### Headers Requeridos
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token} // Para rutas protegidas
```

### Token Management
- Almacenar token de forma segura (Keychain/Keystore)
- Incluir en todas las requests autenticadas
- Renovar cuando expire (implementar refresh token si es necesario)

### Error Handling
- Manejar códigos de estado HTTP apropiadamente
- Mostrar mensajes de error user-friendly
- Implementar retry logic para errores de red

### Offline Support
- Cachear datos del usuario localmente
- Sincronizar cuando vuelva la conectividad
- Manejar conflictos de datos

---

## 🧪 TESTING ENDPOINTS

### Base URL Testing
```
http://localhost:8000/api
```

### Test User
```json
{
    "dni": "59964604",
    "password": "password123"
}
```

Este usuario existe en la API de terceros y se promociona automáticamente.
