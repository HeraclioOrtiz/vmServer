# 📊 EJEMPLO DE DATOS REALES - MARÍA GARCÍA

## 🔐 1. RESPUESTA DE LOGIN

```json
{
  "data": {
    "token": "368|z06jXXJKvkMYNKO46pTXlUm6KlByyLRH2p0jwfHY1b215b55",
    "user": {
      "id": 3,
      "name": "Estudiante María García",
      "email": "estudiante@villamitre.com",
      "dni": "33333333",
      "is_professor": false,
      "is_admin": false,
      "account_status": "active"
    }
  }
}
```

## 📋 2. RESPUESTA DE MIS PLANTILLAS ASIGNADAS

**Endpoint:** `GET /api/student/my-templates`

```json
{
  "message": "Plantillas obtenidas exitosamente",
  "data": {
    "professor": {
      "id": 2,
      "name": "Profesor Juan Pérez",
      "email": "profesor@villamitre.com"
    },
    "templates": [
      {
        "id": 8,
        "daily_template": {
          "id": 101,
          "title": "Atlético Explosivo 50'",
          "goal": "strength",
          "level": "advanced",
          "estimated_duration_min": 50,
          "tags": ["explosivo", "atlético"],
          "exercises_count": 5
        },
        "start_date": "2025-09-27",
        "end_date": "2025-10-10",
        "frequency": [1],
        "frequency_days": ["Lunes"],
        "professor_notes": "Rutina avanzada para desarrollo explosivo",
        "status": "active",
        "assigned_by": {
          "id": 2,
          "name": "Profesor Juan Pérez"
        },
        "created_at": "2025-09-27T16:08:40.000000Z"
      },
      {
        "id": 9,
        "daily_template": {
          "id": 95,
          "title": "Activación Matutina 20'",
          "goal": "general",
          "level": "beginner",
          "estimated_duration_min": 20,
          "tags": ["matutina", "activación"],
          "exercises_count": 3
        },
        "start_date": "2025-10-11",
        "end_date": "2025-11-06",
        "frequency": [1, 4],
        "frequency_days": ["Lunes", "Jueves"],
        "professor_notes": "Rutina suave para comenzar el día",
        "status": "active",
        "assigned_by": {
          "id": 2,
          "name": "Profesor Juan Pérez"
        },
        "created_at": "2025-09-27T16:09:05.000000Z"
      },
      {
        "id": 10,
        "daily_template": {
          "id": 84,
          "title": "Fuerza Upper Body 60'",
          "goal": "strength",
          "level": "intermediate",
          "estimated_duration_min": 60,
          "tags": ["fuerza", "upper"],
          "exercises_count": 4
        },
        "start_date": "2025-09-28",
        "end_date": "2025-10-28",
        "frequency": [2, 4, 6],
        "frequency_days": ["Martes", "Jueves", "Sábado"],
        "professor_notes": "Enfoque en tren superior",
        "status": "active",
        "assigned_by": {
          "id": 2,
          "name": "Profesor Juan Pérez"
        },
        "created_at": "2025-09-27T16:28:51.000000Z"
      }
    ]
  }
}
```

## 🏋️ 3. RESPUESTA DE DETALLES DE PLANTILLA

**Endpoint:** `GET /api/student/template/8/details`

```json
{
  "message": "Detalles de plantilla obtenidos exitosamente",
  "data": {
    "assignment_info": {
      "id": 8,
      "start_date": "2025-09-27",
      "end_date": "2025-10-10",
      "frequency": [1],
      "frequency_days": ["Lunes"],
      "professor_notes": "Rutina avanzada para desarrollo explosivo",
      "status": "active",
      "assigned_by": {
        "id": 2,
        "name": "Profesor Juan Pérez",
        "email": "profesor@villamitre.com"
      }
    },
    "template": {
      "id": 101,
      "title": "Atlético Explosivo 50'",
      "goal": "strength",
      "level": "advanced",
      "estimated_duration_min": 50,
      "tags": ["explosivo", "atlético"],
      "created_at": "2025-09-25T20:15:30.000000Z"
    },
    "exercises": [
      {
        "id": 420,
        "order": 1,
        "exercise": {
          "id": 52,
          "name": "Sentadillas con Salto",
          "description": "Ejercicio pliométrico para desarrollo de potencia en piernas",
          "target_muscle_groups": ["cuádriceps", "glúteos", "pantorrillas"],
          "equipment": ["peso corporal"],
          "difficulty_level": "advanced",
          "instructions": "Realizar sentadilla profunda y explotar hacia arriba con salto máximo"
        },
        "sets": [
          {
            "id": 1260,
            "set_number": 1,
            "reps": 8,
            "weight": null,
            "duration": null,
            "rest_seconds": 120,
            "notes": "Máxima explosividad"
          },
          {
            "id": 1261,
            "set_number": 2,
            "reps": 8,
            "weight": null,
            "duration": null,
            "rest_seconds": 120,
            "notes": null
          },
          {
            "id": 1262,
            "set_number": 3,
            "reps": 6,
            "weight": null,
            "duration": null,
            "rest_seconds": 180,
            "notes": "Serie final, máximo esfuerzo"
          }
        ],
        "notes": "Enfocarse en la técnica y explosividad"
      },
      {
        "id": 421,
        "order": 2,
        "exercise": {
          "id": 45,
          "name": "Flexiones Explosivas",
          "description": "Flexiones con despegue de manos para desarrollo de potencia",
          "target_muscle_groups": ["pectorales", "tríceps", "deltoides"],
          "equipment": ["peso corporal"],
          "difficulty_level": "advanced",
          "instructions": "Bajar controlado y explotar hacia arriba despegando las manos"
        },
        "sets": [
          {
            "id": 1263,
            "set_number": 1,
            "reps": 6,
            "weight": null,
            "duration": null,
            "rest_seconds": 90,
            "notes": null
          },
          {
            "id": 1264,
            "set_number": 2,
            "reps": 6,
            "weight": null,
            "duration": null,
            "rest_seconds": 90,
            "notes": null
          },
          {
            "id": 1265,
            "set_number": 3,
            "reps": 4,
            "weight": null,
            "duration": null,
            "rest_seconds": 120,
            "notes": "Máxima explosividad"
          }
        ],
        "notes": "Si no puedes hacer explosivas, hacer flexiones normales"
      }
    ]
  }
}
```

## 📅 4. RESPUESTA DE CALENDARIO SEMANAL

**Endpoint:** `GET /api/student/my-weekly-calendar`

```json
{
  "message": "Calendario semanal obtenido exitosamente",
  "data": {
    "week_start": "2025-09-22",
    "week_end": "2025-09-28",
    "days": [
      {
        "date": "2025-09-22",
        "day_name": "Monday",
        "day_short": "Mon",
        "day_number": 22,
        "has_workouts": true,
        "assignments": [
          {
            "id": 8,
            "daily_template": {
              "id": 101,
              "title": "Atlético Explosivo 50'",
              "goal": "strength",
              "level": "advanced",
              "estimated_duration_min": 50
            },
            "professor_notes": "Rutina avanzada para desarrollo explosivo",
            "assigned_by": {
              "name": "Profesor Juan Pérez"
            }
          },
          {
            "id": 9,
            "daily_template": {
              "id": 95,
              "title": "Activación Matutina 20'",
              "goal": "general",
              "level": "beginner",
              "estimated_duration_min": 20
            },
            "professor_notes": "Rutina suave para comenzar el día",
            "assigned_by": {
              "name": "Profesor Juan Pérez"
            }
          }
        ]
      },
      {
        "date": "2025-09-23",
        "day_name": "Tuesday",
        "day_short": "Tue",
        "day_number": 23,
        "has_workouts": true,
        "assignments": [
          {
            "id": 10,
            "daily_template": {
              "id": 84,
              "title": "Fuerza Upper Body 60'",
              "goal": "strength",
              "level": "intermediate",
              "estimated_duration_min": 60
            },
            "professor_notes": "Enfoque en tren superior",
            "assigned_by": {
              "name": "Profesor Juan Pérez"
            }
          }
        ]
      },
      {
        "date": "2025-09-24",
        "day_name": "Wednesday",
        "day_short": "Wed",
        "day_number": 24,
        "has_workouts": false,
        "assignments": []
      },
      {
        "date": "2025-09-25",
        "day_name": "Thursday",
        "day_short": "Thu",
        "day_number": 25,
        "has_workouts": true,
        "assignments": [
          {
            "id": 9,
            "daily_template": {
              "id": 95,
              "title": "Activación Matutina 20'",
              "goal": "general",
              "level": "beginner",
              "estimated_duration_min": 20
            },
            "professor_notes": "Rutina suave para comenzar el día",
            "assigned_by": {
              "name": "Profesor Juan Pérez"
            }
          },
          {
            "id": 10,
            "daily_template": {
              "id": 84,
              "title": "Fuerza Upper Body 60'",
              "goal": "strength",
              "level": "intermediate",
              "estimated_duration_min": 60
            },
            "professor_notes": "Enfoque en tren superior",
            "assigned_by": {
              "name": "Profesor Juan Pérez"
            }
          }
        ]
      },
      {
        "date": "2025-09-26",
        "day_name": "Friday",
        "day_short": "Fri",
        "day_number": 26,
        "has_workouts": false,
        "assignments": []
      },
      {
        "date": "2025-09-27",
        "day_name": "Saturday",
        "day_short": "Sat",
        "day_number": 27,
        "has_workouts": true,
        "assignments": [
          {
            "id": 10,
            "daily_template": {
              "id": 84,
              "title": "Fuerza Upper Body 60'",
              "goal": "strength",
              "level": "intermediate",
              "estimated_duration_min": 60
            },
            "professor_notes": "Enfoque en tren superior",
            "assigned_by": {
              "name": "Profesor Juan Pérez"
            }
          }
        ]
      },
      {
        "date": "2025-09-28",
        "day_name": "Sunday",
        "day_short": "Sun",
        "day_number": 28,
        "has_workouts": false,
        "assignments": []
      }
    ]
  }
}
```

## 🎯 RESUMEN DE DATOS DISPONIBLES

### 📋 **Para Lista de Plantillas:**
- ✅ **Información del profesor** (nombre, email)
- ✅ **Plantillas asignadas** con detalles básicos
- ✅ **Fechas de inicio/fin** y frecuencias
- ✅ **Notas del profesor** para cada asignación
- ✅ **Estado** de cada asignación

### 🏋️ **Para Detalles de Ejercicios:**
- ✅ **Ejercicios completos** con orden específico
- ✅ **Series detalladas** (repeticiones, peso, descanso)
- ✅ **Músculos objetivo** y equipo necesario
- ✅ **Instrucciones** paso a paso
- ✅ **Notas específicas** por ejercicio y serie

### 📅 **Para Calendario Semanal:**
- ✅ **Vista de 7 días** con fechas exactas
- ✅ **Entrenamientos por día** organizados
- ✅ **Días de descanso** claramente marcados
- ✅ **Múltiples entrenamientos** por día si aplica

## 💡 **Uso en Frontend:**

```javascript
// Ejemplo de cómo usar estos datos en React
const StudentDashboard = () => {
  const [templates, setTemplates] = useState([]);
  const [calendar, setCalendar] = useState([]);
  
  // Cargar plantillas
  useEffect(() => {
    fetch('/api/student/my-templates', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(data => {
      setTemplates(data.data.templates);
      setProfessor(data.data.professor);
    });
  }, []);
  
  return (
    <div>
      <h2>Mis Entrenamientos - Profesor: {professor.name}</h2>
      {templates.map(template => (
        <TemplateCard 
          key={template.id}
          title={template.daily_template.title}
          duration={template.daily_template.estimated_duration_min}
          level={template.daily_template.level}
          days={template.frequency_days}
          notes={template.professor_notes}
        />
      ))}
    </div>
  );
};
```

**🎉 ¡Todos los datos están perfectamente estructurados y listos para el frontend!**
