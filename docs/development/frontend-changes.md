# Cambios Requeridos en el Frontend - API de Terceros Actualizada

## Resumen de Cambios en el Backend

El backend ha sido actualizado para manejar la nueva estructura de respuesta de la API de terceros del Club Villa Mitre. Los cambios principales incluyen:

### 1. Nueva Estructura de Respuesta de la API
- **Estado de respuesta**: Solo `estado: "0"` es válido, otros estados contienen errores
- **Nuevos campos**: `barcode`, `saldo`, `semaforo` agregados a todos los usuarios
- **Campo email**: La API usa `mail` en lugar de `email`

### 2. Nuevos Campos de Usuario Disponibles

#### Campos Críticos para el Frontend:
- **`barcode`**: ID único de cobro digital (string) - Se imprime en el carnet
- **`saldo`**: Saldo de cuenta corriente (decimal)
  - `0.00` = Sin deuda
  - `> 0` = Saldo a favor
  - `< 0` = Debe ese monto
- **`semaforo`**: Estado de deuda (integer)
  - `1` = Al día
  - `99` = Con deuda exigible  
  - `10` = Con deuda no exigible
- **`foto_url`**: URL completa de la imagen de perfil (ya implementado)

#### Campos Adicionales Disponibles:
- `socio_n`: Número de socio adicional
- `tipo_dni`: Tipo de documento
- `r1`, `r2`: Campos específicos del club
- `tutor`: Campo tutor
- `observaciones`: Observaciones del socio
- `deuda`: Monto de deuda
- `descuento`: Descuento aplicable
- `alta`: Fecha de alta en el club
- `suspendido`: Estado de suspensión (boolean)
- `facturado`: Estado de facturación (boolean)
- `fecha_baja`: Fecha de baja (si aplica)
- `monto_descuento`: Monto específico de descuento
- `update_ts`: Timestamp de última actualización en la API
- `validmail_st`: Estado de validación de email (boolean)
- `validmail_ts`: Timestamp de validación de email

## Cambios Requeridos en el Frontend

### 1. Actualizar Interfaces TypeScript

```typescript
// src/types/index.ts - Actualizar UserData interface
export interface UserData {
  id: number;
  dni: string;
  name: string;
  nombre?: string;
  apellido?: string;
  email?: string;
  nacionalidad?: string;
  nacimiento?: string;
  domicilio?: string;
  localidad?: string;
  telefono?: string;
  celular?: string;
  categoria?: string;
  socio_id?: string;
  socio_n?: string;
  
  // NUEVOS CAMPOS CRÍTICOS
  barcode?: string;           // ID único de cobro digital
  saldo?: number;             // Saldo cuenta corriente
  semaforo?: number;          // Estado de deuda (1=ok, 99=deuda exigible, 10=deuda no exigible)
  foto_url?: string;          // URL de imagen de perfil
  
  // CAMPOS ADICIONALES OPCIONALES
  tipo_dni?: string;
  r1?: string;
  r2?: string;
  tutor?: string;
  observaciones?: string;
  deuda?: number;
  descuento?: number;
  alta?: string;
  suspendido?: boolean;
  facturado?: boolean;
  fecha_baja?: string;
  monto_descuento?: number;
  update_ts?: string;
  validmail_st?: boolean;
  validmail_ts?: string;
  
  // Campos existentes
  user_type?: string;
  estado_socio?: string;
  avatar_path?: string;
  api_updated_at?: string;
}
```

### 2. Implementar Lógica de Estado de Deuda

```typescript
// src/utils/userStatus.ts - Nuevo archivo
export const getUserDebtStatus = (user: UserData) => {
  const semaforo = user.semaforo || 1;
  const saldo = user.saldo || 0;
  
  return {
    status: semaforo,
    statusText: getSemaforoText(semaforo),
    saldo: saldo,
    saldoText: getSaldoText(saldo),
    hasDebt: semaforo === 99,
    hasNonExigibleDebt: semaforo === 10,
    isUpToDate: semaforo === 1
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

const getSaldoText = (saldo: number): string => {
  if (saldo === 0) return 'Sin deuda';
  if (saldo > 0) return `Saldo a favor: $${saldo.toFixed(2)}`;
  return `Debe: $${Math.abs(saldo).toFixed(2)}`;
};
```

### 3. Actualizar Componentes de Perfil de Usuario

```typescript
// src/components/UserProfile.tsx - Ejemplo de uso
import { getUserDebtStatus } from '../utils/userStatus';

const UserProfile: React.FC<{ user: UserData }> = ({ user }) => {
  const debtStatus = getUserDebtStatus(user);
  
  return (
    <View style={styles.container}>
      {/* Imagen de perfil - ahora con foto_url */}
      {user.foto_url && (
        <Image 
          source={{ uri: user.foto_url }} 
          style={styles.avatar}
        />
      )}
      
      {/* Información básica */}
      <Text style={styles.name}>{user.name}</Text>
      <Text style={styles.dni}>DNI: {user.dni}</Text>
      
      {/* NUEVA SECCIÓN: Estado financiero */}
      <View style={styles.financialStatus}>
        <Text style={[
          styles.statusText, 
          debtStatus.hasDebt ? styles.debtText : styles.okText
        ]}>
          {debtStatus.statusText}
        </Text>
        <Text style={styles.saldoText}>
          {debtStatus.saldoText}
        </Text>
      </View>
      
      {/* NUEVA SECCIÓN: Código de barras para pagos */}
      {user.barcode && (
        <View style={styles.barcodeSection}>
          <Text style={styles.barcodeLabel}>Código de pago:</Text>
          <Text style={styles.barcodeText}>{user.barcode}</Text>
        </View>
      )}
      
      {/* Resto de información del usuario */}
    </View>
  );
};
```

### 4. Actualizar Pantallas de Autenticación

```typescript
// src/screens/LoginScreen.tsx y RegisterScreen.tsx
// Verificar que manejen correctamente el campo foto_url

const handleLoginSuccess = (userData: UserData) => {
  // El foto_url ahora debería estar disponible inmediatamente
  console.log('Usuario logueado con imagen:', userData.foto_url);
  
  // Verificar estado de deuda si es necesario
  const debtStatus = getUserDebtStatus(userData);
  if (debtStatus.hasDebt) {
    // Mostrar notificación de deuda si es necesario
    showDebtNotification(debtStatus);
  }
  
  // Continuar con navegación normal
  navigation.navigate('Home');
};
```

### 5. Nuevas Funcionalidades Posibles

#### A. Indicador de Estado Financiero
```typescript
// Componente para mostrar estado financiero en la UI
const FinancialStatusIndicator: React.FC<{ user: UserData }> = ({ user }) => {
  const status = getUserDebtStatus(user);
  
  return (
    <View style={[styles.indicator, getStatusColor(status.status)]}>
      <Icon name={getStatusIcon(status.status)} />
      <Text>{status.statusText}</Text>
    </View>
  );
};
```

#### B. Pantalla de Código de Barras
```typescript
// Nueva pantalla para mostrar código de barras para pagos
const PaymentCodeScreen: React.FC = () => {
  const { user } = useAuth();
  
  return (
    <View style={styles.container}>
      <Text style={styles.title}>Código de Pago</Text>
      {user.barcode && (
        <>
          <BarcodeGenerator value={user.barcode} />
          <Text style={styles.barcodeText}>{user.barcode}</Text>
          <Text style={styles.instructions}>
            Presenta este código en los puntos de pago autorizados
          </Text>
        </>
      )}
    </View>
  );
};
```

### 6. Validaciones y Manejo de Errores

```typescript
// src/utils/validation.ts
export const validateUserData = (userData: UserData): boolean => {
  // Validar campos críticos
  if (!userData.dni || !userData.name) {
    return false;
  }
  
  // Validar semáforo si está presente
  if (userData.semaforo && ![1, 10, 99].includes(userData.semaforo)) {
    console.warn('Semáforo con valor inesperado:', userData.semaforo);
  }
  
  return true;
};
```

## Consideraciones de Implementación

### 1. Compatibilidad hacia atrás
- Los campos nuevos son opcionales para mantener compatibilidad
- Verificar siempre la existencia de campos antes de usarlos
- Mantener fallbacks para usuarios sin datos completos

### 2. Manejo de Estados
- Implementar loading states para la descarga de imágenes
- Manejar casos donde la imagen no esté disponible
- Considerar cache local para imágenes de perfil

### 3. UX/UI
- Usar colores distintivos para diferentes estados de semáforo
- Mostrar información financiera de manera clara pero no intrusiva
- Considerar notificaciones push para cambios de estado de deuda

### 4. Testing
- Probar con usuarios que tengan diferentes estados de semáforo
- Verificar manejo de usuarios sin barcode o con saldo 0
- Testear carga de imágenes en diferentes condiciones de red

## Próximos Pasos Recomendados

1. **Actualizar interfaces TypeScript** con los nuevos campos
2. **Implementar utilidades** para manejo de estado financiero
3. **Actualizar componentes** de perfil de usuario
4. **Agregar funcionalidades** de código de barras si es necesario
5. **Testear integración** con datos reales del DNI 20562964
6. **Documentar cambios** en la documentación del proyecto

## Datos de Prueba

Usar el DNI `20562964` para testing, que debería devolver:
- Nombre: ADRIAN HERNAN GONZALEZ
- Barcode: 73858850140000115123200000008
- Saldo: 0.00
- Semáforo: 1 (al día)
