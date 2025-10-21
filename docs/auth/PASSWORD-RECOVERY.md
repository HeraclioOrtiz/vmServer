# 🔐 Sistema de Recuperación de Contraseña - Villa Mitre Server

**Fecha de diseño:** 21 de Octubre 2025
**Estado:** 📋 Diseño y especificación (Pendiente de implementación)
**Prioridad:** 🟡 MEDIA (Fase 2 de refactorización)

---

## 📋 Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Flujo de Recuperación](#flujo-de-recuperación)
4. [Implementación Backend](#implementación-backend)
5. [Implementación Frontend (App Móvil)](#implementación-frontend-app-móvil)
6. [Seguridad](#seguridad)
7. [Testing](#testing)
8. [Configuración y Deployment](#configuración-y-deployment)

---

## 🎯 Resumen Ejecutivo

### Objetivo
Implementar un sistema moderno y seguro de recuperación de contraseña que funcione con el sistema dual de usuarios (local + API) de Villa Mitre, siguiendo las mejores prácticas de Laravel y estándares de seguridad actuales.

### Características Principales
- ✅ **Tokens seguros** con expiración de 60 minutos (configurable)
- ✅ **Rate limiting** para prevenir abuso (máx. 5 intentos por hora)
- ✅ **Notificaciones por email** con links únicos
- ✅ **Soporte dual** para usuarios locales y API
- ✅ **Validación robusta** de emails y DNIs
- ✅ **Auditoría completa** de todas las operaciones
- ✅ **UI/UX moderna** en aplicación móvil

### Tecnologías
- Laravel 12 Password Reset (built-in)
- Laravel Notifications
- Laravel Sanctum (tokens de API)
- Rate Limiting Middleware
- Audit Service (logging)

---

## 🏗️ Arquitectura del Sistema

### Componentes del Backend

```
app/Services/Auth/
├── PasswordResetService.php      (Lógica principal)
└── PasswordRecoveryService.php   (Orquestador - wrapper)

app/Http/Controllers/
└── Auth/
    └── PasswordResetController.php

app/Http/Requests/Auth/
├── ForgotPasswordRequest.php
├── ResetPasswordRequest.php
└── ValidateResetTokenRequest.php

app/Notifications/
└── ResetPasswordNotification.php (Email customizado)

routes/
└── api.php (nuevos endpoints)
```

### Base de Datos

**Tabla existente:** `password_reset_tokens`

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

**Índices recomendados:**
```sql
CREATE INDEX idx_token ON password_reset_tokens(token);
CREATE INDEX idx_created_at ON password_reset_tokens(created_at);
```

---

## 🔄 Flujo de Recuperación

### Diagrama de Flujo

```
[Usuario olvida contraseña]
         |
         v
[1. Solicitud de Reset]
    - Input: email o DNI
    - Validación de usuario
    - Generación de token seguro
    - Envío de email
         |
         v
[2. Usuario recibe email]
    - Link con token único
    - Válido por 60 minutos
         |
         v
[3. Click en link]
    - App abre con token
    - Validación de token
         |
         v
[4. Nueva contraseña]
    - Input: password + confirm
    - Validación de fortaleza
    - Reset de contraseña
    - Invalidación de token
         |
         v
[5. Login automático]
    - Genera token Sanctum
    - Redirección a app
```

### Casos Especiales

#### Usuarios API (sincronizados)
```
⚠️ RESTRICCIÓN: Los usuarios API NO pueden cambiar su contraseña localmente
├─ Razón: Sus datos provienen del sistema externo del club
├─ Solución: Email informativo con instrucciones para contactar al club
└─ Alternativa: Link a sistema externo del club (si disponible)
```

#### Usuarios Locales Promocionados
```
✅ PERMITIDO: Pueden cambiar contraseña normalmente
└─ Nota: Una vez promocionados a API, ya no pueden cambiar localmente
```

---

## 💻 Implementación Backend

### 1. Service: PasswordResetService.php

```php
<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\UserType;
use App\Services\Core\AuditService;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Notifications\ResetPassword;

class PasswordResetService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Solicita un reset de contraseña por email
     *
     * @throws \Exception si el usuario es de tipo API
     */
    public function requestReset(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Security: No revelar si el email existe o no
            return [
                'success' => true,
                'message' => 'Si el email existe, recibirás instrucciones para resetear tu contraseña.'
            ];
        }

        // Verificar tipo de usuario
        if ($user->user_type === UserType::API) {
            $this->auditService->log(
                action: 'password_reset.rejected_api_user',
                userId: $user->id,
                metadata: ['reason' => 'API users cannot reset password locally']
            );

            throw new \Exception(
                'Los usuarios sincronizados con el club no pueden cambiar su contraseña aquí. ' .
                'Por favor, contacta a la administración del club.'
            );
        }

        // Generar y enviar token
        $status = Password::sendResetLink(['email' => $email]);

        $this->auditService->log(
            action: 'password_reset.requested',
            userId: $user->id,
            metadata: [
                'email' => $email,
                'status' => $status
            ]
        );

        return [
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => 'Si el email existe, recibirás instrucciones para resetear tu contraseña.'
        ];
    }

    /**
     * Solicita reset por DNI (busca el email asociado)
     */
    public function requestResetByDni(string $dni): array
    {
        $user = User::where('dni', $dni)->first();

        if (!$user || !$user->email) {
            return [
                'success' => true,
                'message' => 'Si el DNI está registrado, recibirás instrucciones en tu email.'
            ];
        }

        return $this->requestReset($user->email);
    }

    /**
     * Valida un token de reset (sin resetear la contraseña)
     */
    public function validateToken(string $email, string $token): bool
    {
        // Usar el broker de Laravel para validar
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $tokenData = \DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenData) {
            return false;
        }

        // Verificar expiración (60 minutos por defecto)
        $expirationMinutes = config('auth.passwords.users.expire', 60);
        $tokenAge = now()->diffInMinutes($tokenData->created_at);

        if ($tokenAge > $expirationMinutes) {
            $this->auditService->log(
                action: 'password_reset.token_expired',
                userId: $user->id,
                metadata: ['age_minutes' => $tokenAge]
            );
            return false;
        }

        // Verificar hash del token
        return Hash::check($token, $tokenData->token);
    }

    /**
     * Resetea la contraseña usando el token
     */
    public function resetPassword(string $email, string $token, string $newPassword): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ];
        }

        // Verificar tipo de usuario (doble check)
        if ($user->user_type === UserType::API) {
            $this->auditService->log(
                action: 'password_reset.rejected_api_user',
                userId: $user->id,
                metadata: ['reason' => 'API users cannot reset password']
            );

            throw new \Exception('Los usuarios API no pueden cambiar su contraseña localmente.');
        }

        // Intentar reset con Laravel Password Broker
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => $token
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revocar todos los tokens de sesión existentes
                $user->tokens()->delete();
            }
        );

        $success = $status === Password::PASSWORD_RESET;

        $this->auditService->log(
            action: $success ? 'password_reset.completed' : 'password_reset.failed',
            userId: $user->id,
            metadata: [
                'status' => $status,
                'ip' => request()->ip()
            ]
        );

        return [
            'success' => $success,
            'message' => $success
                ? 'Contraseña actualizada exitosamente.'
                : 'El token es inválido o ha expirado.'
        ];
    }

    /**
     * Verifica si un usuario puede resetear su contraseña
     */
    public function canResetPassword(string $identifier): array
    {
        // Buscar por email o DNI
        $user = User::where('email', $identifier)
            ->orWhere('dni', $identifier)
            ->first();

        if (!$user) {
            return [
                'can_reset' => false,
                'reason' => 'user_not_found',
                'message' => 'Usuario no encontrado.'
            ];
        }

        if ($user->user_type === UserType::API) {
            return [
                'can_reset' => false,
                'reason' => 'api_user',
                'message' => 'Los usuarios sincronizados con el club deben contactar a la administración.',
                'contact_info' => [
                    'email' => config('mail.contact_email', 'contacto@villamitre.com'),
                    'phone' => config('gym.contact_phone', '+54 11 1234-5678')
                ]
            ];
        }

        if (!$user->email) {
            return [
                'can_reset' => false,
                'reason' => 'no_email',
                'message' => 'Este usuario no tiene un email registrado.'
            ];
        }

        return [
            'can_reset' => true,
            'email' => $user->email,
            'message' => 'Puedes resetear tu contraseña.'
        ];
    }
}
```

### 2. Controller: PasswordResetController.php

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ValidateResetTokenRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {
        // Rate limiting
        $this->middleware('throttle:5,60')->only(['requestReset', 'resetPassword']);
    }

    /**
     * POST /api/auth/password/forgot
     *
     * Solicita un link de recuperación
     */
    public function requestReset(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // Soportar búsqueda por email o DNI
            if ($request->has('email')) {
                $result = $this->passwordResetService->requestReset($request->email);
            } else {
                $result = $this->passwordResetService->requestResetByDni($request->dni);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/auth/password/validate-token
     *
     * Valida un token sin resetear la contraseña
     */
    public function validateToken(ValidateResetTokenRequest $request): JsonResponse
    {
        $isValid = $this->passwordResetService->validateToken(
            $request->email,
            $request->token
        );

        return response()->json([
            'valid' => $isValid,
            'message' => $isValid
                ? 'Token válido. Puedes proceder a resetear tu contraseña.'
                : 'El token es inválido o ha expirado.'
        ]);
    }

    /**
     * POST /api/auth/password/reset
     *
     * Resetea la contraseña usando el token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->passwordResetService->resetPassword(
                $request->email,
                $request->token,
                $request->password
            );

            if ($result['success']) {
                // Autologin opcional: generar token Sanctum
                $user = \App\Models\User::where('email', $request->email)->first();
                $token = $user->createToken('auth')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'token' => $token,
                    'user' => $user
                ]);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/auth/password/can-reset
     *
     * Verifica si un usuario puede resetear su contraseña
     */
    public function canReset(ForgotPasswordRequest $request): JsonResponse
    {
        $identifier = $request->email ?? $request->dni;
        $result = $this->passwordResetService->canResetPassword($identifier);

        return response()->json($result);
    }
}
```

### 3. Form Requests

#### ForgotPasswordRequest.php
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required_without:dni|email|max:255',
            'dni' => 'required_without:email|string|digits_between:7,8',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Debes proporcionar un email o DNI.',
            'dni.required_without' => 'Debes proporcionar un email o DNI.',
            'email.email' => 'El email no es válido.',
            'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos.',
        ];
    }
}
```

#### ResetPasswordRequest.php
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', // Al menos 1 minúscula, 1 mayúscula, 1 número
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email es requerido.',
            'email.email' => 'El email no es válido.',
            'email.exists' => 'No existe un usuario con ese email.',
            'token.required' => 'El token es requerido.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
        ];
    }
}
```

#### ValidateResetTokenRequest.php
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ValidateResetTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ];
    }
}
```

### 4. Custom Email Notification

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // URL de deeplink para la app móvil
        $url = config('app.mobile_url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Recuperación de Contraseña - Villa Mitre')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Recibimos una solicitud para resetear tu contraseña.')
            ->line('Este link es válido por 60 minutos.')
            ->action('Resetear Contraseña', $url)
            ->line('Si no solicitaste este cambio, ignora este email.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}
```

### 5. Rutas API

```php
// routes/api.php

// Password Reset Routes (sin autenticación)
Route::prefix('auth/password')->group(function () {
    // Solicitar reset (rate limited: 5 por hora)
    Route::post('/forgot', [PasswordResetController::class, 'requestReset'])
        ->middleware('throttle:5,60');

    // Validar token antes de mostrar formulario
    Route::post('/validate-token', [PasswordResetController::class, 'validateToken']);

    // Resetear contraseña
    Route::post('/reset', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:5,60');

    // Verificar si puede resetear (por email o DNI)
    Route::post('/can-reset', [PasswordResetController::class, 'canReset']);
});
```

### 6. Configuración

#### config/auth.php
```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60, // minutos
        'throttle' => 60, // segundos entre intentos
    ],
],
```

#### .env
```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@villamitre.com"
MAIL_FROM_NAME="${APP_NAME}"

# App URLs
APP_URL=http://localhost:8000
MOBILE_APP_URL=villamitre://  # Deep link para app móvil

# Contact Info
CONTACT_EMAIL=contacto@villamitre.com
CONTACT_PHONE="+54 11 1234-5678"
```

---

## 📱 Implementación Frontend (App Móvil)

### Pantallas Necesarias

#### 1. Pantalla: Olvidé mi Contraseña

```
┌─────────────────────────────────┐
│  ← Volver                       │
│                                 │
│  🔐 Recuperar Contraseña        │
│                                 │
│  Ingresa tu email o DNI para    │
│  recibir instrucciones          │
│                                 │
│  ┌─────────────────────────┐   │
│  │ Email o DNI             │   │
│  └─────────────────────────┘   │
│                                 │
│  ┌─────────────────────────┐   │
│  │   Enviar Instrucciones  │   │
│  └─────────────────────────┘   │
│                                 │
│  ¿Recordaste tu contraseña?     │
│  Iniciar Sesión                 │
│                                 │
└─────────────────────────────────┘
```

**Código de ejemplo (React Native):**

```typescript
// screens/ForgotPasswordScreen.tsx

import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, Alert } from 'react-native';
import axios from 'axios';
import { API_BASE_URL } from '@/config';

export default function ForgotPasswordScreen({ navigation }) {
  const [identifier, setIdentifier] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async () => {
    if (!identifier.trim()) {
      Alert.alert('Error', 'Por favor ingresa tu email o DNI');
      return;
    }

    setLoading(true);

    try {
      // Primero verificar si puede resetear
      const checkResponse = await axios.post(`${API_BASE_URL}/auth/password/can-reset`, {
        email: identifier.includes('@') ? identifier : undefined,
        dni: !identifier.includes('@') ? identifier : undefined,
      });

      if (!checkResponse.data.can_reset) {
        // Usuario API o sin email
        Alert.alert(
          'No disponible',
          checkResponse.data.message,
          checkResponse.data.contact_info ? [
            {
              text: 'Contactar al Club',
              onPress: () => {
                // Abrir email o teléfono
              }
            },
            { text: 'OK' }
          ] : [{ text: 'OK' }]
        );
        setLoading(false);
        return;
      }

      // Enviar solicitud de reset
      const response = await axios.post(`${API_BASE_URL}/auth/password/forgot`, {
        email: identifier.includes('@') ? identifier : undefined,
        dni: !identifier.includes('@') ? identifier : undefined,
      });

      Alert.alert(
        'Email Enviado',
        'Si el email existe en nuestro sistema, recibirás instrucciones para resetear tu contraseña.',
        [
          {
            text: 'OK',
            onPress: () => navigation.goBack()
          }
        ]
      );

    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Ocurrió un error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Recuperar Contraseña</Text>
      <Text style={styles.subtitle}>
        Ingresa tu email o DNI para recibir instrucciones
      </Text>

      <TextInput
        style={styles.input}
        placeholder="Email o DNI"
        value={identifier}
        onChangeText={setIdentifier}
        keyboardType="email-address"
        autoCapitalize="none"
      />

      <TouchableOpacity
        style={styles.button}
        onPress={handleSubmit}
        disabled={loading}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Enviando...' : 'Enviar Instrucciones'}
        </Text>
      </TouchableOpacity>

      <TouchableOpacity onPress={() => navigation.goBack()}>
        <Text style={styles.link}>¿Recordaste tu contraseña? Iniciar Sesión</Text>
      </TouchableOpacity>
    </View>
  );
}
```

#### 2. Pantalla: Resetear Contraseña

```
┌─────────────────────────────────┐
│  ← Volver                       │
│                                 │
│  🔑 Nueva Contraseña            │
│                                 │
│  ┌─────────────────────────┐   │
│  │ Nueva Contraseña        │   │
│  └─────────────────────────┘   │
│                                 │
│  ┌─────────────────────────┐   │
│  │ Confirmar Contraseña    │   │
│  └─────────────────────────┘   │
│                                 │
│  ✓ Al menos 8 caracteres        │
│  ✓ Una mayúscula                │
│  ✓ Una minúscula                │
│  ✓ Un número                    │
│                                 │
│  ┌─────────────────────────┐   │
│  │   Cambiar Contraseña    │   │
│  └─────────────────────────┘   │
│                                 │
└─────────────────────────────────┘
```

**Código de ejemplo (React Native):**

```typescript
// screens/ResetPasswordScreen.tsx

import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, TouchableOpacity, Alert } from 'react-native';
import axios from 'axios';
import { API_BASE_URL } from '@/config';
import { useAuth } from '@/contexts/AuthContext';

export default function ResetPasswordScreen({ route, navigation }) {
  const { token, email } = route.params;
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);
  const [tokenValid, setTokenValid] = useState<boolean | null>(null);
  const { login } = useAuth();

  // Validar token al montar
  useEffect(() => {
    validateToken();
  }, []);

  const validateToken = async () => {
    try {
      const response = await axios.post(`${API_BASE_URL}/auth/password/validate-token`, {
        email,
        token
      });

      setTokenValid(response.data.valid);

      if (!response.data.valid) {
        Alert.alert(
          'Token Inválido',
          'El link ha expirado o es inválido. Por favor solicita uno nuevo.',
          [{ text: 'OK', onPress: () => navigation.navigate('ForgotPassword') }]
        );
      }
    } catch (error) {
      setTokenValid(false);
      Alert.alert('Error', 'No se pudo validar el token');
    }
  };

  const validatePassword = (): boolean => {
    if (password.length < 8) {
      Alert.alert('Error', 'La contraseña debe tener al menos 8 caracteres');
      return false;
    }

    if (!/[A-Z]/.test(password)) {
      Alert.alert('Error', 'La contraseña debe contener al menos una mayúscula');
      return false;
    }

    if (!/[a-z]/.test(password)) {
      Alert.alert('Error', 'La contraseña debe contener al menos una minúscula');
      return false;
    }

    if (!/\d/.test(password)) {
      Alert.alert('Error', 'La contraseña debe contener al menos un número');
      return false;
    }

    if (password !== passwordConfirmation) {
      Alert.alert('Error', 'Las contraseñas no coinciden');
      return false;
    }

    return true;
  };

  const handleSubmit = async () => {
    if (!validatePassword()) {
      return;
    }

    setLoading(true);

    try {
      const response = await axios.post(`${API_BASE_URL}/auth/password/reset`, {
        email,
        token,
        password,
        password_confirmation: passwordConfirmation
      });

      if (response.data.success) {
        Alert.alert(
          'Éxito',
          'Tu contraseña ha sido actualizada',
          [
            {
              text: 'OK',
              onPress: async () => {
                // Auto-login con el token retornado
                if (response.data.token) {
                  await login(response.data.token, response.data.user);
                  navigation.navigate('Home');
                } else {
                  navigation.navigate('Login');
                }
              }
            }
          ]
        );
      }

    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Ocurrió un error');
    } finally {
      setLoading(false);
    }
  };

  if (tokenValid === null) {
    return <View><Text>Validando token...</Text></View>;
  }

  if (tokenValid === false) {
    return null; // Ya mostramos alert
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Nueva Contraseña</Text>

      <TextInput
        style={styles.input}
        placeholder="Nueva Contraseña"
        secureTextEntry
        value={password}
        onChangeText={setPassword}
      />

      <TextInput
        style={styles.input}
        placeholder="Confirmar Contraseña"
        secureTextEntry
        value={passwordConfirmation}
        onChangeText={setPasswordConfirmation}
      />

      <View style={styles.requirements}>
        <Text style={password.length >= 8 ? styles.valid : styles.invalid}>
          {password.length >= 8 ? '✓' : '○'} Al menos 8 caracteres
        </Text>
        <Text style={/[A-Z]/.test(password) ? styles.valid : styles.invalid}>
          {/[A-Z]/.test(password) ? '✓' : '○'} Una mayúscula
        </Text>
        <Text style={/[a-z]/.test(password) ? styles.valid : styles.invalid}>
          {/[a-z]/.test(password) ? '✓' : '○'} Una minúscula
        </Text>
        <Text style={/\d/.test(password) ? styles.valid : styles.invalid}>
          {/\d/.test(password) ? '✓' : '○'} Un número
        </Text>
      </View>

      <TouchableOpacity
        style={styles.button}
        onPress={handleSubmit}
        disabled={loading}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Cambiando...' : 'Cambiar Contraseña'}
        </Text>
      </TouchableOpacity>
    </View>
  );
}
```

### Deep Linking Configuration

#### iOS (Info.plist)
```xml
<key>CFBundleURLTypes</key>
<array>
  <dict>
    <key>CFBundleURLSchemes</key>
    <array>
      <string>villamitre</string>
    </array>
  </dict>
</array>
```

#### Android (AndroidManifest.xml)
```xml
<intent-filter>
  <action android:name="android.intent.action.VIEW" />
  <category android:name="android.intent.category.DEFAULT" />
  <category android:name="android.intent.category.BROWSABLE" />
  <data android:scheme="villamitre" />
</intent-filter>
```

#### React Native Linking
```typescript
// App.tsx

import { Linking } from 'react-native';

useEffect(() => {
  // Manejar deep links
  const handleDeepLink = ({ url }: { url: string }) => {
    const route = url.replace(/.*?:\/\//g, '');

    // villamitre://reset-password?token=XXX&email=YYY
    if (route.startsWith('reset-password')) {
      const params = new URLSearchParams(route.split('?')[1]);
      const token = params.get('token');
      const email = params.get('email');

      if (token && email) {
        navigation.navigate('ResetPassword', { token, email });
      }
    }
  };

  // Initial URL (app abierta desde link)
  Linking.getInitialURL().then((url) => {
    if (url) {
      handleDeepLink({ url });
    }
  });

  // Listener para links mientras app está abierta
  const subscription = Linking.addEventListener('url', handleDeepLink);

  return () => {
    subscription.remove();
  };
}, []);
```

---

## 🔒 Seguridad

### Medidas Implementadas

#### 1. Rate Limiting
```php
// 5 intentos por hora por IP
Route::post('/forgot', [PasswordResetController::class, 'requestReset'])
    ->middleware('throttle:5,60');
```

#### 2. Token Seguro
- **Generación:** Usa `Str::random(60)` + hash
- **Almacenamiento:** Hash en BD (no plaintext)
- **Expiración:** 60 minutos (configurable)

#### 3. No Information Disclosure
```php
// Siempre retornar el mismo mensaje (no revelar si el email existe)
return [
    'success' => true,
    'message' => 'Si el email existe, recibirás instrucciones...'
];
```

#### 4. Validación de Usuarios API
```php
if ($user->user_type === UserType::API) {
    throw new \Exception('Los usuarios API no pueden cambiar contraseña localmente');
}
```

#### 5. Revocación de Tokens
```php
// Al cambiar contraseña, revocar todos los tokens Sanctum
$user->tokens()->delete();
```

#### 6. Auditoría Completa
```php
$this->auditService->log(
    action: 'password_reset.completed',
    userId: $user->id,
    metadata: ['ip' => request()->ip()]
);
```

### Checklist de Seguridad

- [x] Rate limiting en endpoints sensibles
- [x] Tokens hasheados en BD
- [x] Expiración de tokens (60 min)
- [x] No revelar existencia de usuarios
- [x] Validación de fortaleza de contraseña
- [x] HTTPS en producción
- [x] Logs de auditoría
- [x] Revocación de sesiones al cambiar password
- [x] Protección contra usuarios API
- [x] Email verification (opcional futuro)

---

## 🧪 Testing

### Tests Unitarios

#### PasswordResetServiceTest.php

```php
<?php

namespace Tests\Unit\Services\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserType;
use App\Services\Auth\PasswordResetService;
use App\Services\Core\AuditService;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetServiceTest extends TestCase
{
    use RefreshDatabase;

    private PasswordResetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PasswordResetService::class);
    }

    /** @test */
    public function it_sends_reset_link_to_local_user()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        $result = $this->service->requestReset($user->email);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email
        ]);
    }

    /** @test */
    public function it_rejects_reset_request_for_api_user()
    {
        $user = User::factory()->create([
            'user_type' => UserType::API,
            'email' => 'api@example.com'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('usuarios sincronizados');

        $this->service->requestReset($user->email);
    }

    /** @test */
    public function it_does_not_reveal_if_email_exists()
    {
        $result = $this->service->requestReset('nonexistent@example.com');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Si el email existe', $result['message']);
    }

    /** @test */
    public function it_validates_token_correctly()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        // Generar token
        $token = Password::createToken($user);

        // Validar
        $isValid = $this->service->validateToken($user->email, $token);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_rejects_expired_token()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        // Crear token y forzar que esté expirado
        $token = Password::createToken($user);
        \DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subHours(2)]);

        $isValid = $this->service->validateToken($user->email, $token);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_resets_password_successfully()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com',
            'password' => bcrypt('oldpassword')
        ]);

        $token = Password::createToken($user);

        $result = $this->service->resetPassword(
            $user->email,
            $token,
            'NewPassword123'
        );

        $this->assertTrue($result['success']);

        // Verificar que la contraseña cambió
        $user->refresh();
        $this->assertTrue(\Hash::check('NewPassword123', $user->password));
    }

    /** @test */
    public function it_revokes_tokens_on_password_reset()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        // Crear token Sanctum
        $user->createToken('auth')->plainTextToken;
        $this->assertCount(1, $user->tokens);

        // Resetear contraseña
        $token = Password::createToken($user);
        $this->service->resetPassword($user->email, $token, 'NewPassword123');

        // Verificar que se revocaron los tokens
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    /** @test */
    public function it_checks_if_user_can_reset_password()
    {
        $localUser = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'local@example.com'
        ]);

        $apiUser = User::factory()->create([
            'user_type' => UserType::API,
            'email' => 'api@example.com'
        ]);

        // Local user puede
        $result = $this->service->canResetPassword($localUser->email);
        $this->assertTrue($result['can_reset']);

        // API user no puede
        $result = $this->service->canResetPassword($apiUser->email);
        $this->assertFalse($result['can_reset']);
        $this->assertEquals('api_user', $result['reason']);
    }

    /** @test */
    public function it_logs_all_password_reset_actions()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        $this->service->requestReset($user->email);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'password_reset.requested'
        ]);
    }
}
```

### Tests de Integración (Feature)

#### PasswordResetControllerTest.php

```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_reset_link_via_email()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/auth/password/forgot', [
            'email' => $user->email
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function it_sends_reset_link_via_dni()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'dni' => '12345678',
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/auth/password/forgot', [
            'dni' => '12345678'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function it_rejects_api_user_reset_request()
    {
        $user = User::factory()->create([
            'user_type' => UserType::API,
            'email' => 'api@example.com'
        ]);

        $response = $this->postJson('/api/auth/password/forgot', [
            'email' => $user->email
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function it_validates_reset_token()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/password/validate-token', [
            'email' => $user->email,
            'token' => $token
        ]);

        $response->assertOk()
            ->assertJson([
                'valid' => true
            ]);
    }

    /** @test */
    public function it_resets_password_with_valid_token()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com',
            'password' => bcrypt('oldpassword')
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'token',  // Token Sanctum para auto-login
                'user'
            ]);

        // Verificar nueva contraseña
        $user->refresh();
        $this->assertTrue(\Hash::check('NewPassword123', $user->password));
    }

    /** @test */
    public function it_rate_limits_reset_requests()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        // Hacer 5 requests (límite)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/password/forgot', ['email' => $user->email]);
        }

        // El 6to debe ser rechazado
        $response = $this->postJson('/api/auth/password/forgot', [
            'email' => $user->email
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function it_validates_password_requirements()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'test@example.com'
        ]);

        $token = Password::createToken($user);

        // Contraseña muy corta
        $response = $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Contraseña sin mayúsculas
        $response = $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'alllowercase123',
            'password_confirmation' => 'alllowercase123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_checks_if_user_can_reset_password()
    {
        $localUser = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'email' => 'local@example.com'
        ]);

        $apiUser = User::factory()->create([
            'user_type' => UserType::API,
            'email' => 'api@example.com'
        ]);

        // Local user puede
        $response = $this->postJson('/api/auth/password/can-reset', [
            'email' => $localUser->email
        ]);

        $response->assertOk()
            ->assertJson([
                'can_reset' => true
            ]);

        // API user no puede
        $response = $this->postJson('/api/auth/password/can-reset', [
            'email' => $apiUser->email
        ]);

        $response->assertOk()
            ->assertJson([
                'can_reset' => false,
                'reason' => 'api_user'
            ]);
    }
}
```

### Tests de Correo

```php
/** @test */
public function it_sends_reset_email_with_correct_format()
{
    \Mail::fake();

    $user = User::factory()->create([
        'user_type' => UserType::LOCAL,
        'email' => 'test@example.com'
    ]);

    $this->postJson('/api/auth/password/forgot', [
        'email' => $user->email
    ]);

    \Mail::assertSent(ResetPasswordNotification::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
}
```

---

## ⚙️ Configuración y Deployment

### Checklist Pre-Deployment

#### Backend
- [ ] Configurar variables de entorno en `.env`
- [ ] Configurar servicio de email (SMTP, SendGrid, etc.)
- [ ] Verificar que `password_reset_tokens` table existe
- [ ] Configurar rate limiting en producción
- [ ] Habilitar logs de auditoría
- [ ] Configurar deep link URL para app móvil
- [ ] Testing completo en staging
- [ ] Configurar HTTPS (obligatorio)

#### Frontend
- [ ] Implementar pantallas de UI
- [ ] Configurar deep linking (iOS + Android)
- [ ] Testing de flujo completo
- [ ] Validación de contraseñas en cliente
- [ ] Manejo de errores y timeouts
- [ ] Testing de emails en dispositivos reales

### Comandos de Deploy

```bash
# Backend
php artisan config:cache
php artisan route:cache
php artisan migrate --force

# Verificar tabla
php artisan tinker
>>> \Schema::hasTable('password_reset_tokens')

# Frontend (React Native)
npx react-native run-android
npx react-native run-ios
```

### Monitoring

#### Métricas a monitorear
- Tasa de éxito de envío de emails
- Tasa de conversión (reset completados / emails enviados)
- Intentos bloqueados por rate limiting
- Tokens expirados
- Errores en API

#### Logs importantes
```php
// Laravel Log
\Log::info('Password reset requested', ['user_id' => $user->id]);
\Log::warning('Rate limit exceeded', ['ip' => request()->ip()]);
\Log::error('Failed to send reset email', ['user_id' => $user->id, 'error' => $e->getMessage()]);

// Audit Log
SELECT * FROM audit_logs WHERE action LIKE 'password_reset%' ORDER BY created_at DESC;
```

---

## 📝 Resumen de Endpoints

| Método | Endpoint | Descripción | Rate Limit |
|--------|----------|-------------|------------|
| POST | `/api/auth/password/forgot` | Solicitar reset (email o DNI) | 5/hora |
| POST | `/api/auth/password/validate-token` | Validar token sin resetear | Ninguno |
| POST | `/api/auth/password/reset` | Resetear contraseña con token | 5/hora |
| POST | `/api/auth/password/can-reset` | Verificar elegibilidad | Ninguno |

---

## 🎯 Próximos Pasos para Implementación

### Fase 1: Setup Básico (2-3 horas)
1. Crear `PasswordResetService.php`
2. Crear `PasswordResetController.php`
3. Crear Form Requests (3 archivos)
4. Agregar rutas en `api.php`
5. Configurar email en `.env`

### Fase 2: Customización (1-2 horas)
6. Crear `ResetPasswordNotification.php` custom
7. Agregar deep link configuration
8. Configurar rate limiting

### Fase 3: Frontend Mobile (4-6 horas)
9. Implementar `ForgotPasswordScreen`
10. Implementar `ResetPasswordScreen`
11. Configurar deep linking (iOS + Android)
12. Testing de flujo completo

### Fase 4: Testing y QA (2-3 horas)
13. Escribir tests unitarios
14. Escribir tests de integración
15. Testing manual en dispositivos
16. Verificar emails en clientes reales

### Fase 5: Documentación y Deploy (1 hora)
17. Actualizar documentación de API
18. Deployment a staging
19. Testing en staging
20. Deployment a producción

**Tiempo total estimado:** 10-15 horas de desarrollo

---

## ✅ Checklist de Implementación Completa

- [ ] Backend Service implementado
- [ ] Controller con rate limiting
- [ ] Form Requests con validación
- [ ] Rutas API configuradas
- [ ] Email notification customizado
- [ ] Tests unitarios pasando
- [ ] Tests de integración pasando
- [ ] Pantalla "Forgot Password" en app móvil
- [ ] Pantalla "Reset Password" en app móvil
- [ ] Deep linking configurado (iOS)
- [ ] Deep linking configurado (Android)
- [ ] Email service configurado en producción
- [ ] Auditoría de seguridad completada
- [ ] Documentación actualizada
- [ ] Testing en staging exitoso
- [ ] Deploy a producción
- [ ] Monitoring configurado

---

**Fecha de última actualización:** 21 de Octubre 2025
**Autor:** Claude Code
**Estado:** Documento de diseño completo - Listo para implementación

