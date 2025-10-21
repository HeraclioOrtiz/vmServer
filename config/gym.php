<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Professor Assignment
    |--------------------------------------------------------------------------
    |
    | DNI del profesor por defecto para auto-asignación de nuevos estudiantes.
    | Esto es TEMPORAL hasta que se implemente la UI de asignación manual.
    |
    | Para deshabilitar la auto-asignación, dejar en null.
    |
    */

    'default_professor_dni' => env('GYM_DEFAULT_PROFESSOR_DNI', null),

    'auto_assign_students' => env('GYM_AUTO_ASSIGN_STUDENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Gym Limits
    |--------------------------------------------------------------------------
    |
    | Límites configurables para el sistema del gimnasio.
    |
    */

    'max_exercises_per_template' => env('GYM_MAX_EXERCISES', 20),

    'max_students_per_professor' => env('GYM_MAX_STUDENTS', 50),

    'max_sets_per_exercise' => env('GYM_MAX_SETS', 10),

    /*
    |--------------------------------------------------------------------------
    | Template Settings
    |--------------------------------------------------------------------------
    |
    | Configuraciones para plantillas de ejercicios.
    |
    */

    'template_cache_ttl' => env('GYM_TEMPLATE_CACHE_TTL', 300), // 5 minutos

    'allow_template_duplication' => env('GYM_ALLOW_TEMPLATE_DUPLICATION', true),

];
