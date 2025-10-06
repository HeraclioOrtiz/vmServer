# Gym Service - Mobile API Guide

## Introducción

Esta guía está dirigida a desarrolladores de la **app móvil** que necesitan integrar el servicio de gimnasios. Los alumnos pueden consultar sus rutinas semanales y diarias asignadas por profesores.

## Autenticación

### Requisitos
- **Token Sanctum**: obtenido via `/api/auth/login`
- **Header requerido**: `Authorization: Bearer {token}`
- **Usuario alumno**: cualquier usuario autenticado puede consultar sus rutinas

### Obtener Token
```http
POST /api/auth/login
Content-Type: application/json

{
  "dni": "12345678",
  "password": "password123"
}
```

**Respuesta exitosa (200):**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "dni": "12345678",
    "name": "Juan Pérez"
  }
}
```

## Endpoints Disponibles

### 1. Consultar Mi Semana

Obtiene la semana de entrenamiento vigente para el usuario autenticado.

```http
GET /api/gym/my-week?date=2025-09-22
Authorization: Bearer {token}
```

**Parámetros:**
- `date` (opcional): fecha en formato `YYYY-MM-DD`. Si se omite, usa la fecha actual.

**Respuesta exitosa (200):**
```json
{
  "week_start": "2025-09-22",
  "week_end": "2025-09-28",
  "days": [
    {
      "weekday": 1,
      "date": "2025-09-22",
      "has_session": true,
      "title": "Full-Body 45'"
    },
    {
      "weekday": 2,
      "date": "2025-09-23",
      "has_session": false,
      "title": null
    },
    {
      "weekday": 3,
      "date": "2025-09-24",
      "has_session": true,
      "title": "Push 60'"
    }
    // ... días 4-7
  ]
}
```

**Sin asignación (200):**
```json
{
  "week_start": null,
  "week_end": null,
  "days": []
}
```

### 2. Consultar Mi Día

Obtiene el detalle de la rutina para un día específico.

```http
GET /api/gym/my-day?date=2025-09-22
Authorization: Bearer {token}
```

**Parámetros:**
- `date` (opcional): fecha en formato `YYYY-MM-DD`. Si se omite, usa la fecha actual.

**Respuesta exitosa (200):**
```json
{
  "title": "Full-Body 45'",
  "exercises": [
    {
      "name": "Sentadilla con barra",
      "order": 1,
      "sets": [
        {
          "reps": "8-10",
          "rest_seconds": 120,
          "tempo": "3-1-1",
          "rpe_target": 8.0,
          "notes": "Técnica controlada"
        },
        {
          "reps": "8-10",
          "rest_seconds": 120,
          "tempo": "3-1-1",
          "rpe_target": 8.0,
          "notes": null
        }
      ],
      "notes": "Mantener columna neutra"
    },
    {
      "name": "Press banca",
      "order": 2,
      "sets": [
        {
          "reps": "6-8",
          "rest_seconds": 180,
          "tempo": "2-1-1",
          "rpe_target": 9.0,
          "notes": null
        }
      ],
      "notes": "Pausa en pecho"
    }
  ]
}
```

**Sin rutina para el día (200):**
```json
{
  "title": null,
  "exercises": []
}
```

**Sin asignación para la fecha (404):**
```json
{
  "message": "No assignment found for date"
}
```

## Códigos de Respuesta

| Código | Significado | Acción Recomendada |
|--------|-------------|-------------------|
| 200 | Éxito | Procesar datos normalmente |
| 401 | No autenticado | Redirigir a login |
| 404 | Sin asignación para fecha | Mostrar "Sin rutina hoy" |
| 500 | Error servidor | Mostrar error genérico, reintentar |

## Formato de Datos

### Campos de Ejercicio
- **name**: Nombre del ejercicio (ej: "Sentadilla con barra")
- **order**: Orden de ejecución en la rutina (1, 2, 3...)
- **notes**: Instrucciones específicas del ejercicio (opcional)

### Campos de Serie
- **reps**: Repeticiones en formato "min-max" o valor fijo (ej: "8-10", "12")
- **rest_seconds**: Descanso en segundos (ej: 120 = 2 minutos)
- **tempo**: Tempo de ejecución (ej: "3-1-1" = 3s excéntrico, 1s pausa, 1s concéntrico)
- **rpe_target**: RPE objetivo (escala 1-10, opcional)
- **notes**: Notas específicas de la serie (opcional)

### Días de la Semana
- **weekday**: 1=Lunes, 2=Martes, ..., 7=Domingo
- **has_session**: `true` si hay rutina ese día, `false` si es descanso

## Ejemplos de Implementación

### React Native / JavaScript
```javascript
const API_BASE = 'https://tu-servidor.com/api';

class GymService {
  constructor(token) {
    this.token = token;
  }

  async getMyWeek(date = null) {
    const url = date ? `${API_BASE}/gym/my-week?date=${date}` : `${API_BASE}/gym/my-week`;
    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  }

  async getMyDay(date = null) {
    const url = date ? `${API_BASE}/gym/my-day?date=${date}` : `${API_BASE}/gym/my-day`;
    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.status === 404) {
      return { title: null, exercises: [] };
    }
    
    return response.json();
  }
}
```

### Flutter / Dart
```dart
class GymService {
  final String token;
  final String baseUrl = 'https://tu-servidor.com/api';

  GymService(this.token);

  Future<Map<String, dynamic>> getMyWeek([String? date]) async {
    final url = date != null 
      ? '$baseUrl/gym/my-week?date=$date'
      : '$baseUrl/gym/my-week';
    
    final response = await http.get(
      Uri.parse(url),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    return json.decode(response.body);
  }

  Future<Map<String, dynamic>> getMyDay([String? date]) async {
    final url = date != null 
      ? '$baseUrl/gym/my-day?date=$date'
      : '$baseUrl/gym/my-day';
    
    final response = await http.get(
      Uri.parse(url),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 404) {
      return {'title': null, 'exercises': []};
    }

    return json.decode(response.body);
  }
}
```

## Manejo de Errores

### Errores Comunes
1. **Token expirado (401)**: Renovar token o redirigir a login
2. **Sin conexión**: Implementar retry con backoff exponencial
3. **Sin rutina (404)**: Mostrar mensaje amigable "Sin entrenamiento hoy"

### Estrategia de Cache
- Cachear `my-week` por 1 hora (cambia poco)
- Cachear `my-day` por 30 minutos
- Invalidar cache al cambiar fecha o recibir push notification

## Consideraciones de UX

### Estados de la App
- **Cargando**: Mostrar skeleton/spinner
- **Sin asignación**: "No tienes rutina asignada esta semana"
- **Sin rutina del día**: "Día de descanso" o "Sin entrenamiento hoy"
- **Error de red**: "Revisa tu conexión" con botón reintentar

### Navegación Sugerida
- **Vista semanal**: calendario con días activos/descanso
- **Vista diaria**: lista de ejercicios con sets expandibles
- **Navegación**: swipe entre días, botones prev/next

## Soporte

Para dudas técnicas o reportar problemas:
- Revisar logs de la app con códigos de respuesta HTTP
- Verificar formato de fechas (YYYY-MM-DD)
- Confirmar headers de autenticación
- Contactar al equipo backend con detalles del error
