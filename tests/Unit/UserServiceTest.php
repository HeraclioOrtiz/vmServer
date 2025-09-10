<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserService;
use App\Services\CacheService;
use App\Models\User;
use App\Enums\UserType;
use App\Enums\PromotionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;
    private $cacheServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cacheServiceMock = Mockery::mock(CacheService::class);
        $this->userService = new UserService($this->cacheServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_local_user()
    {
        $userData = [
            'dni' => '12345678',
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'phone' => '+54911234567'
        ];

        $this->cacheServiceMock
            ->shouldReceive('putUser')
            ->once();

        $user = $this->userService->createLocalUser($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['dni'], $user->dni);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals(UserType::LOCAL, $user->user_type);
        $this->assertEquals(PromotionStatus::NONE, $user->promotion_status);
        $this->assertTrue(\Hash::check($userData['password'], $user->password));
    }

    /** @test */
    public function it_throws_exception_when_dni_already_exists()
    {
        User::factory()->create(['dni' => '12345678']);

        $userData = [
            'dni' => '12345678',
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123'
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El DNI ya está registrado.');

        $this->userService->createLocalUser($userData);
    }

    /** @test */
    public function it_throws_exception_when_email_already_exists()
    {
        User::factory()->create(['email' => 'juan@example.com']);

        $userData = [
            'dni' => '12345678',
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123'
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El email ya está registrado.');

        $this->userService->createLocalUser($userData);
    }

    /** @test */
    public function it_can_update_local_user()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com'
        ]);

        $updateData = [
            'name' => 'Juan Carlos Pérez',
            'email' => 'juancarlos@example.com'
        ];

        $this->cacheServiceMock
            ->shouldReceive('forgetUser')
            ->once()
            ->with($user->dni);

        $updatedUser = $this->userService->updateUser($user, $updateData);

        $this->assertEquals($updateData['name'], $updatedUser->name);
        $this->assertEquals($updateData['email'], $updatedUser->email);
    }

    /** @test */
    public function it_only_updates_allowed_fields_for_api_user()
    {
        $user = User::factory()->create([
            'user_type' => UserType::API,
            'name' => 'González, Adrián',
            'phone' => '+54911111111'
        ]);

        $updateData = [
            'name' => 'Nuevo Nombre', // No debería actualizarse
            'phone' => '+54922222222' // Debería actualizarse
        ];

        $this->cacheServiceMock
            ->shouldReceive('forgetUser')
            ->once()
            ->with($user->dni);

        $updatedUser = $this->userService->updateUser($user, $updateData);

        $this->assertEquals('González, Adrián', $updatedUser->name); // Sin cambios
        $this->assertEquals('+54922222222', $updatedUser->phone); // Actualizado
    }

    /** @test */
    public function it_can_delete_local_user()
    {
        $user = User::factory()->create(['user_type' => UserType::LOCAL]);

        $this->cacheServiceMock
            ->shouldReceive('forgetUser')
            ->once()
            ->with($user->dni);

        $result = $this->userService->deleteUser($user);

        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_cannot_delete_api_user_with_socio_data()
    {
        $user = User::factory()->create([
            'user_type' => UserType::API,
            'socio_id' => '12345'
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No se puede eliminar un usuario API con datos del club.');

        $this->userService->deleteUser($user);
    }

    /** @test */
    public function it_returns_user_stats()
    {
        // Crear usuarios de prueba
        User::factory()->count(3)->create(['user_type' => UserType::LOCAL]);
        User::factory()->count(2)->create(['user_type' => UserType::API]);
        User::factory()->create([
            'user_type' => UserType::LOCAL,
            'promotion_status' => PromotionStatus::APPROVED,
            'promoted_at' => now()
        ]);

        $stats = $this->userService->getUserStats();

        $this->assertEquals(6, $stats['total_users']);
        $this->assertEquals(3, $stats['local_users']);
        $this->assertEquals(2, $stats['api_users']);
        $this->assertEquals(1, $stats['promoted_users']);
    }

    /** @test */
    public function it_can_search_users()
    {
        User::factory()->create([
            'name' => 'Juan Pérez',
            'dni' => '12345678',
            'email' => 'juan@example.com'
        ]);

        User::factory()->create([
            'name' => 'María González',
            'dni' => '87654321',
            'email' => 'maria@example.com'
        ]);

        // Buscar por nombre
        $results = $this->userService->searchUsers('Juan');
        $this->assertCount(1, $results);
        $this->assertEquals('Juan Pérez', $results[0]['name']);

        // Buscar por DNI
        $results = $this->userService->searchUsers('87654321');
        $this->assertCount(1, $results);
        $this->assertEquals('María González', $results[0]['name']);

        // Buscar por email
        $results = $this->userService->searchUsers('maria@');
        $this->assertCount(1, $results);
        $this->assertEquals('María González', $results[0]['name']);
    }

    /** @test */
    public function it_can_change_user_type()
    {
        $user = User::factory()->create([
            'user_type' => UserType::LOCAL,
            'socio_id' => null
        ]);

        $this->cacheServiceMock
            ->shouldReceive('forgetUser')
            ->once()
            ->with($user->dni);

        $updatedUser = $this->userService->changeUserType($user, UserType::API);

        $this->assertEquals(UserType::API, $updatedUser->user_type);
        $this->assertEquals(PromotionStatus::APPROVED, $updatedUser->promotion_status);
    }

    /** @test */
    public function it_cannot_change_api_user_with_socio_data_to_local()
    {
        $user = User::factory()->create([
            'user_type' => UserType::API,
            'socio_id' => '12345'
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No se puede convertir a local un usuario con datos del club.');

        $this->userService->changeUserType($user, UserType::LOCAL);
    }

    /** @test */
    public function it_can_clear_user_cache()
    {
        $dni = '12345678';

        $this->cacheServiceMock
            ->shouldReceive('forgetUser')
            ->once()
            ->with($dni);

        $this->userService->clearUserCache($dni);
    }

    /** @test */
    public function it_can_clear_all_users_cache()
    {
        $this->cacheServiceMock
            ->shouldReceive('clearAllUserCache')
            ->once();

        $this->userService->clearAllUsersCache();
    }
}
