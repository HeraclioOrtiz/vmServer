# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Villa Mitre Server is a Laravel 12 API backend for a gym management system for Club Villa Mitre. The system integrates with an external club member API and provides a comprehensive gym management platform with user authentication, admin panels, professor management, and student workout tracking.

## Core Architecture

### Service-Oriented Architecture
The codebase follows a modular service-based architecture organized by domain:

- **`app/Services/Auth/`** - Authentication services (login, registration, password validation)
- **`app/Services/Admin/`** - Admin panel services (user management, professor management, audit logs)
- **`app/Services/Gym/`** - Gym functionality (templates, assignments, exercises)
- **`app/Services/User/`** - User operations (refresh, promotion)
- **`app/Services/External/`** - External API integration (Socios API, data mapping)
- **`app/Services/Core/`** - Core system services (cache, audit logging)

**Controllers are lightweight** - they handle validation and delegate business logic to services. When implementing new features, create or use existing services rather than adding logic to controllers.

### Key Design Patterns
- **Orchestration Pattern**: `AuthService` orchestrates specialized services
- **Single Responsibility**: Each service has one clear purpose
- **Dependency Injection**: Services are injected via constructors

### Dual User Type System
The system manages two types of users:
- **Local Users** (`user_type: 'local'`): Registered directly in the system
- **API Users** (`user_type: 'api'`): Synchronized from external club API (`SOCIOS_API_BASE`)

Users can be promoted from local to API via `UserPromotionService`. The system auto-refreshes API user data every 24 hours (configurable via `SOCIOS_REFRESH_HOURS`).

### Role System
- **Regular User**: Basic authenticated user
- **Student** (`student_gym: true`): Can access gym functionality
- **Professor** (`is_professor: true`): Can manage students and create templates
- **Admin** (`is_admin: true`): Full system access

## Database

**Primary Database**: MySQL 8.0+ in production, SQLite in testing

### Key Models and Relationships

**Users** (`users` table):
- Has roles: `is_admin`, `is_professor`, `student_gym`
- Contains both local and external API data
- Links to `personal_access_tokens` (Sanctum)

**Gym System** (all in `gym_*` tables):
- **Exercises** → **DailyTemplates** → **WeeklyTemplates** → **WeeklyAssignments**
- **ProfessorStudentAssignment**: Links professors to students
- **DailyTemplateSet**: Individual sets within exercises (reps, weight, rest_time)
- **AssignedSet**: Copies of sets for student assignments

### Critical Migration Pattern
When modifying user-related tables, ensure compatibility with the external API sync. The system maps external API fields (like `socio_n`, `socio_id`, `barcode`) to internal fields.

## Development Commands

### Setup
```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure gym settings (in .env)
GYM_DEFAULT_PROFESSOR_DNI=22222222  # TEMPORAL: Auto-assign students
GYM_AUTO_ASSIGN_STUDENTS=true       # Enable/disable auto-assignment

# Database
php artisan migrate:fresh --seed

# Create admin user (uses external script)
php create_admin_user.php

# Start server
php artisan serve
```

### Development
```bash
# Run development environment (server + queue + logs + vite)
composer dev

# Clear caches (important after config changes)
php artisan cache:clear
php artisan config:clear
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# With coverage
php artisan test --coverage

# Run single test file
php artisan test tests/Unit/Auth/AuthenticationServiceTest.php
```

### Artisan Commands
```bash
# Make user a professor
php artisan make:professor {user_id}

# Assign all students to a professor (temporary helper)
php artisan assign:students-to-professor {professor_id}

# Delete user (for testing)
php artisan delete:user {user_id}
```

## API Structure

### Authentication
All API routes use Sanctum token authentication (`auth:sanctum` middleware).

**Auth Endpoints** (`/api/auth/*`):
- `POST /api/auth/login` - Authenticate with DNI and password
- `POST /api/auth/register` - Register new local user
- `GET /api/auth/me` - Get current authenticated user
- `POST /api/auth/logout` - Invalidate token

### Route Organization
Routes are split across files:
- `routes/api.php` - Main API routes (auth, gym, mobile)
- `routes/admin.php` - Admin panel routes (protected by `admin` middleware)

**Middleware Stack**:
- `auth:sanctum` - Requires authentication
- `admin` - Requires `is_admin = true`
- `professor` - Requires `is_professor = true`

### Admin Panel Routes (`/api/admin/*`)
Protected by `admin` middleware. Includes user management, professor management, audit logs, and system settings.

### Gym Routes
- `/api/admin/gym/*` - Professor/admin management (exercises, templates, assignments) - requires `professor` middleware
- `/api/gym/*` - Student mobile API (my-week, my-day)
- `/api/professor/*` - Professor-specific routes (my-students, assign-template)

## Critical Implementation Notes

### Password Validation Service
The `PasswordValidationService` prevents critical crashes when validating passwords. **Always use this service** when checking passwords - never call `Hash::check()` directly. The service handles null values and malformed data safely.

Location: `app/Services/Auth/PasswordValidationService.php:93`

### External API Integration
The `SociosApi` service integrates with the club's member system:
- Base URL configured in `SOCIOS_API_BASE`
- Uses circuit breaker pattern for fault tolerance
- Data mapping handled by `SocioDataMappingService`

**When users authenticate**, the system checks if they exist in the external API and auto-syncs their data if needed.

### Cache Strategy
User data is cached via `CacheService` to reduce API calls:
- Cache key: `user:{dni}`
- TTL: Configured via `SOCIOS_CACHE_TTL` (default 3600s)
- Auto-refresh on authentication if data is stale

### Gym Template System
Templates follow this hierarchy:
1. **Exercise**: Base exercise definition (name, type, description)
2. **DailyTemplate**: Collection of exercises for one day
3. **DailyTemplateSet**: Individual sets within exercises (reps, weight, rest_time)
4. **WeeklyTemplate**: 7 daily templates (one per day)
5. **WeeklyAssignment**: Assigns a weekly template to a student with start_date

**Important**: Sets are copied to `AssignedSet` when assignments are created. Editing templates doesn't affect existing assignments.

### Weight Validation
When creating or updating sets, weight values must be validated:
- Can be null (bodyweight exercises)
- Must be numeric if provided
- Decimal values allowed

Locations:
- `app/Services/Gym/SetService.php` - Handles set updates
- `app/Http/Requests/Gym/ExerciseRequest.php` - Validates exercise data

## Testing Guidelines

### Test Structure
- **Unit tests** (`tests/Unit/`) - Test individual services in isolation
- **Feature tests** (`tests/Feature/`) - Test full request/response cycles

### Critical Test Suites
These test suites prevent production crashes:
- `AuthenticationServiceTest` - Password validation edge cases
- `PasswordValidationServiceTest` - Null/malformed password handling
- `UserManagementServiceTest` - Role assignment validation
- `SocioDataMappingServiceTest` - External API data validation

### Test Database
Tests use SQLite in-memory database (configured in `phpunit.xml`). Migrations run automatically before tests.

## Common Patterns

### Creating New API Endpoints
1. Add route to `routes/api.php` or `routes/admin.php`
2. Create/use controller in `app/Http/Controllers/`
3. Create request validation class in `app/Http/Requests/`
4. Implement business logic in service class in `app/Services/`
5. Write feature test in `tests/Feature/`
6. Write unit test for service in `tests/Unit/`

### Adding New Service
```php
namespace App\Services\[Domain];

class NewService
{
    public function __construct(
        private DependencyService $dependency
    ) {}

    public function performAction(array $data): Result
    {
        // Business logic here
    }
}
```

Register in `AppServiceProvider` if needed for dependency injection.

### Audit Logging
Use `AuditService` for important actions:
```php
$this->auditService->log(
    action: 'user.updated',
    userId: $user->id,
    performedBy: auth()->id(),
    metadata: ['changes' => $changes]
);
```

## Deployment Notes

### Environment Configuration
Critical `.env` variables:
- `SOCIOS_API_BASE` - External club API URL
- `SOCIOS_API_LOGIN` - API credentials
- `SOCIOS_REFRESH_HOURS` - How often to sync API users (default: 24)
- `DB_CONNECTION=mysql` - Use MySQL in production

**Gym Configuration** (see `config/gym.php`):
- `GYM_DEFAULT_PROFESSOR_DNI` - Professor DNI for auto-assignment (TEMPORAL)
- `GYM_AUTO_ASSIGN_STUDENTS` - Enable/disable auto-assignment (false by default)
- `GYM_MAX_EXERCISES` - Max exercises per template (default: 20)
- `GYM_MAX_STUDENTS` - Max students per professor (default: 50)

### Production Checklist
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 775 storage bootstrap/cache
```

See `docs/deployment/PRODUCTION-DEPLOYMENT-GUIDE.md` for full deployment guide.

## Documentation

Comprehensive documentation is in the `docs/` directory:
- `docs/architecture/SERVICES-ARCHITECTURE.md` - Service architecture details
- `docs/api/API-DOCUMENTATION.md` - Complete API reference
- `docs/gym/GYM-DOCUMENTATION.md` - Gym system documentation
- `docs/testing/TESTING-GUIDE-MAIN.md` - Testing guidelines
- `docs/admin-panel/` - Admin panel implementation guides

## Troubleshooting

### Common Issues

**Login returns 500 error**: Check database connection and verify `PasswordValidationService` is being used

**API user data not syncing**: Verify `SOCIOS_API_BASE` and credentials in `.env`

**Tests failing**: Run `php artisan config:clear` before running tests

**Permission denied errors**: Ensure `storage/` and `bootstrap/cache/` are writable
