# Panel de Administración - Setup del Proyecto

## 🚀 **Configuración Inicial del Proyecto**

### **Crear Nuevo Proyecto React + Vite**

```bash
# Crear proyecto base
npm create vite@latest villa-mitre-admin -- --template react-ts
cd villa-mitre-admin
npm install

# Dependencias principales
npm install @tanstack/react-query axios react-router-dom
npm install @headlessui/react @heroicons/react
npm install react-hook-form @hookform/resolvers zod
npm install date-fns clsx tailwind-merge

# Dependencias de desarrollo
npm install -D tailwindcss postcss autoprefixer
npm install -D @types/node
npx tailwindcss init -p
```

### **Estructura de Carpetas Recomendada**

```
villa-mitre-admin/
├── public/
│   ├── favicon.ico
│   └── logo-villa-mitre.png
├── src/
│   ├── components/           # Componentes reutilizables
│   │   ├── ui/              # Componentes base (Button, Input, etc.)
│   │   ├── layout/          # Layout components (Header, Sidebar, etc.)
│   │   ├── forms/           # Formularios específicos
│   │   └── tables/          # Tablas y listados
│   ├── pages/               # Páginas principales
│   │   ├── auth/            # Login, registro
│   │   ├── dashboard/       # Dashboard principal
│   │   ├── gym/             # Gestión gimnasio
│   │   │   ├── exercises/   # CRUD ejercicios
│   │   │   ├── templates/   # Plantillas diarias/semanales
│   │   │   ├── assignments/ # Asignaciones a alumnos
│   │   │   └── reports/     # Reportes y métricas
│   │   └── admin/           # Panel Villa Mitre
│   │       ├── users/       # Gestión usuarios
│   │       ├── professors/  # Asignación profesores
│   │       └── settings/    # Configuración sistema
│   ├── services/            # Servicios API
│   │   ├── api.ts           # Cliente HTTP base
│   │   ├── auth.ts          # Autenticación
│   │   ├── gym.ts           # Servicios gimnasio
│   │   └── admin.ts         # Servicios administración
│   ├── hooks/               # Custom hooks
│   │   ├── useAuth.ts       # Hook autenticación
│   │   ├── useGym.ts        # Hooks gimnasio
│   │   └── useAdmin.ts      # Hooks administración
│   ├── types/               # Definiciones TypeScript
│   │   ├── auth.ts          # Tipos autenticación
│   │   ├── gym.ts           # Tipos gimnasio
│   │   └── admin.ts         # Tipos administración
│   ├── utils/               # Utilidades
│   │   ├── constants.ts     # Constantes
│   │   ├── formatters.ts    # Formateadores
│   │   └── validators.ts    # Validaciones
│   ├── styles/              # Estilos globales
│   │   ├── globals.css      # Estilos base + Tailwind
│   │   └── components.css   # Estilos componentes
│   ├── App.tsx              # Componente principal
│   ├── main.tsx             # Entry point
│   └── vite-env.d.ts        # Tipos Vite
├── .env.example             # Variables de entorno ejemplo
├── .env.local               # Variables de entorno local
├── package.json
├── tailwind.config.js
├── tsconfig.json
└── vite.config.ts
```

## ⚙️ **Configuración de Herramientas**

### **Vite Config (vite.config.ts)**
```typescript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 3001, // Puerto diferente al móvil
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
})
```

### **Tailwind Config (tailwind.config.js)**
```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'villa-mitre': {
          50: '#f0f9ff',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
        },
      },
    },
  },
  plugins: [],
}
```

### **Variables de Entorno (.env.local)**
```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=Villa Mitre Admin
VITE_APP_VERSION=1.0.0
```

## 🔐 **Configuración de Autenticación**

### **Tipos Base (src/types/auth.ts)**
```typescript
export interface User {
  id: number;
  name: string;
  email: string;
  dni: string;
  user_type: 'local' | 'api';
  type_label: string;
  is_professor: boolean;
  avatar_url?: string;
}

export interface LoginCredentials {
  dni: string;
  password: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}
```

### **Cliente API Base (src/services/api.ts)**
```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor para token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor para errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

## 🎨 **Sistema de Diseño**

### **Paleta de Colores Villa Mitre**
- **Primario**: Azul Villa Mitre (#0284c7)
- **Secundario**: Gris neutro (#6b7280)
- **Éxito**: Verde (#10b981)
- **Advertencia**: Amarillo (#f59e0b)
- **Error**: Rojo (#ef4444)

### **Tipografía**
- **Títulos**: Inter Bold
- **Subtítulos**: Inter SemiBold
- **Cuerpo**: Inter Regular
- **Código**: JetBrains Mono

### **Componentes Base Necesarios**
- Button (variants: primary, secondary, danger)
- Input (text, email, password, number)
- Select (simple, multi-select)
- Table (sortable, filterable, paginable)
- Modal (confirmation, forms)
- Toast (notifications)
- Loading (spinner, skeleton)
- Card (content containers)

## 📱 **Responsive Design**

### **Breakpoints**
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### **Layout Adaptativo**
- Mobile: Stack vertical, menú hamburguesa
- Tablet: Sidebar colapsable
- Desktop: Sidebar fijo, múltiples columnas

## 🚀 **Scripts de Desarrollo**

### **Package.json Scripts**
```json
{
  "scripts": {
    "dev": "vite",
    "build": "tsc && vite build",
    "preview": "vite preview",
    "lint": "eslint . --ext ts,tsx --report-unused-disable-directives --max-warnings 0",
    "type-check": "tsc --noEmit"
  }
}
```

## 🔄 **Integración con Backend**

### **Endpoints Principales**
- **Auth**: `/api/auth/login`, `/api/auth/logout`
- **Gym Admin**: `/api/admin/gym/*` (requiere profesor)
- **Villa Mitre Admin**: `/api/admin/users/*` (requiere admin)
- **Usuarios**: `/api/users/*`

### **Manejo de Estados**
- **React Query** para cache y sincronización
- **Context API** para estado global de auth
- **Local Storage** para persistencia de token
