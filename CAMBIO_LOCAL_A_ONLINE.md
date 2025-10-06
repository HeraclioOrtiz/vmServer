# 🔄 CAMBIO DE DESARROLLO LOCAL A SERVIDOR ONLINE

## 📱 PARA APP MÓVIL

### ANTES (Local):
```javascript
const API_BASE_URL = 'http://localhost:8000';
const API_BASE_URL = 'http://127.0.0.1:8000';
```

### AHORA (Online):
```javascript
const API_BASE_URL = 'https://villamitre.loca.lt';
```

---

## 🖥️ PARA PANEL DE ADMINISTRACIÓN (React/Vue/Angular)

### ANTES (Local):
```javascript
// En tu archivo de configuración
const API_ENDPOINT = 'http://localhost:8000/api';
const BASE_URL = 'http://localhost:8000';
```

### AHORA (Online):
```javascript
// En tu archivo de configuración  
const API_ENDPOINT = 'https://villamitre.loca.lt/api';
const BASE_URL = 'https://villamitre.loca.lt';
```

---

## 🔧 CAMBIOS COMUNES

### 1. **Archivo de configuración (.env, config.js, constants.js):**
```javascript
// CAMBIAR ESTO:
REACT_APP_API_URL=http://localhost:8000
VUE_APP_API_URL=http://localhost:8000

// POR ESTO:
REACT_APP_API_URL=https://villamitre.loca.lt
VUE_APP_API_URL=https://villamitre.loca.lt
```

### 2. **Requests HTTP (axios, fetch):**
```javascript
// ANTES:
axios.defaults.baseURL = 'http://localhost:8000/api';

// AHORA:
axios.defaults.baseURL = 'https://villamitre.loca.lt/api';
```

### 3. **URLs de imágenes/assets:**
```javascript
// ANTES:
const imageUrl = `http://localhost:8000/storage/${image}`;

// AHORA:
const imageUrl = `https://villamitre.loca.lt/storage/${image}`;
```

---

## ⚡ CAMBIO RÁPIDO (1 MINUTO)

**Buscar y reemplazar en todo el proyecto:**

1. **Buscar:** `http://localhost:8000`
2. **Reemplazar:** `https://villamitre.loca.lt`
3. **Guardar y recompilar**

---

## 🎯 VERIFICACIÓN

**Probar estos endpoints:**
- ✅ Login: `https://villamitre.loca.lt/api/auth/login`
- ✅ Datos: `https://villamitre.loca.lt/api/student/my-templates`

**Credenciales de prueba:**
- DNI: `33333333`
- Password: `estudiante123`
