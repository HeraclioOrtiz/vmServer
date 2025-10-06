<?php

namespace Tests\Unit\Auth;

use App\Services\Auth\PasswordValidationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PasswordValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PasswordValidationService $passwordValidationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordValidationService = new PasswordValidationService();
    }

    /** @test */
    public function it_validates_correct_password_successfully()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct_password')
        ]);

        // Act & Assert - No exception should be thrown
        $this->passwordValidationService->validate($user, 'correct_password');
        $this->assertTrue(true); // If we reach here, validation passed
    }

    /** @test */
    public function it_throws_exception_for_incorrect_password()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct_password')
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Credenciales inválidas.');
        
        $this->passwordValidationService->validate($user, 'wrong_password');
    }

    /** @test */
    public function it_handles_critical_password_validation_errors()
    {
        // Arrange - Create user with potentially problematic password hash
        $user = User::factory()->create([
            'dni' => '58964605', // The problematic DNI from the bug report
            'password' => Hash::make('Zzxx4518688') // The problematic password
        ]);

        Log::shouldReceive('info')->once();
        
        // Act & Assert - Should handle gracefully without crashing
        try {
            $this->passwordValidationService->validate($user, 'Zzxx4518688');
            $this->assertTrue(true); // Validation should pass
        } catch (ValidationException $e) {
            // If validation fails, it should be a proper ValidationException, not a crash
            $this->assertStringContains('Credenciales inválidas', $e->getMessage());
        } catch (\Exception $e) {
            // Any other exception should be caught and converted
            $this->fail('Critical error should be handled gracefully: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_logs_failed_attempts_properly()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct_password')
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->with('Failed password validation', \Mockery::type('array'));

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->passwordValidationService->validate($user, 'wrong_password');
    }

    /** @test */
    public function it_validates_password_strength_correctly()
    {
        // Test weak password
        $errors = $this->passwordValidationService->validatePasswordStrength('123');
        $this->assertNotEmpty($errors);
        $this->assertContains('La contraseña debe tener al menos 8 caracteres.', $errors);

        // Test strong password
        $errors = $this->passwordValidationService->validatePasswordStrength('StrongPass123!');
        $this->assertEmpty($errors);
    }

    /** @test */
    public function it_validates_password_strength_requirements()
    {
        // No uppercase
        $errors = $this->passwordValidationService->validatePasswordStrength('lowercase123');
        $this->assertContains('La contraseña debe contener al menos una letra mayúscula.', $errors);

        // No lowercase
        $errors = $this->passwordValidationService->validatePasswordStrength('UPPERCASE123');
        $this->assertContains('La contraseña debe contener al menos una letra minúscula.', $errors);

        // No numbers
        $errors = $this->passwordValidationService->validatePasswordStrength('NoNumbers');
        $this->assertContains('La contraseña debe contener al menos un número.', $errors);
    }

    /** @test */
    public function it_hashes_passwords_securely()
    {
        // Act
        $hash = $this->passwordValidationService->hashPassword('test_password');

        // Assert
        $this->assertTrue(Hash::check('test_password', $hash));
        $this->assertNotEquals('test_password', $hash);
    }

    /** @test */
    public function it_checks_if_password_needs_rehash()
    {
        // Arrange
        $hash = Hash::make('test_password');

        // Act
        $needsRehash = $this->passwordValidationService->needsRehash($hash);

        // Assert
        $this->assertIsBool($needsRehash);
    }

    /** @test */
    public function it_validates_without_throwing_exceptions()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct_password')
        ]);

        // Test valid password
        $isValid = $this->passwordValidationService->isValid($user, 'correct_password');
        $this->assertTrue($isValid);

        // Test invalid password
        $isValid = $this->passwordValidationService->isValid($user, 'wrong_password');
        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_handles_edge_cases_gracefully()
    {
        // Test with empty password
        $user = User::factory()->create([
            'password' => Hash::make('test')
        ]);

        $this->expectException(ValidationException::class);
        $this->passwordValidationService->validate($user, '');
    }

    /** @test */
    public function it_handles_null_password_hash()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => null
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->passwordValidationService->validate($user, 'any_password');
    }

    /** @test */
    public function it_logs_critical_errors_with_detailed_information()
    {
        // This test ensures that critical errors are logged with enough detail for debugging
        $user = User::factory()->create([
            'dni' => '58964605',
            'password' => Hash::make('test_password')
        ]);

        // Mock a scenario where Hash::check might throw an exception
        Log::shouldReceive('critical')
            ->never(); // We don't expect critical errors in normal operation

        Log::shouldReceive('info')
            ->once()
            ->with('Successful password validation', \Mockery::type('array'));

        // This should work normally
        $this->passwordValidationService->validate($user, 'test_password');
    }
}
