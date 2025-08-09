# Authentication Testing Documentation

## Overview

This document provides comprehensive guidelines for testing the authentication system in the HR System application. The authentication system uses JWT (JSON Web Tokens) for secure user authentication and authorization.

## Test Structure

The authentication test suite is organized into several categories:

### Backend Tests

#### 1. Integration Tests (`tests/Integration/Authentication/`)
- **AuthenticationFlowTest.php**: Complete authentication flow testing
- Tests the entire authentication process from login to protected resource access
- Covers user creation, login, token validation, and session management

#### 2. Functional Tests (`tests/Functional/Controller/`)
- **AuthControllerTest.php**: Controller-level authentication testing
- Tests API endpoints for login and authentication
- Validates request/response handling and HTTP status codes

#### 3. Security Tests (`tests/Security/`)
- **JwtSecurityTest.php**: JWT token security validation
- Tests token structure, expiration, tampering detection
- Validates security against common JWT vulnerabilities

### Frontend Tests

#### 1. End-to-End Tests (`frontend/cypress/e2e/`)
- **authentication.cy.js**: Complete user authentication flow
- Tests login form, navigation, protected routes
- Validates user experience and UI interactions

#### 2. Integration Tests (`frontend/tests/integration/`)
- **auth-flow.test.js**: Frontend authentication integration
- Tests authentication state management
- Validates API integration and error handling

## Test Scenarios

### 1. Successful Authentication Flow

#### Backend Testing
```php
// Test successful login with valid credentials
public function testCompleteAuthenticationFlow(): void
{
    // 1. Attempt access without authentication (should fail)
    // 2. Login with valid credentials
    // 3. Verify token structure and content
    // 4. Access protected resource with token
    // 5. Validate user data in response
}
```

#### Frontend Testing
```javascript
// Test successful login flow
it('should login successfully with valid credentials', () => {
  // 1. Fill login form
  // 2. Submit form
  // 3. Verify API call
  // 4. Check redirect to dashboard
  // 5. Validate authentication state
})
```

### 2. Failed Authentication Scenarios

#### Invalid Credentials
- Wrong email/password combinations
- Non-existent user accounts
- Empty or malformed credentials

#### Network and Server Errors
- Network connectivity issues
- Server errors (500, 503)
- Timeout scenarios

### 3. Token Security Testing

#### Token Validation
```php
public function testValidTokenStructure(): void
{
    // Verify JWT has 3 parts (header.payload.signature)
    // Validate base64url encoding
    // Check required claims (username, roles, exp, iat)
}
```

#### Security Vulnerabilities
- Token tampering detection
- Signature validation
- Algorithm confusion attacks
- Token replay attacks
- Timing attack prevention

### 4. Protected Route Access

#### Authenticated Access
- Valid token allows access to protected resources
- Role-based access control (RBAC)
- Token included in Authorization header

#### Unauthenticated Access
- Missing token returns 401 Unauthorized
- Invalid token returns 401 Unauthorized
- Expired token returns 401 Unauthorized

### 5. Session Management

#### Token Persistence
- Authentication state maintained across page reloads
- LocalStorage/SessionStorage handling
- Token refresh mechanisms

#### Logout Flow
- Token invalidation
- Session cleanup
- Redirect to login page

## Test Data and Fixtures

### Test Users
```php
// Valid test user
$testUser = User::create(
    new Email('test@example.com'),
    HashedPassword::fromPlainPassword('TestPassword123!'),
    'Test User'
);

// Admin test user
$adminUser = User::create(
    new Email('admin@example.com'),
    HashedPassword::fromPlainPassword('AdminPassword123!'),
    'Admin User'
);
$adminUser->addRole('ROLE_ADMIN');
```

### Mock API Responses
```javascript
// Successful login response
{
  token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
  user: {
    id: '1',
    email: 'test@example.com',
    name: 'Test User',
    roles: ['ROLE_USER']
  },
  message: 'Login successful'
}

// Error response
{
  message: 'Invalid credentials'
}
```

## Running Tests

### Backend Tests

#### All Authentication Tests
```bash
# Run all authentication-related tests
./vendor/bin/phpunit tests/Integration/Authentication/
./vendor/bin/phpunit tests/Functional/Controller/AuthControllerTest.php
./vendor/bin/phpunit tests/Security/JwtSecurityTest.php
```

#### Specific Test Categories
```bash
# Integration tests only
./vendor/bin/phpunit tests/Integration/Authentication/AuthenticationFlowTest.php

# Security tests only
./vendor/bin/phpunit tests/Security/JwtSecurityTest.php

# Functional tests only
./vendor/bin/phpunit tests/Functional/Controller/AuthControllerTest.php
```

#### With Coverage Report
```bash
# Generate coverage report
./vendor/bin/phpunit --coverage-html coverage/ tests/Integration/Authentication/
./vendor/bin/phpunit --coverage-html coverage/ tests/Security/
```

### Frontend Tests

#### Unit and Integration Tests
```bash
cd frontend

# Run all tests
npm run test

# Run specific test files
npm run test -- auth-flow.test.js
npm run test -- authService.spec.js

# Run with coverage
npm run test:coverage
```

#### End-to-End Tests
```bash
cd frontend

# Run all E2E tests
npm run test:e2e

# Run specific E2E test
npm run test:e2e -- --spec="**/authentication.cy.js"

# Run in headless mode
npm run test:e2e:headless
```

## Test Environment Setup

### Backend Environment
```bash
# Set up test database
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:create --env=test

# Load test fixtures
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

### Frontend Environment
```bash
# Install dependencies
npm install

# Set up test environment
cp .env.example .env.test
```

### Environment Variables
```bash
# Backend (.env.test)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private-test.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public-test.pem

# Frontend (.env.test)
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_ENV=test
```

## Coverage Requirements

### Minimum Coverage Targets
- **Overall Authentication Code**: 90%
- **Critical Security Functions**: 100%
- **API Endpoints**: 95%
- **Frontend Components**: 85%

### Coverage Areas
1. **Authentication Service**: Login, logout, token management
2. **Authorization**: Role-based access control
3. **Security**: Token validation, attack prevention
4. **Error Handling**: Network errors, validation errors
5. **UI Components**: Login form, navigation, protected routes

## Continuous Integration

### Pipeline Configuration
```yaml
# .github/workflows/tests.yml
test-authentication:
  runs-on: ubuntu-latest
  steps:
    - name: Run Backend Authentication Tests
      run: |
        ./vendor/bin/phpunit tests/Integration/Authentication/
        ./vendor/bin/phpunit tests/Security/JwtSecurityTest.php
        
    - name: Run Frontend Authentication Tests
      run: |
        cd frontend
        npm run test -- auth-flow.test.js
        npm run test:e2e -- --spec="**/authentication.cy.js"
```

### Quality Gates
- All authentication tests must pass
- Coverage must meet minimum thresholds
- Security tests must pass with 100% success rate
- E2E tests must pass in multiple browsers

## Security Testing Checklist

### JWT Token Security
- [ ] Token structure validation (3 parts)
- [ ] Signature verification
- [ ] Expiration time validation
- [ ] Claims validation (username, roles, exp, iat)
- [ ] Algorithm confusion prevention
- [ ] Token tampering detection
- [ ] Timing attack prevention

### Authentication Flow Security
- [ ] Password validation
- [ ] Rate limiting (login attempts)
- [ ] Account lockout mechanisms
- [ ] Secure password storage (hashing)
- [ ] Session management
- [ ] CSRF protection
- [ ] XSS prevention

### API Security
- [ ] HTTPS enforcement
- [ ] CORS configuration
- [ ] Input validation
- [ ] Output encoding
- [ ] Error message sanitization
- [ ] Request size limits

## Common Issues and Troubleshooting

### Test Failures

#### Database Connection Issues
```bash
# Reset test database
php bin/console doctrine:database:drop --force --env=test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:create --env=test
```

#### JWT Configuration Issues
```bash
# Regenerate JWT keys
mkdir -p config/jwt
openssl genpkey -out config/jwt/private-test.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private-test.pem -out config/jwt/public-test.pem -pubout
```

#### Frontend Test Issues
```bash
# Clear test cache
npm run test:clear-cache

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

### Performance Issues

#### Slow Test Execution
- Use in-memory database for tests
- Mock external API calls
- Optimize test data setup/teardown
- Run tests in parallel where possible

#### Memory Issues
- Clear test data after each test
- Use database transactions for isolation
- Limit test data size

## Best Practices

### Test Organization
1. **Group related tests** in describe blocks
2. **Use descriptive test names** that explain the scenario
3. **Follow AAA pattern**: Arrange, Act, Assert
4. **Keep tests independent** and isolated
5. **Use proper setup/teardown** for test data

### Test Data Management
1. **Use factories** for creating test data
2. **Clean up after tests** to prevent interference
3. **Use realistic test data** that matches production scenarios
4. **Avoid hardcoded values** where possible

### Security Testing
1. **Test both positive and negative scenarios**
2. **Include edge cases** and boundary conditions
3. **Validate error messages** don't leak sensitive information
4. **Test concurrent access** scenarios
5. **Verify timing consistency** to prevent timing attacks

### Maintenance
1. **Update tests** when authentication logic changes
2. **Review test coverage** regularly
3. **Keep documentation** up to date
4. **Monitor test performance** and optimize as needed

## Reporting and Metrics

### Test Reports
- Generate HTML coverage reports
- Track test execution time
- Monitor test failure rates
- Document security test results

### Metrics to Track
- Authentication test coverage percentage
- Number of security vulnerabilities found
- Test execution time trends
- False positive/negative rates

## Conclusion

This comprehensive testing strategy ensures the authentication system is secure, reliable, and user-friendly. Regular execution of these tests, combined with continuous monitoring and updates, maintains the security posture of the HR System application.

For questions or issues with authentication testing, refer to the development team or security team documentation.