# Compatibilidad Frontend - Configuración XAMPP Local

## 🔄 Cambios de Configuración Requeridos

### 1. **URL Base del API**

**❌ Configuración anterior (Docker):**
```typescript
const API_BASE_URL = 'http://localhost:8080/api';
```

**✅ Nueva configuración (XAMPP):**
```typescript
const API_BASE_URL = 'http://127.0.0.1:8000/api';
// o alternativamente:
const API_BASE_URL = 'http://localhost:8000/api';
```

### 2. **Archivo de Configuración del Frontend**

```typescript
// src/config/api.ts
export const API_CONFIG = {
  BASE_URL: 'http://127.0.0.1:8000/api',
  TIMEOUT: 10000,
  HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
};

// Para desarrollo local con XAMPP
export const STORAGE_BASE_URL = 'http://127.0.0.1:8000/storage';
```

## 🆕 Nuevos Campos Disponibles en la API

### **Campos Críticos Agregados:**

```typescript
export interface UserData {
  // Campos existentes...
  id: number;
  dni: string;
  name: string;
  email?: string;
  
  // 🆕 NUEVOS CAMPOS CRÍTICOS
  barcode?: string;           // ID único para pagos digitales
  saldo?: number;             // Saldo cuenta corriente
  semaforo?: number;          // Estado deuda (1=ok, 99=debe, 10=deuda no exigible)
  foto_url?: string;          // URL completa de imagen de perfil
  
  // 🆕 CAMPOS ADICIONALES
  socio_n?: string;           // Número de socio adicional
  tipo_dni?: string;          // Tipo de documento
  observaciones?: string;     // Observaciones del club
  deuda?: number;             // Monto de deuda específico
  alta?: string;              // Fecha de alta en el club
  suspendido?: boolean;       // Estado de suspensión
  update_ts?: string;         // Última actualización API
}
```

## 🎯 Implementaciones Requeridas

### **1. Utilidad para Estado Financiero**

```typescript
// src/utils/userFinancialStatus.ts
export interface FinancialStatus {
  status: number;
  statusText: string;
  statusColor: string;
  saldo: number;
  saldoText: string;
  hasDebt: boolean;
  canMakePayments: boolean;
}

export const getFinancialStatus = (user: UserData): FinancialStatus => {
  const semaforo = user.semaforo || 1;
  const saldo = user.saldo || 0;
  
  return {
    status: semaforo,
    statusText: getSemaforoText(semaforo),
    statusColor: getSemaforoColor(semaforo),
    saldo: saldo,
    saldoText: formatSaldo(saldo),
    hasDebt: semaforo === 99,
    canMakePayments: Boolean(user.barcode)
  };
};

const getSemaforoText = (semaforo: number): string => {
  switch (semaforo) {
    case 1: return 'Al día';
    case 99: return 'Con deuda exigible';
    case 10: return 'Con deuda no exigible';
    default: return 'Estado desconocido';
  }
};

const getSemaforoColor = (semaforo: number): string => {
  switch (semaforo) {
    case 1: return '#4CAF50';    // Verde
    case 99: return '#F44336';   // Rojo
    case 10: return '#FF9800';   // Naranja
    default: return '#9E9E9E';   // Gris
  }
};

const formatSaldo = (saldo: number): string => {
  if (saldo === 0) return 'Sin deuda';
  if (saldo > 0) return `Saldo a favor: $${saldo.toFixed(2)}`;
  return `Debe: $${Math.abs(saldo).toFixed(2)}`;
};
```

### **2. Componente de Estado Financiero**

```typescript
// src/components/FinancialStatusCard.tsx
import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { getFinancialStatus } from '../utils/userFinancialStatus';
import { UserData } from '../types';

interface Props {
  user: UserData;
  showDetails?: boolean;
}

export const FinancialStatusCard: React.FC<Props> = ({ user, showDetails = true }) => {
  const financial = getFinancialStatus(user);
  
  return (
    <View style={[styles.container, { borderLeftColor: financial.statusColor }]}>
      <View style={styles.header}>
        <Text style={[styles.statusText, { color: financial.statusColor }]}>
          {financial.statusText}
        </Text>
        {financial.hasDebt && (
          <View style={[styles.badge, styles.debtBadge]}>
            <Text style={styles.badgeText}>!</Text>
          </View>
        )}
      </View>
      
      {showDetails && (
        <>
          <Text style={styles.saldoText}>{financial.saldoText}</Text>
          
          {user.barcode && financial.hasDebt && (
            <View style={styles.paymentSection}>
              <Text style={styles.paymentLabel}>Código de pago:</Text>
              <Text style={styles.barcodeText}>{user.barcode}</Text>
            </View>
          )}
        </>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 8,
    borderLeftWidth: 4,
    marginVertical: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  statusText: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  badge: {
    width: 24,
    height: 24,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  debtBadge: {
    backgroundColor: '#F44336',
  },
  badgeText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: 'bold',
  },
  saldoText: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  paymentSection: {
    backgroundColor: '#f5f5f5',
    padding: 12,
    borderRadius: 6,
    marginTop: 8,
  },
  paymentLabel: {
    fontSize: 12,
    color: '#666',
    marginBottom: 4,
  },
  barcodeText: {
    fontSize: 14,
    fontFamily: 'monospace',
    color: '#333',
  },
});
```

### **3. Actualización del Perfil de Usuario**

```typescript
// src/components/UserProfile.tsx - Actualización
import React from 'react';
import { View, Text, Image, StyleSheet } from 'react-native';
import { FinancialStatusCard } from './FinancialStatusCard';
import { UserData } from '../types';

interface Props {
  user: UserData;
}

export const UserProfile: React.FC<Props> = ({ user }) => {
  return (
    <View style={styles.container}>
      {/* Avatar con nueva URL completa */}
      <View style={styles.avatarContainer}>
        {user.foto_url ? (
          <Image 
            source={{ uri: user.foto_url }} 
            style={styles.avatar}
            onError={() => console.log('Error cargando imagen:', user.foto_url)}
          />
        ) : (
          <View style={[styles.avatar, styles.avatarPlaceholder]}>
            <Text style={styles.avatarText}>
              {user.name?.charAt(0)?.toUpperCase() || '?'}
            </Text>
          </View>
        )}
      </View>
      
      {/* Información básica */}
      <Text style={styles.name}>{user.name}</Text>
      <Text style={styles.dni}>DNI: {user.dni}</Text>
      
      {/* 🆕 Estado financiero */}
      <FinancialStatusCard user={user} />
      
      {/* Información adicional */}
      {user.categoria && (
        <Text style={styles.category}>Categoría: {user.categoria}</Text>
      )}
      
      {user.observaciones && (
        <View style={styles.observationsContainer}>
          <Text style={styles.observationsLabel}>Observaciones:</Text>
          <Text style={styles.observationsText}>{user.observaciones}</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 20,
    backgroundColor: '#fff',
  },
  avatarContainer: {
    alignItems: 'center',
    marginBottom: 20,
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
  },
  avatarPlaceholder: {
    backgroundColor: '#e0e0e0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 36,
    color: '#666',
    fontWeight: 'bold',
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 8,
  },
  dni: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
    marginBottom: 20,
  },
  category: {
    fontSize: 16,
    color: '#333',
    marginTop: 16,
  },
  observationsContainer: {
    marginTop: 16,
    padding: 12,
    backgroundColor: '#f9f9f9',
    borderRadius: 8,
  },
  observationsLabel: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#666',
    marginBottom: 4,
  },
  observationsText: {
    fontSize: 14,
    color: '#333',
  },
});
```

## 🔧 Configuración de Desarrollo

### **1. Variables de Entorno**

```bash
# .env.development
REACT_APP_API_BASE_URL=http://127.0.0.1:8000/api
REACT_APP_STORAGE_BASE_URL=http://127.0.0.1:8000/storage
REACT_APP_ENVIRONMENT=development
```

### **2. Configuración de Red (React Native)**

```typescript
// src/config/network.ts
import { Platform } from 'react-native';

export const getApiBaseUrl = (): string => {
  if (__DEV__) {
    // Para desarrollo local con XAMPP
    return Platform.OS === 'android' 
      ? 'http://10.0.2.2:8000/api'  // Android emulator
      : 'http://127.0.0.1:8000/api'; // iOS simulator
  }
  
  // Para producción
  return 'https://tu-dominio.com/api';
};
```

## 🧪 Testing con Datos Reales

### **Usuario de Prueba:**
- **DNI:** `20562964`
- **Nombre:** ADRIAN HERNAN GONZALEZ
- **Barcode:** `73858850140000115123200000008`
- **Saldo:** `0.00`
- **Semáforo:** `1` (al día)

### **Comandos de Testing:**

```bash
# Desde el directorio del proyecto Laravel
C:\xampp\php\php.exe test_registration_flow.php
C:\xampp\php\php.exe test_login_flow.php
C:\xampp\php\php.exe test_api_integration.php
```

## ⚠️ Consideraciones Importantes

### **1. CORS (si es necesario)**
Si el frontend tiene problemas de CORS, verificar en `config/cors.php`:

```php
'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],
```

### **2. Manejo de Errores**

```typescript
// src/utils/apiErrorHandler.ts
export const handleApiError = (error: any) => {
  if (error.response?.status === 401) {
    // Token expirado, redirigir a login
    return 'Sesión expirada';
  }
  
  if (error.code === 'NETWORK_ERROR') {
    return 'Error de conexión. Verificar que el servidor esté ejecutándose en http://127.0.0.1:8000';
  }
  
  return error.message || 'Error desconocido';
};
```

### **3. Compatibilidad hacia Atrás**

```typescript
// Verificar campos antes de usar
const renderFinancialInfo = (user: UserData) => {
  // Solo mostrar si los campos están disponibles
  if (user.semaforo !== undefined || user.saldo !== undefined) {
    return <FinancialStatusCard user={user} />;
  }
  return null;
};
```

## 📋 Checklist de Implementación

- [ ] Actualizar URL base del API a `http://127.0.0.1:8000/api`
- [ ] Agregar nuevos campos a interfaces TypeScript
- [ ] Implementar utilidades de estado financiero
- [ ] Crear componente de estado financiero
- [ ] Actualizar componentes de perfil de usuario
- [ ] Configurar variables de entorno
- [ ] Testear con DNI de prueba `20562964`
- [ ] Verificar carga de imágenes con `foto_url`
- [ ] Implementar manejo de errores de red
- [ ] Documentar cambios para el equipo

## 🚀 Próximos Pasos

1. **Implementar cambios básicos** (URL + interfaces)
2. **Testear conectividad** con servidor XAMPP
3. **Agregar funcionalidades financieras** gradualmente
4. **Validar con datos reales** usando DNI de prueba
5. **Optimizar UX** basado en feedback de usuario
