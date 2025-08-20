# Contributing to QR Attendance System

First off, thank you for considering contributing to QR Attendance System! It's people like you that make this project great.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

- Use a clear and descriptive title
- Describe the exact steps which reproduce the problem
- Provide specific examples to demonstrate the steps
- Describe the behavior you observed after following the steps
- Explain which behavior you expected to see instead and why
- Include screenshots if possible
- Include your environment details (OS, PHP version, Node.js version, etc.)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- Use a clear and descriptive title
- Provide a step-by-step description of the suggested enhancement
- Provide specific examples to demonstrate the steps
- Describe the current behavior and explain which behavior you expected to see instead
- Explain why this enhancement would be useful

### Pull Requests

1. Fork the repo and create your branch from `dev`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code lints
6. Issue that pull request!

## Development Setup

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js >= 16
- PostgreSQL >= 12
- Git

### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Frontend Setup
```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

## Coding Standards

### Backend (Laravel/PHP)
- Follow PSR-12 coding standard
- Use meaningful variable and function names
- Write documentation for public methods
- Follow Laravel conventions
- Use type hints where possible

```php
/**
 * Create a new attendance record
 *
 * @param array $data
 * @return Attendance
 * @throws ValidationException
 */
public function createAttendance(array $data): Attendance
{
    // Implementation
}
```

### Frontend (Vue.js/JavaScript)
- Use ESLint configuration provided
- Follow Vue.js style guide
- Use Composition API for new components
- Write meaningful component names
- Use TypeScript when possible

```javascript
// Good
const handleSubmit = async () => {
  try {
    const response = await api.post('/users', formData.value)
    // Handle success
  } catch (error) {
    // Handle error
  }
}

// Bad
const submit = () => {
  api.post('/users', formData.value).then(resp => {
    // Handle response
  })
}
```

### CSS/Styling
- Use Tailwind CSS utility classes
- Follow mobile-first approach
- Use semantic class names for custom CSS
- Maintain consistency with existing design

## Testing

### Backend Testing
```bash
cd backend

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Frontend Testing
```bash
cd frontend

# Run unit tests
npm run test

# Run with coverage
npm run test:coverage

# Run in watch mode
npm run test:watch
```

### Writing Tests

#### Backend Tests
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserManagementTest extends TestCase
{
    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();
        
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'role' => 'security_guard'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'email', 'role'],
                'message'
            ]);
    }
}
```

#### Frontend Tests
```javascript
import { mount } from '@vue/test-utils'
import UserManagement from '@/views/admin/UserManagement.vue'

describe('UserManagement.vue', () => {
  it('renders user list correctly', () => {
    const wrapper = mount(UserManagement, {
      global: {
        stubs: ['router-link']
      }
    })

    expect(wrapper.find('h2').text()).toBe('Kelola Satpam')
  })

  it('opens create modal when add button is clicked', async () => {
    const wrapper = mount(UserManagement)
    
    await wrapper.find('[data-testid="add-user-button"]').trigger('click')
    
    expect(wrapper.vm.showModal).toBe(true)
  })
})
```

## Commit Messages

Use clear and meaningful commit messages:

```
feat: add QR code generation for locations
fix: resolve pagination issue in user management
docs: update API documentation for attendance endpoints
style: improve mobile responsiveness in dashboard
refactor: extract chart components to separate files
test: add unit tests for attendance service
chore: update dependencies
```

### Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

Types:
- `feat`: New features
- `fix`: Bug fixes
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

## Branch Naming

- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `hotfix/urgent-fix` - Urgent fixes
- `docs/documentation-update` - Documentation updates

## Pull Request Process

1. **Create a feature branch** from `dev`:
   ```bash
   git checkout dev
   git pull origin dev
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** following the coding standards

3. **Add tests** for new functionality

4. **Run tests** to ensure everything passes:
   ```bash
   # Backend
   cd backend && php artisan test
   
   # Frontend
   cd frontend && npm run test
   ```

5. **Update documentation** if needed

6. **Commit your changes** with clear commit messages

7. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

8. **Create a Pull Request** with:
   - Clear title and description
   - Reference any related issues
   - Include screenshots for UI changes
   - List any breaking changes

## Issue Templates

### Bug Report
```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment:**
- OS: [e.g. Windows 10]
- Browser: [e.g. chrome, safari]
- PHP Version: [e.g. 8.2]
- Node.js Version: [e.g. 18.0]

**Additional context**
Add any other context about the problem here.
```

### Feature Request
```markdown
**Is your feature request related to a problem? Please describe.**
A clear and concise description of what the problem is.

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Additional context**
Add any other context or screenshots about the feature request here.
```

## Getting Help

- Check the [README.md](README.md) for setup instructions
- Look at [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for API details
- Search existing issues before creating new ones
- Ask questions in GitHub Discussions
- Join our community Discord (if available)

## Recognition

Contributors will be recognized in:
- README.md contributors section
- Release notes for significant contributions
- GitHub contributors graph

Thank you for contributing to QR Attendance System! 🎉
