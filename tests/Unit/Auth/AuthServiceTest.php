<?php

namespace Tests\Unit\Auth;

use App\Services\Auth\AuthService;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\UserRegistrationService;
use App\Services\Core\AuditService;
use App\Models\User;
use App\DTOs\AuthResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private $mockAuthenticationService;
    private $mockUserRegistrationService;
    private $mockAuditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockAuthenticationService = Mockery::mock(AuthenticationService::class);
        $this->mockUserRegistrationService = Mockery::mock(UserRegistrationService::class);
        $this->mockAuditService = Mockery::mock(AuditService::class);
        
        $this->authService = new AuthService(
            $this->mockAuthenticationService,
            $this->mockUserRegistrationService,
            $this->mockAuditService
        );
    }

    /** @test */
    public function it_authenticates_user_successfully()
    {
        // Arrange
        $user = User::factory()->create(['dni' => '12345678']);
        $authResult = new AuthResult($user, false, false);

        $this->mockAuthenticationService
            ->shouldReceive('authenticate')
            ->with('12345678', 'password123')
            ->once()
            ->andReturn($authResult);

        $this->mockAuditService
            ->shouldReceive('logLogin')
            ->with($user->id, true)
            ->once();

        // Act
        $result = $this->authService->authenticate('12345678', 'password123');

        // Assert
        $this->assertInstanceOf(AuthResult::class, $result);
        $this->assertEquals($user->id, $result->user->id);
        $this->assertFalse($result->fetchedFromApi);
    }

    /** @test */
    public function it_logs_failed_authentication_attempts()
    {
        // Arrange
        $user = User::factory()->create(['dni' => '12345678']);
        
        $this->mockAuthenticationService
            ->shouldReceive('authenticate')
            ->with('12345678', 'wrong_password')
            ->once()
            ->andThrow(new ValidationException(validator([], [])));

        $this->mockAuthenticationService
            ->shouldReceive('getUserByDni')
            ->with('12345678')
            ->once()
            ->andReturn($user);

        $this->mockAuditService
            ->shouldReceive('logLogin')
            ->with($user->id, false)
            ->once();

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->authService->authenticate('12345678', 'wrong_password');
    }

    /** @test */
    public function it_registers_local_user_successfully()
    {
        // Arrange
        $userData = [
            'dni' => '12345678',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $user = User::factory()->create($userData);

        $this->mockUserRegistrationService
            ->shouldReceive('registerLocal')
            ->with($userData)
            ->once()
            ->andReturn($user);

        $this->mockAuditService
            ->shouldReceive('logCreate')
            ->with('user', $user->id, Mockery::type('array'))
            ->once();

        // Act
        $result = $this->authService->registerLocal($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('12345678', $result->dni);
    }

    /** @test */
    public function it_validates_credentials_without_authenticating()
    {
        // Arrange
        $this->mockAuthenticationService
            ->shouldReceive('validateCredentials')
            ->with('12345678', 'password123')
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->authService->validateCredentials('12345678', 'password123');

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_checks_dni_availability()
    {
        // Arrange
        $this->mockUserRegistrationService
            ->shouldReceive('isDniAvailable')
            ->with('12345678')
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->authService->isDniAvailable('12345678');

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_validates_registration_data()
    {
        // Arrange
        $data = ['dni' => '12345678', 'email' => 'test@example.com'];
        $expectedErrors = [];

        $this->mockUserRegistrationService
            ->shouldReceive('validateRegistrationData')
            ->with($data)
            ->once()
            ->andReturn($expectedErrors);

        // Act
        $result = $this->authService->validateRegistrationData($data);

        // Assert
        $this->assertEquals($expectedErrors, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
