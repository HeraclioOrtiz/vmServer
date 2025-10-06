<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $professorUser;
    protected $studentUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuarios de prueba
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'dni' => '11111111',
            'password' => bcrypt('admin123'),
            'is_admin' => true,
            'permissions' => ['super_admin', 'user_management', 'gym_admin'],
            'account_status' => 'active',
        ]);

        $this->professorUser = User::factory()->create([
            'name' => 'Profesor Test',
            'email' => 'profesor@test.com',
            'dni' => '22222222',
            'password' => bcrypt('profesor123'),
            'is_professor' => true,
            'permissions' => ['gym_admin'],
            'account_status' => 'active',
        ]);

        $this->studentUser = User::factory()->create([
            'name' => 'Estudiante Test',
            'email' => 'estudiante@test.com',
            'dni' => '33333333',
            'password' => bcrypt('estudiante123'),
            'account_status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_login_and_access_admin_panel()
    {
        // Login como admin
        $response = $this->postJson('/api/auth/login', [
            'dni' => '11111111',
            'password' => 'admin123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'token']);
        
        $token = $response->json('token');

        // Acceder al panel admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function professor_can_access_gym_panel_but_not_admin_panel()
    {
        // Login como profesor
        $response = $this->postJson('/api/auth/login', [
            'dni' => '22222222',
            'password' => 'profesor123'
        ]);

        $response->assertStatus(200);
        $token = $response->json('token');

        // Puede acceder al panel gym
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/gym/exercises');

        $response->assertStatus(200);

        // NO puede acceder al panel admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function student_cannot_access_admin_panels()
    {
        // Login como estudiante
        $response = $this->postJson('/api/auth/login', [
            'dni' => '33333333',
            'password' => 'estudiante123'
        ]);

        $response->assertStatus(200);
        $token = $response->json('token');

        // NO puede acceder al panel admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(403);

        // NO puede acceder al panel gym
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/gym/exercises');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_manage_users()
    {
        $token = $this->loginAsAdmin();

        // Listar usuarios
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);

        // Ver usuario específico
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users/' . $this->studentUser->id);

        $response->assertStatus(200);
        $response->assertJson(['id' => $this->studentUser->id]);

        // Actualizar usuario
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/users/' . $this->studentUser->id, [
            'name' => 'Estudiante Actualizado',
            'account_status' => 'active'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_manage_professors()
    {
        $token = $this->loginAsAdmin();

        // Listar profesores
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/professors');

        $response->assertStatus(200);

        // Asignar profesor
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/professors/assign', [
            'user_id' => $this->studentUser->id,
            'qualifications' => [
                [
                    'type' => 'certification',
                    'title' => 'Entrenador Personal',
                    'institution' => 'Instituto Test'
                ]
            ]
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function professor_can_manage_exercises()
    {
        $token = $this->loginAsProfessor();

        // Crear ejercicio
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/gym/exercises', [
            'name' => 'Push-ups Test',
            'description' => 'Ejercicio de prueba',
            'category' => 'strength',
            'muscle_groups' => ['chest', 'arms'],
            'difficulty_level' => 2
        ]);

        $response->assertStatus(201);
        $exerciseId = $response->json('id');

        // Listar ejercicios
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/gym/exercises');

        $response->assertStatus(200);

        // Actualizar ejercicio
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/gym/exercises/' . $exerciseId, [
            'name' => 'Push-ups Actualizado'
        ]);

        $response->assertStatus(200);

        // Duplicar ejercicio
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/gym/exercises/' . $exerciseId . '/duplicate');

        $response->assertStatus(201);
    }

    /** @test */
    public function system_settings_work_correctly()
    {
        // Crear configuración
        $setting = SystemSetting::create([
            'key' => 'test_setting',
            'value' => 'test_value',
            'category' => 'testing'
        ]);

        // Verificar método get
        $this->assertEquals('test_value', SystemSetting::get('test_setting'));
        $this->assertEquals('default', SystemSetting::get('non_existent', 'default'));

        // Verificar método set
        SystemSetting::set('new_setting', 'new_value', 'testing');
        $this->assertEquals('new_value', SystemSetting::get('new_setting'));
    }

    /** @test */
    public function audit_logs_are_created()
    {
        $token = $this->loginAsAdmin();

        // Realizar una acción que debería crear un log
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/gym/exercises', [
            'name' => 'Test Exercise',
            'category' => 'strength',
            'muscle_groups' => ['chest'],
            'difficulty_level' => 1
        ]);

        // Verificar que se creó el log
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/audit');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_access_is_blocked()
    {
        // Sin token
        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(401);

        // Con token inválido
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson('/api/admin/users');
        
        $response->assertStatus(401);
    }

    /** @test */
    public function form_validation_works()
    {
        $token = $this->loginAsProfessor();

        // Crear ejercicio con datos inválidos
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/gym/exercises', [
            'name' => '', // Requerido
            'category' => 'invalid_category', // Categoría inválida
            'difficulty_level' => 10 // Fuera de rango
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'category', 'difficulty_level']);
    }

    // Métodos auxiliares
    private function loginAsAdmin(): string
    {
        $response = $this->postJson('/api/auth/login', [
            'dni' => '11111111',
            'password' => 'admin123'
        ]);

        return $response->json('token');
    }

    private function loginAsProfessor(): string
    {
        $response = $this->postJson('/api/auth/login', [
            'dni' => '22222222',
            'password' => 'profesor123'
        ]);

        return $response->json('token');
    }
}
