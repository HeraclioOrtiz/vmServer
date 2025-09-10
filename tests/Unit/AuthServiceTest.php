<?php

namespace Tests\Unit;

use App\Contracts\SociosApiInterface;
use App\Exceptions\SocioNotFoundException;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private $mockSociosApi;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockSociosApi = Mockery::mock(SociosApiInterface::class);
        $this->authService = new AuthService($this->mockSociosApi);
    }

    /** @test */
    public function it_authenticates_existing_user_with_valid_credentials()
    {
        // Arrange
        $user = User::factory()->create([
            'dni' => '12345678',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $result = $this->authService->authenticate('12345678', 'password123');

        // Assert
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertFalse($result['fetched_from_api']);
        $this->assertEquals($user->id, $result['user']->id);
    }

    /** @test */
    public function it_throws_exception_for_invalid_password()
    {
        // Arrange
        User::factory()->create([
            'dni' => '12345678',
            'password' => Hash::make('correct_password'),
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->authService->authenticate('12345678', 'wrong_password');
    }

    /** @test */
    public function it_creates_new_user_from_api_when_not_exists_locally()
    {
        // Arrange
        $socioData = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'mail' => 'juan@example.com',
            'Id' => '12345',
            'update_ts' => '2025-09-08 12:00:00',
        ];

        $this->mockSociosApi
            ->shouldReceive('getSocioPorDni')
            ->with('12345678')
            ->once()
            ->andReturn($socioData);

        $this->mockSociosApi
            ->shouldReceive('fetchFotoSocio')
            ->with('12345')
            ->once()
            ->andReturn(null);

        // Act
        $result = $this->authService->authenticate('12345678', 'password123');

        // Assert
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertTrue($result['fetched_from_api']);
        $this->assertEquals('12345678', $result['user']->dni);
        $this->assertEquals('Pérez, Juan', $result['user']->name);
    }

    /** @test */
    public function it_throws_exception_when_socio_not_found_in_api()
    {
        // Arrange
        $this->mockSociosApi
            ->shouldReceive('getSocioPorDni')
            ->with('99999999')
            ->once()
            ->andReturn(null);

        // Act & Assert
        $this->expectException(SocioNotFoundException::class);
        $this->authService->authenticate('99999999', 'password123');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
