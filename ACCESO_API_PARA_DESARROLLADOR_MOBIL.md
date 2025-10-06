# 🌐 ACCESO API PARA DESARROLLADOR MÓVIL

**Fecha:** 2025-10-04  
**Estado:** ✅ SERVIDOR ACTIVO Y ACCESIBLE

---

## 🚀 **URLS DE ACCESO**

### **🏠 Local (Desarrollo):**
- **URL:** http://localhost:8000
- **Uso:** Solo desde la misma máquina

### **🌍 Público (LocalTunnel):**
- **URL:** https://villamitre.loca.lt
- **Uso:** Accesible desde cualquier dispositivo/ubicación
- **Estado:** ✅ ACTIVO

---

## 📱 **PARA TESTING CON APP MÓVIL**

### **Base URL para requests:**
```
https://villamitre.loca.lt
```

### **Ejemplo de request desde app móvil:**
```javascript
// Login
fetch('https://villamitre.loca.lt/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    dni: '55555555',
    password: 'maria123'
  })
})

// Obtener plantillas (con token)
fetch('https://villamitre.loca.lt/api/student/my-templates', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  }
})
```

---

## 🔐 **USUARIOS DE PRUEBA**

### **👨‍🎓 Estudiante (Para App Móvil):**
- **DNI:** 55555555
- **Password:** maria123
- **Email:** maria.garcia@villamitre.com
- **Datos:** Tiene profesor asignado y plantillas activas

### **👨‍🏫 Profesor:**
- **Email:** profesor@villamitre.com
- **Password:** profesor123
- **Datos:** Tiene estudiantes asignados

### **👨‍💼 Admin:**
- **Email:** admin@villamitre.com
- **Password:** admin123
- **Datos:** Acceso completo al sistema

---

## 📋 **ENDPOINTS DISPONIBLES**

### **🔑 Autenticación:**
```
POST https://villamitre.loca.lt/api/auth/login (requiere dni + password)
POST https://villamitre.loca.lt/api/test/login (endpoint de testing)
POST https://villamitre.loca.lt/api/auth/logout
```

### **👨‍🎓 Estudiantes (App Móvil):**
```
GET https://villamitre.loca.lt/api/student/my-templates
GET https://villamitre.loca.lt/api/student/template/{id}/details
GET https://villamitre.loca.lt/api/student/my-weekly-calendar
POST https://villamitre.loca.lt/api/student/progress/{session_id}/complete
```

### **👨‍🏫 Profesores:**
```
GET https://villamitre.loca.lt/api/professor/my-students
GET https://villamitre.loca.lt/api/professor/student/{id}/assignments
POST https://villamitre.loca.lt/api/professor/assign-template
```

### **👨‍💼 Administradores:**
```
GET https://villamitre.loca.lt/api/admin/professors
GET https://villamitre.loca.lt/api/admin/professors/{id}/students
POST https://villamitre.loca.lt/api/admin/assignments
```

---

## 🧪 **TESTING RÁPIDO CON CURL**

### **1. Test de conectividad:**
```bash
curl https://villamitre.loca.lt/api/health
```

### **2. Login de estudiante:**
```bash
curl -X POST https://villamitre.loca.lt/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "dni": "55555555",
    "password": "maria123"
  }'
```

### **3. Obtener plantillas (reemplazar {TOKEN}):**
```bash
curl -X GET https://villamitre.loca.lt/api/student/my-templates \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### **4. Calendario semanal:**
```bash
curl -X GET https://villamitre.loca.lt/api/student/my-weekly-calendar \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📊 **DATOS DE PRUEBA DISPONIBLES**

### **✅ Datos confirmados en el sistema:**
- **Profesor:** Profesor Juan Pérez (ID: 2)
- **Estudiante:** María García (ID: 5)
- **Plantilla activa:** "Full Body - General"
- **Ejercicios:** 4 ejercicios con 12 sets totales
- **Frecuencia:** Lunes, Miércoles, Viernes
- **Duración:** 75 minutos por sesión

### **✅ Calendario actual:**
```
🏋️ Lunes - Full Body - General (75 min)
📅 Martes - Sin entrenamiento
🏋️ Miércoles - Full Body - General (75 min)
📅 Jueves - Sin entrenamiento  
🏋️ Viernes - Full Body - General (75 min)
📅 Sábado/Domingo - Sin entrenamiento
```

---

## ⚠️ **CONSIDERACIONES IMPORTANTES**

### **🔒 Seguridad:**
- LocalTunnel es para desarrollo/testing únicamente
- No usar en producción
- El túnel puede cambiar de URL si se reinicia

### **🌐 Conectividad:**
- Requiere conexión a internet
- El servidor Laravel debe estar ejecutándose
- LocalTunnel debe estar activo

### **📱 CORS:**
- Configurado para aceptar requests desde cualquier origen
- Headers permitidos: Authorization, Content-Type, Accept
- Métodos permitidos: GET, POST, PUT, DELETE, OPTIONS

---

## 🔧 **COMANDOS DE MANTENIMIENTO**

### **Verificar estado del servidor:**
```bash
# En directorio del proyecto
php artisan serve  # Puerto 8000
```

### **Verificar estado de LocalTunnel:**
```bash
# En cualquier directorio
npx localtunnel --port 8000
```

### **Reiniciar LocalTunnel (si cambia URL):**
```bash
# Ctrl+C para detener, luego:
npx localtunnel --port 8000
```

---

## 📞 **SOPORTE Y DOCUMENTACIÓN**

### **📚 Documentación completa:**
- `PROMPT_DESARROLLADOR_APP_MOBIL.md` - Guía completa para desarrollador
- `REPORTE_TESTING_APP_MOBIL.md` - Resultados de testing
- `test_mobile_app_endpoints.php` - Script de testing

### **🧪 Scripts de testing:**
```bash
# Testing completo de endpoints
php test_mobile_app_endpoints.php

# Verificar panel de admin
php verify_admin_professor_panel.php
```

---

## ✅ **CHECKLIST PARA DESARROLLADOR MÓVIL**

### **Antes de empezar:**
- [ ] Verificar acceso a https://villamitre.loca.lt
- [ ] Probar login con usuario de prueba
- [ ] Confirmar estructura de respuestas
- [ ] Validar headers requeridos

### **Durante desarrollo:**
- [ ] Implementar autenticación Sanctum
- [ ] Crear modelos basados en estructuras documentadas
- [ ] Manejar tokens de forma segura
- [ ] Implementar manejo de errores

### **Para testing:**
- [ ] Probar con datos reales del sistema
- [ ] Validar todos los endpoints
- [ ] Testing en dispositivo real
- [ ] Verificar modo offline (opcional)

---

## 🎯 **PRÓXIMOS PASOS**

1. **✅ Servidor activo:** Laravel + LocalTunnel funcionando
2. **📱 App móvil:** Usar URL pública para requests
3. **🧪 Testing:** Validar integración con datos reales
4. **🚀 Desarrollo:** Implementar según documentación

---

**🌐 URL PÚBLICA ACTIVA:** https://villamitre.loca.lt  
**📱 LISTO PARA DESARROLLO DE APP MÓVIL**

---

## 📋 **ESTADO DE SERVICIOS**

| Servicio | Estado | URL |
|----------|--------|-----|
| Laravel Server | ✅ ACTIVO | http://localhost:8000 |
| LocalTunnel | ✅ ACTIVO | https://villamitre.loca.lt |
| Base de Datos | ✅ ACTIVO | Datos de prueba cargados |
| API Endpoints | ✅ FUNCIONAL | 4 endpoints principales |

**Última actualización:** 2025-10-04 14:08
