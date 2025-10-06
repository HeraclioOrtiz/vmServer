<?php

namespace Tests\Unit\External;

use App\Services\External\SocioDataMappingService;
use App\Enums\UserType;
use App\Enums\PromotionStatus;
use Tests\TestCase;

class SocioDataMappingServiceTest extends TestCase
{
    private SocioDataMappingService $mappingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mappingService = new SocioDataMappingService();
    }

    /** @test */
    public function it_maps_complete_socio_data_correctly()
    {
        // Arrange
        $socioData = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan.perez@example.com',
            'nacionalidad' => 'Argentina',
            'nacimiento' => '1990-05-15',
            'domicilio' => 'Av. Corrientes 1234',
            'localidad' => 'Buenos Aires',
            'telefono' => '011-1234-5678',
            'celular' => '011-9876-5432',
            'socio_id' => 12345,
            'socio_n' => 67890,
            'categoria' => 'Activo',
            'barcode' => '12345678901',
            'saldo' => 1500.50,
            'deuda' => 0.00,
            'estado_socio' => 'ACTIVO',
            'semaforo' => 1,
            'suspendido' => false,
            'foto_url' => 'https://example.com/photo.jpg'
        ];

        // Act
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'hashed_password');

        // Assert
        $this->assertEquals('12345678', $result['dni']);
        $this->assertEquals(UserType::API, $result['user_type']);
        $this->assertEquals(PromotionStatus::APPROVED, $result['promotion_status']);
        $this->assertEquals('Juan', $result['nombre']);
        $this->assertEquals('Pérez', $result['apellido']);
        $this->assertEquals('Pérez, Juan', $result['name']);
        $this->assertEquals('juan.perez@example.com', $result['email']);
        $this->assertEquals(12345, $result['socio_id']);
        $this->assertEquals('ACTIVO', $result['estado_socio']);
        $this->assertEquals(1, $result['semaforo']);
        $this->assertEquals(1500.50, $result['saldo']);
        $this->assertEquals('https://example.com/photo.jpg', $result['foto_url']);
        $this->assertNotNull($result['api_updated_at']);
    }

    /** @test */
    public function it_handles_missing_optional_fields()
    {
        // Arrange
        $socioData = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez'
            // Missing most optional fields
        ];

        // Act
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');

        // Assert
        $this->assertEquals('Juan', $result['nombre']);
        $this->assertEquals('Pérez', $result['apellido']);
        $this->assertEquals('Pérez, Juan', $result['name']);
        $this->assertNull($result['email']);
        $this->assertNull($result['socio_id']);
        $this->assertEquals('', $result['telefono']);
        $this->assertEquals(0.0, $result['saldo']);
        $this->assertEquals('ACTIVO', $result['estado_socio']); // Default value
        $this->assertEquals(1, $result['semaforo']); // Default value
    }

    /** @test */
    public function it_sanitizes_string_fields()
    {
        // Arrange
        $socioData = [
            'nombre' => '  <script>alert("xss")</script>Juan  ',
            'apellido' => '  Pérez<br>  ',
            'domicilio' => '<b>Av. Corrientes</b> 1234'
        ];

        // Act
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');

        // Assert
        $this->assertEquals('Juan', $result['nombre']);
        $this->assertEquals('Pérez', $result['apellido']);
        $this->assertEquals('Av. Corrientes 1234', $result['domicilio']);
    }

    /** @test */
    public function it_validates_and_sanitizes_email()
    {
        // Valid email
        $socioData = ['email' => '  JUAN.PEREZ@EXAMPLE.COM  '];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertEquals('juan.perez@example.com', $result['email']);

        // Invalid email
        $socioData = ['email' => 'invalid-email'];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertNull($result['email']);

        // Empty email
        $socioData = ['email' => ''];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertNull($result['email']);
    }

    /** @test */
    public function it_validates_and_sanitizes_url()
    {
        // Valid URL
        $socioData = ['foto_url' => 'https://example.com/photo.jpg'];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertEquals('https://example.com/photo.jpg', $result['foto_url']);

        // Invalid URL
        $socioData = ['foto_url' => 'not-a-url'];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertNull($result['foto_url']);
    }

    /** @test */
    public function it_parses_numeric_values_correctly()
    {
        // Arrange
        $socioData = [
            'socio_id' => '12345',
            'saldo' => '1500.75',
            'deuda' => '250.00',
            'semaforo' => '1'
        ];

        // Act
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');

        // Assert
        $this->assertEquals(12345, $result['socio_id']);
        $this->assertEquals(1500.75, $result['saldo']);
        $this->assertEquals(250.00, $result['deuda']);
        $this->assertEquals(1, $result['semaforo']);
    }

    /** @test */
    public function it_handles_invalid_numeric_values()
    {
        // Arrange
        $socioData = [
            'socio_id' => 'invalid',
            'saldo' => 'not-a-number',
            'semaforo' => 'invalid'
        ];

        // Act
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');

        // Assert
        $this->assertNull($result['socio_id']);
        $this->assertEquals(0.0, $result['saldo']);
        $this->assertNull($result['semaforo']);
    }

    /** @test */
    public function it_parses_boolean_values_correctly()
    {
        // Test various boolean representations
        $testCases = [
            ['suspendido' => true, 'expected' => true],
            ['suspendido' => false, 'expected' => false],
            ['suspendido' => 'true', 'expected' => true],
            ['suspendido' => 'false', 'expected' => false],
            ['suspendido' => '1', 'expected' => true],
            ['suspendido' => '0', 'expected' => false],
            ['suspendido' => 1, 'expected' => true],
            ['suspendido' => 0, 'expected' => false],
            ['suspendido' => 'yes', 'expected' => true],
            ['suspendido' => 'no', 'expected' => false],
        ];

        foreach ($testCases as $testCase) {
            $result = $this->mappingService->mapSocioToUserData($testCase, '12345678', 'password');
            $this->assertEquals($testCase['expected'], $result['suspendido']);
        }
    }

    /** @test */
    public function it_parses_dates_correctly()
    {
        // Arrange
        $socioData = [
            'nacimiento' => '1990-05-15',
            'alta' => '2020-01-01 10:30:00',
            'update_ts' => '2025-09-18 15:45:30'
        ];

        // Act
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');

        // Assert
        $this->assertEquals('1990-05-15', $result['nacimiento']->format('Y-m-d'));
        $this->assertEquals('2020-01-01', $result['alta']->format('Y-m-d'));
        $this->assertEquals('2025-09-18 15:45:30', $result['update_ts']->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_handles_invalid_dates_gracefully()
    {
        // Arrange
        $socioData = [
            'nacimiento' => 'invalid-date',
            'alta' => '2020-13-45', // Invalid date
        ];

        // Act
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');

        // Assert
        $this->assertNull($result['nacimiento']);
        $this->assertNull($result['alta']);
    }

    /** @test */
    public function it_builds_full_name_correctly()
    {
        // Both nombre and apellido
        $socioData = ['nombre' => 'Juan', 'apellido' => 'Pérez'];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertEquals('Pérez, Juan', $result['name']);

        // Only apellido
        $socioData = ['apellido' => 'Pérez'];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertEquals('Pérez', $result['name']);

        // Only nombre
        $socioData = ['nombre' => 'Juan'];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertEquals('Juan', $result['name']);

        // Neither
        $socioData = [];
        $result = $this->mappingService->mapSocioToUserData($socioData, '12345678', 'password');
        $this->assertEquals('Usuario API', $result['name']);
    }

    /** @test */
    public function it_maps_minimal_data_correctly()
    {
        // Arrange
        $socioData = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'socio_id' => 12345,
            'estado_socio' => 'ACTIVO',
            'semaforo' => 1
        ];

        // Act
        $result = $this->mappingService->mapSocioToMinimalData($socioData, '12345678');

        // Assert
        $this->assertEquals('12345678', $result['dni']);
        $this->assertEquals('Juan', $result['nombre']);
        $this->assertEquals('Pérez', $result['apellido']);
        $this->assertEquals(12345, $result['socio_id']);
        $this->assertEquals('ACTIVO', $result['estado_socio']);
        $this->assertEquals(1, $result['semaforo']);
    }

    /** @test */
    public function it_validates_mapped_data()
    {
        // Valid data
        $validData = [
            'dni' => '12345678',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'socio_id' => 12345,
            'estado_socio' => 'ACTIVO',
            'semaforo' => 1
        ];
        $errors = $this->mappingService->validateMappedData($validData);
        $this->assertEmpty($errors);

        // Invalid data - missing DNI
        $invalidData = [
            'nombre' => 'Juan',
            'socio_id' => 12345,
            'estado_socio' => 'ACTIVO',
            'semaforo' => 1
        ];
        $errors = $this->mappingService->validateMappedData($invalidData);
        $this->assertContains('DNI es requerido', $errors);

        // Invalid data - missing name
        $invalidData = [
            'dni' => '12345678',
            'socio_id' => 12345,
            'estado_socio' => 'ACTIVO',
            'semaforo' => 1
        ];
        $errors = $this->mappingService->validateMappedData($invalidData);
        $this->assertContains('Nombre o apellido es requerido', $errors);

        // Invalid data - invalid semaforo
        $invalidData = [
            'dni' => '12345678',
            'nombre' => 'Juan',
            'socio_id' => 12345,
            'estado_socio' => 'ACTIVO',
            'semaforo' => 5 // Invalid value
        ];
        $errors = $this->mappingService->validateMappedData($invalidData);
        $this->assertContains('Valor de semáforo inválido', $errors);
    }

    /** @test */
    public function it_handles_mapping_errors_gracefully()
    {
        // This test ensures that any exception during mapping is caught and re-thrown with context
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error procesando datos del socio');

        // Pass invalid data that might cause an exception
        $this->mappingService->mapSocioToUserData(null, '12345678', 'password');
    }
}
