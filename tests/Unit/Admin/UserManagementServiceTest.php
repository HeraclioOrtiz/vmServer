<?php

namespace Tests\Unit\Admin;

use App\Services\Admin\UserManagementService;
use App\Services\Core\AuditService;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class UserManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserManagementService $userManagementService;
    private $mockAuditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockAuditService = Mockery::mock(AuditService::class);
        $this->userManagementService = new UserManagementService($this->mockAuditService);
    }

    /** @test */
    public function it_gets_filtered_users_with_pagination()
    {
        // Arrange
        User::factory()->count(25)->create();
        
        $filters = [
            'search' => '',
            'user_type' => [],
            'sort_by' => 'created_at',
            'sort_direction' => 'desc'
        ];

        // Act
        $result = $this->userManagementService->getFilteredUsers($filters, 10);

        // Assert
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    /** @test */
    public function it_filters_users_by_search_term()
    {
        // Arrange
        User::factory()->create(['name' => 'John Doe', 'dni' => '12345678']);
        User::factory()->create(['name' => 'Jane Smith', 'dni' => '87654321']);
        
        $filters = ['search' => 'John'];

        // Act
        $result = $this->userManagementService->getFilteredUsers($filters, 20);

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals('John Doe', $result->first()['name']);
    }

    /** @test */
    public function it_filters_users_by_type()
    {
        // Arrange
        User::factory()->create(['user_type' => UserType::LOCAL]);
        User::factory()->create(['user_type' => UserType::API]);
        
        $filters = ['user_type' => ['local']];

        // Act
        $result = $this->userManagementService->getFilteredUsers($filters, 20);

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals('local', $result->first()['user_type']);
    }

    /** @test */
    public function it_creates_user_successfully()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'dni' => '12345678',
            'email' => 'test@example.com',
            'password' => 'password123',
            'user_type' => 'local',
            'is_professor' => false,
            'is_admin' => false
        ];
        
        $creator = User::factory()->create(['is_admin' => true]);

        $this->mockAuditService
            ->shouldReceive('logCreate')
            ->once()
            ->with('user', Mockery::type('int'), Mockery::type('array'));

        // Act
        $user = $this->userManagementService->createUser($userData, $creator);

        // Assert
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('12345678', $user->dni);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertEquals('active', $user->account_status);
    }

    /** @test */
    public function it_prevents_non_super_admin_from_creating_admin_users()
    {
        // Arrange
        $userData = [
            'name' => 'Admin User',
            'dni' => '12345678',
            'is_admin' => true
        ];
        
        $creator = User::factory()->create(['is_admin' => true, 'permissions' => []]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only super admins can create admin users');
        
        $this->userManagementService->createUser($userData, $creator);
    }

    /** @test */
    public function it_updates_user_successfully()
    {
        // Arrange
        $user = User::factory()->create(['name' => 'Old Name']);
        $updater = User::factory()->create(['is_admin' => true]);
        
        $updateData = ['name' => 'New Name'];

        $this->mockAuditService
            ->shouldReceive('logUpdate')
            ->once()
            ->with('user', $user->id, Mockery::type('array'), Mockery::type('array'));

        // Act
        $updatedUser = $this->userManagementService->updateUser($user, $updateData, $updater);

        // Assert
        $this->assertEquals('New Name', $updatedUser->name);
    }

    /** @test */
    public function it_prevents_user_from_removing_own_admin_privileges()
    {
        // Arrange
        $user = User::factory()->create(['is_admin' => true]);
        $updateData = ['is_admin' => false];

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot remove your own admin privileges');
        
        $this->userManagementService->updateUser($user, $updateData, $user);
    }

    /** @test */
    public function it_suspends_user_successfully()
    {
        // Arrange
        $user = User::factory()->create(['account_status' => 'active']);
        $suspender = User::factory()->create(['is_admin' => true]);

        $this->mockAuditService
            ->shouldReceive('logUserSuspension')
            ->once()
            ->with($user->id, 'Test reason');

        // Act
        $suspendedUser = $this->userManagementService->suspendUser($user, $suspender, 'Test reason');

        // Assert
        $this->assertEquals('suspended', $suspendedUser->account_status);
        $this->assertStringContains('Test reason', $suspendedUser->admin_notes);
    }

    /** @test */
    public function it_prevents_user_from_suspending_themselves()
    {
        // Arrange
        $user = User::factory()->create(['is_admin' => true]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot suspend your own account');
        
        $this->userManagementService->suspendUser($user, $user);
    }

    /** @test */
    public function it_activates_user_successfully()
    {
        // Arrange
        $user = User::factory()->create(['account_status' => 'suspended']);

        $this->mockAuditService
            ->shouldReceive('logUserActivation')
            ->once()
            ->with($user->id);

        // Act
        $activatedUser = $this->userManagementService->activateUser($user);

        // Assert
        $this->assertEquals('active', $activatedUser->account_status);
    }

    /** @test */
    public function it_assigns_admin_role_successfully()
    {
        // Arrange
        $user = User::factory()->create(['is_admin' => false]);
        $assigner = User::factory()->create([
            'is_admin' => true, 
            'permissions' => ['super_admin']
        ]);
        $permissions = ['user_management', 'reports_access'];

        $this->mockAuditService
            ->shouldReceive('logRoleAssignment')
            ->once()
            ->with($user->id, 'admin', $permissions);

        // Act
        $adminUser = $this->userManagementService->assignAdminRole($user, $permissions, $assigner);

        // Assert
        $this->assertTrue($adminUser->is_admin);
        $this->assertEquals($permissions, $adminUser->permissions);
    }

    /** @test */
    public function it_removes_admin_role_successfully()
    {
        // Arrange
        $user = User::factory()->create(['is_admin' => true]);
        $remover = User::factory()->create([
            'is_admin' => true, 
            'permissions' => ['super_admin']
        ]);

        $this->mockAuditService
            ->shouldReceive('logRoleRemoval')
            ->once()
            ->with($user->id, 'admin');

        // Act
        $regularUser = $this->userManagementService->removeAdminRole($user, $remover);

        // Assert
        $this->assertFalse($regularUser->is_admin);
        $this->assertNull($regularUser->permissions);
    }

    /** @test */
    public function it_gets_user_stats()
    {
        // Arrange
        User::factory()->count(5)->create(['user_type' => UserType::LOCAL]);
        User::factory()->count(3)->create(['user_type' => UserType::API]);
        User::factory()->create(['is_professor' => true]);
        User::factory()->create(['is_admin' => true]);

        // Act
        $stats = $this->userManagementService->getUserStats();

        // Assert
        $this->assertEquals(10, $stats['overview']['total_users']);
        $this->assertEquals(5, $stats['by_type']['local_users']);
        $this->assertEquals(3, $stats['by_type']['api_users']);
        $this->assertEquals(1, $stats['by_role']['professors']);
        $this->assertEquals(1, $stats['by_role']['admins']);
    }

    /** @test */
    public function it_gets_filters_summary()
    {
        // Arrange
        User::factory()->count(10)->create();
        User::factory()->create(['is_professor' => true]);
        User::factory()->create(['is_admin' => true]);

        // Act
        $summary = $this->userManagementService->getFiltersSummary();

        // Assert
        $this->assertEquals(12, $summary['total_users']);
        $this->assertEquals(1, $summary['professors']);
        $this->assertEquals(1, $summary['admins']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
