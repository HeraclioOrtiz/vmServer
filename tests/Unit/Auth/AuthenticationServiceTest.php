<?php

namespace Tests\Unit\Auth;

use App\Services\Auth\AuthenticationService;
use App\Services\Auth\PasswordValidationService;
use App\Services\Core\CacheService;
use App\Services\User\UserRefreshService;
use App\Models\User;
use App\DTOs\AuthResult;
use App\Enums\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthenticationService $authenticationService;
    private $mockCacheService;
    private $mockUserRefreshService;
    private $mockPasswordValidationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockCacheService = Mockery::mock(CacheService::class);
        $this->mockUserRefreshService = Mockery::mock(UserRefreshService::class);
        $this->mockPasswordValidationService = Mockery::mock(PasswordValidationService::class);
        
        $this->authenticationService = new AuthenticationService(
            $this->mockCacheService,
            $this->mockUserRefreshService,
            $this->mockPasswordValidationService
        );
    }

    /** @test */
    public function it_authenticates_user_from_database_when_not_cached()
    {
        // Arrange
        $user = User::factory()->create([
            'dni' => '12345678',
            'user_type' => UserType::LOCAL
        ]);

        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('12345678')
            ->once()
            ->andReturn(null);

        $this->mockPasswordValidationService
            ->shouldReceive('validate')
            ->with($user, 'password123')
            ->once();

        $this->mockCacheService
            ->shouldReceive('putUser')
            ->with($user)
            ->once();

        // Act
        $result = $this->authenticationService->authenticate('12345678', 'password123');

        // Assert
        $this->assertInstanceOf(AuthResult::class, $result);
        $this->assertEquals($user->id, $result->user->id);
        $this->assertFalse($result->fetchedFromApi);
        $this->assertFalse($result->refreshed);
    }

    /** @test */
    public function it_authenticates_user_from_cache()
    {
        // Arrange
        $user = User::factory()->create([
            'dni' => '12345678',
            'user_type' => UserType::API
        ]);

        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('12345678')
            ->once()
            ->andReturn($user);

        $this->mockPasswordValidationService
            ->shouldReceive('validate')
            ->with($user, 'password123')
            ->once();

        $user->shouldReceive('needsRefresh')
            ->once()
            ->andReturn(false);

        // Act
        $result = $this->authenticationService->authenticate('12345678', 'password123');

        // Assert
        $this->assertInstanceOf(AuthResult::class, $result);
        $this->assertEquals($user->id, $result->user->id);
        $this->assertFalse($result->fetchedFromApi);
        $this->assertFalse($result->refreshed);
    }

    /** @test */
    public function it_refreshes_api_user_when_needed()
    {
        // Arrange
        $user = User::factory()->create([
            'dni' => '12345678',
            'user_type' => UserType::API
        ]);

        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('12345678')
            ->once()
            ->andReturn(null);

        $this->mockPasswordValidationService
            ->shouldReceive('validate')
            ->with($user, 'password123')
            ->once();

        $user->shouldReceive('needsRefresh')
            ->once()
            ->andReturn(true);

        $this->mockUserRefreshService
            ->shouldReceive('refreshFromApi')
            ->with($user)
            ->once()
            ->andReturn(true);

        $this->mockCacheService
            ->shouldReceive('putUser')
            ->with($user)
            ->once();

        // Act
        $result = $this->authenticationService->authenticate('12345678', 'password123');

        // Assert
        $this->assertInstanceOf(AuthResult::class, $result);
        $this->assertTrue($result->refreshed);
    }

    /** @test */
    public function it_throws_exception_when_user_not_found()
    {
        // Arrange
        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('99999999')
            ->once()
            ->andReturn(null);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Usuario no registrado');
        
        $this->authenticationService->authenticate('99999999', 'password123');
    }

    /** @test */
    public function it_validates_credentials_without_authenticating()
    {
        // Arrange
        $user = User::factory()->create(['dni' => '12345678']);

        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('12345678')
            ->once()
            ->andReturn($user);

        $this->mockPasswordValidationService
            ->shouldReceive('validate')
            ->with($user, 'password123')
            ->once();

        $user->shouldReceive('needsRefresh')
            ->once()
            ->andReturn(false);

        // Act
        $result = $this->authenticationService->validateCredentials('12345678', 'password123');

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_for_invalid_credentials()
    {
        // Arrange
        $user = User::factory()->create(['dni' => '12345678']);

        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('12345678')
            ->once()
            ->andReturn($user);

        $this->mockPasswordValidationService
            ->shouldReceive('validate')
            ->with($user, 'wrong_password')
            ->once()
            ->andThrow(new ValidationException(validator([], [])));

        // Act
        $result = $this->authenticationService->validateCredentials('12345678', 'wrong_password');

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_gets_user_by_dni_from_cache_first()
    {
        // Arrange
        $user = User::factory()->create(['dni' => '12345678']);

        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('12345678')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->authenticationService->getUserByDni('12345678');

        // Assert
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function it_gets_user_by_dni_from_database_when_not_cached()
    {
        // Arrange
        $user = User::factory()->create(['dni' => '12345678']);

        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('12345678')
            ->once()
            ->andReturn(null);

        // Act
        $result = $this->authenticationService->getUserByDni('12345678');

        // Assert
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_user_not_found_by_dni()
    {
        // Arrange
        $this->mockCacheService
            ->shouldReceive('getUser')
            ->with('99999999')
            ->once()
            ->andReturn(null);

        // Act
        $result = $this->authenticationService->getUserByDni('99999999');

        // Assert
        $this->assertNull($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
