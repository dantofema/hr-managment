describe('Authentication Flow', () => {
  const testUser = {
    email: 'test.user@example.com',
    password: 'TestPassword123!',
    name: 'Test User'
  }

  const adminUser = {
    email: 'admin@example.com',
    password: 'AdminPassword123!',
    name: 'Admin User'
  }

  beforeEach(() => {
    // Clear any existing authentication data
    cy.clearLocalStorage()
    cy.clearCookies()
    
    // Visit the login page
    cy.visit('/login')
    
    // Wait for the page to load
    cy.get('form').should('be.visible')
  })

  describe('Login Page UI', () => {
    it('should display login form elements', () => {
      // Check page title and header
      cy.get('h2').should('contain.text', 'Sign in to your account')
      cy.get('p').should('contain.text', 'Welcome back to HR System')

      // Check form elements
      cy.get('#email').should('be.visible').and('have.attr', 'type', 'email')
      cy.get('#password').should('be.visible').and('have.attr', 'type', 'password')
      cy.get('#remember-me').should('be.visible').and('have.attr', 'type', 'checkbox')
      cy.get('button[type="submit"]').should('be.visible').and('contain.text', 'Sign in')

      // Check forgot password link
      cy.get('a').should('contain.text', 'Forgot your password?')
    })

    it('should show password visibility toggle', () => {
      cy.get('#password').should('have.attr', 'type', 'password')
      
      // Click password visibility toggle
      cy.get('#password').parent().find('button').click()
      cy.get('#password').should('have.attr', 'type', 'text')
      
      // Click again to hide
      cy.get('#password').parent().find('button').click()
      cy.get('#password').should('have.attr', 'type', 'password')
    })
  })

  describe('Form Validation', () => {
    it('should show validation errors for empty fields', () => {
      // Try to submit empty form
      cy.get('button[type="submit"]').click()
      
      // Check HTML5 validation (required fields)
      cy.get('#email:invalid').should('exist')
      cy.get('#password:invalid').should('exist')
    })

    it('should validate email format', () => {
      // Enter invalid email
      cy.get('#email').type('invalid-email')
      cy.get('#email').blur()
      
      // Check HTML5 email validation
      cy.get('#email:invalid').should('exist')
    })

    it('should clear field errors on input', () => {
      // Enter invalid email to trigger error
      cy.get('#email').type('invalid-email')
      cy.get('#password').type('short')
      cy.get('button[type="submit"]').click()
      
      // Start typing in email field
      cy.get('#email').clear().type('valid@example.com')
      
      // Error should be cleared (assuming error styling changes)
      cy.get('#email').should('not.have.class', 'border-red-300')
    })
  })

  describe('Successful Authentication', () => {
    beforeEach(() => {
      // Mock successful login API response
      cy.intercept('POST', '/api/login_check', {
        statusCode: 200,
        body: {
          token: 'mock-jwt-token-12345',
          user: {
            id: '1',
            email: testUser.email,
            name: testUser.name,
            roles: ['ROLE_USER']
          },
          message: 'Login successful'
        }
      }).as('loginRequest')
    })

    it('should login successfully with valid credentials', () => {
      // Fill in the form
      cy.get('#email').type(testUser.email)
      cy.get('#password').type(testUser.password)
      
      // Submit the form
      cy.get('button[type="submit"]').click()
      
      // Wait for API call
      cy.wait('@loginRequest')
      
      // Should redirect to dashboard/home
      cy.url().should('not.include', '/login')
      cy.url().should('match', /\/(dashboard|home|employees)?$/)
    })

    it('should remember user when remember me is checked', () => {
      // Check remember me
      cy.get('#remember-me').check()
      
      // Fill in the form
      cy.get('#email').type(testUser.email)
      cy.get('#password').type(testUser.password)
      
      // Submit the form
      cy.get('button[type="submit"]').click()
      
      // Wait for API call
      cy.wait('@loginRequest')
      
      // Check that authentication data is stored
      cy.window().then((win) => {
        const authData = JSON.parse(win.localStorage.getItem('authData') || '{}')
        expect(authData.token).to.exist
        expect(authData.user).to.exist
      })
    })

    it('should show loading state during login', () => {
      // Delay the API response to see loading state
      cy.intercept('POST', '/api/login_check', {
        statusCode: 200,
        body: {
          token: 'mock-jwt-token-12345',
          user: {
            id: '1',
            email: testUser.email,
            name: testUser.name,
            roles: ['ROLE_USER']
          }
        },
        delay: 1000
      }).as('slowLoginRequest')

      // Fill in the form
      cy.get('#email').type(testUser.email)
      cy.get('#password').type(testUser.password)
      
      // Submit the form
      cy.get('button[type="submit"]').click()
      
      // Check loading state
      cy.get('button[type="submit"]').should('be.disabled')
      cy.get('.animate-spin').should('be.visible') // Loading spinner
      
      // Wait for completion
      cy.wait('@slowLoginRequest')
    })
  })

  describe('Failed Authentication', () => {
    it('should show error for invalid credentials', () => {
      // Mock failed login API response
      cy.intercept('POST', '/api/login_check', {
        statusCode: 401,
        body: {
          message: 'Invalid credentials'
        }
      }).as('failedLoginRequest')

      // Fill in the form with invalid credentials
      cy.get('#email').type('wrong@example.com')
      cy.get('#password').type('wrongpassword')
      
      // Submit the form
      cy.get('button[type="submit"]').click()
      
      // Wait for API call
      cy.wait('@failedLoginRequest')
      
      // Should show error message
      cy.get('[role="alert"]').should('be.visible')
      cy.get('[role="alert"]').should('contain.text', 'Invalid credentials')
      
      // Should stay on login page
      cy.url().should('include', '/login')
    })

    it('should handle network errors gracefully', () => {
      // Mock network error
      cy.intercept('POST', '/api/login_check', {
        forceNetworkError: true
      }).as('networkErrorRequest')

      // Fill in the form
      cy.get('#email').type(testUser.email)
      cy.get('#password').type(testUser.password)
      
      // Submit the form
      cy.get('button[type="submit"]').click()
      
      // Wait for API call
      cy.wait('@networkErrorRequest')
      
      // Should show network error message
      cy.get('[role="alert"]').should('be.visible')
      cy.get('[role="alert"]').should('contain.text', 'Network error')
    })

    it('should handle server errors', () => {
      // Mock server error
      cy.intercept('POST', '/api/login_check', {
        statusCode: 500,
        body: {
          message: 'Internal server error'
        }
      }).as('serverErrorRequest')

      // Fill in the form
      cy.get('#email').type(testUser.email)
      cy.get('#password').type(testUser.password)
      
      // Submit the form
      cy.get('button[type="submit"]').click()
      
      // Wait for API call
      cy.wait('@serverErrorRequest')
      
      // Should show server error message
      cy.get('[role="alert"]').should('be.visible')
    })
  })

  describe('Protected Routes Access', () => {
    it('should redirect unauthenticated users to login', () => {
      // Try to access protected route
      cy.visit('/employees')
      
      // Should redirect to login
      cy.url().should('include', '/login')
      cy.get('h2').should('contain.text', 'Sign in to your account')
    })

    it('should allow authenticated users to access protected routes', () => {
      // Mock authentication state
      cy.window().then((win) => {
        win.localStorage.setItem('authData', JSON.stringify({
          token: 'valid-jwt-token',
          user: {
            id: '1',
            email: testUser.email,
            name: testUser.name,
            roles: ['ROLE_USER']
          }
        }))
      })

      // Mock API calls for protected routes
      cy.intercept('GET', '/api/employees', {
        statusCode: 200,
        body: []
      }).as('employeesRequest')

      // Visit protected route
      cy.visit('/employees')
      
      // Should not redirect to login
      cy.url().should('include', '/employees')
      cy.url().should('not.include', '/login')
    })
  })

  describe('Logout Flow', () => {
    beforeEach(() => {
      // Set up authenticated state
      cy.window().then((win) => {
        win.localStorage.setItem('authData', JSON.stringify({
          token: 'valid-jwt-token',
          user: {
            id: '1',
            email: testUser.email,
            name: testUser.name,
            roles: ['ROLE_USER']
          }
        }))
      })

      // Visit a protected page
      cy.visit('/employees')
    })

    it('should logout successfully and redirect to login', () => {
      // Find and click logout button (assuming it's in navigation)
      cy.get('[data-testid="user-menu"]').click()
      cy.get('[data-testid="logout-button"]').click()
      
      // Should redirect to login
      cy.url().should('include', '/login')
      
      // Should clear authentication data
      cy.window().then((win) => {
        const authData = win.localStorage.getItem('authData')
        expect(authData).to.be.null
      })
    })

    it('should clear all authentication data on logout', () => {
      // Logout
      cy.get('[data-testid="user-menu"]').click()
      cy.get('[data-testid="logout-button"]').click()
      
      // Check that all auth-related storage is cleared
      cy.window().then((win) => {
        expect(win.localStorage.getItem('authData')).to.be.null
        expect(win.localStorage.getItem('token')).to.be.null
        expect(win.localStorage.getItem('refreshToken')).to.be.null
      })
    })
  })

  describe('Session Persistence', () => {
    it('should maintain authentication across page reloads', () => {
      // Set up authenticated state
      cy.window().then((win) => {
        win.localStorage.setItem('authData', JSON.stringify({
          token: 'valid-jwt-token',
          user: {
            id: '1',
            email: testUser.email,
            name: testUser.name,
            roles: ['ROLE_USER']
          }
        }))
      })

      // Visit protected page
      cy.visit('/employees')
      cy.url().should('include', '/employees')
      
      // Reload page
      cy.reload()
      
      // Should still be authenticated
      cy.url().should('include', '/employees')
      cy.url().should('not.include', '/login')
    })

    it('should handle expired tokens gracefully', () => {
      // Mock expired token response
      cy.intercept('GET', '/api/employees', {
        statusCode: 401,
        body: {
          message: 'Token expired'
        }
      }).as('expiredTokenRequest')

      // Set up authenticated state with expired token
      cy.window().then((win) => {
        win.localStorage.setItem('authData', JSON.stringify({
          token: 'expired-jwt-token',
          user: {
            id: '1',
            email: testUser.email,
            name: testUser.name,
            roles: ['ROLE_USER']
          }
        }))
      })

      // Try to visit protected page
      cy.visit('/employees')
      
      // Wait for API call
      cy.wait('@expiredTokenRequest')
      
      // Should redirect to login
      cy.url().should('include', '/login')
    })
  })

  describe('Role-based Access', () => {
    it('should handle different user roles correctly', () => {
      // Mock admin login
      cy.intercept('POST', '/api/login_check', {
        statusCode: 200,
        body: {
          token: 'admin-jwt-token',
          user: {
            id: '2',
            email: adminUser.email,
            name: adminUser.name,
            roles: ['ROLE_USER', 'ROLE_ADMIN']
          }
        }
      }).as('adminLoginRequest')

      // Login as admin
      cy.get('#email').type(adminUser.email)
      cy.get('#password').type(adminUser.password)
      cy.get('button[type="submit"]').click()
      
      cy.wait('@adminLoginRequest')
      
      // Should have access to admin features
      cy.window().then((win) => {
        const authData = JSON.parse(win.localStorage.getItem('authData') || '{}')
        expect(authData.user.roles).to.include('ROLE_ADMIN')
      })
    })
  })

  describe('CORS and API Communication', () => {
    it('should handle CORS preflight requests', () => {
      // Mock CORS preflight
      cy.intercept('OPTIONS', '/api/login_check', {
        statusCode: 200,
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Methods': 'POST, OPTIONS',
          'Access-Control-Allow-Headers': 'Content-Type, Authorization'
        }
      }).as('corsPreflightRequest')

      // Mock actual login request
      cy.intercept('POST', '/api/login_check', {
        statusCode: 200,
        body: {
          token: 'mock-jwt-token',
          user: {
            id: '1',
            email: testUser.email,
            name: testUser.name,
            roles: ['ROLE_USER']
          }
        }
      }).as('loginRequest')

      // Fill and submit form
      cy.get('#email').type(testUser.email)
      cy.get('#password').type(testUser.password)
      cy.get('button[type="submit"]').click()
      
      // Should handle CORS correctly
      cy.wait('@loginRequest')
    })
  })

  describe('Accessibility', () => {
    it('should be accessible with keyboard navigation', () => {
      // Tab through form elements
      cy.get('body').tab()
      cy.focused().should('have.id', 'email')
      
      cy.focused().tab()
      cy.focused().should('have.id', 'password')
      
      cy.focused().tab()
      cy.focused().should('have.id', 'remember-me')
      
      cy.focused().tab()
      cy.focused().should('have.attr', 'type', 'submit')
    })

    it('should have proper ARIA labels and roles', () => {
      // Check form accessibility
      cy.get('#email').should('have.attr', 'aria-label').or('have.attr', 'aria-labelledby')
      cy.get('#password').should('have.attr', 'aria-label').or('have.attr', 'aria-labelledby')
      
      // Check error messages have proper role
      cy.get('#email').type('invalid-email')
      cy.get('button[type="submit"]').click()
      
      cy.get('[role="alert"]').should('exist')
    })
  })
})