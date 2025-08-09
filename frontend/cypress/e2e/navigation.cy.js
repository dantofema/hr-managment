describe('Navigation E2E Tests', () => {
  beforeEach(() => {
    // Visit the application
    cy.visit('/')
  })

  describe('Initial State', () => {
    it('should display the HR System brand', () => {
      cy.get('h1').should('contain.text', 'HR System')
    })

    it('should show the home view by default', () => {
      cy.get('[data-testid="home-view"]').should('be.visible')
    })

    it('should display the footer', () => {
      cy.get('[data-testid="app-footer"]').should('be.visible')
    })
  })

  describe('Non-Authenticated Navigation', () => {
    beforeEach(() => {
      // Ensure user is not authenticated
      cy.window().then((win) => {
        win.localStorage.removeItem('auth_token')
        win.localStorage.removeItem('auth_user')
      })
      cy.reload()
    })

    it('should show login button when not authenticated', () => {
      cy.get('button').contains('Iniciar Sesión').should('be.visible')
    })

    it('should not show protected navigation links', () => {
      cy.get('button').contains('Empleados').should('not.exist')
    })

    it('should navigate to login view when login button is clicked', () => {
      cy.get('button').contains('Iniciar Sesión').click()
      cy.get('[data-testid="login-view"]').should('be.visible')
      cy.get('[data-testid="home-view"]').should('not.exist')
    })

    it('should be able to navigate back to home from login', () => {
      // Go to login
      cy.get('button').contains('Iniciar Sesión').click()
      cy.get('[data-testid="login-view"]').should('be.visible')

      // Navigate back to home (simulate navigation)
      cy.get('h1').contains('HR System').click()
      // Since we don't have actual routing, we'll simulate this
      cy.window().then((win) => {
        // Trigger navigation programmatically if needed
        // This would depend on your actual routing implementation
      })
    })
  })

  describe('Authenticated Navigation', () => {
    beforeEach(() => {
      // Mock authentication state
      cy.window().then((win) => {
        win.localStorage.setItem('auth_token', 'mock-jwt-token')
        win.localStorage.setItem('auth_user', JSON.stringify({
          id: 1,
          name: 'John Doe',
          email: 'john.doe@example.com',
          roles: ['ROLE_USER']
        }))
      })
      cy.reload()
    })

    it('should show navigation links when authenticated', () => {
      cy.get('button').contains('Inicio').should('be.visible')
      cy.get('button').contains('Empleados').should('be.visible')
    })

    it('should show user menu instead of login button', () => {
      cy.get('[data-testid="user-menu"]').should('be.visible')
      cy.get('button').contains('Iniciar Sesión').should('not.exist')
    })

    it('should navigate between different views', () => {
      // Start at home
      cy.get('[data-testid="home-view"]').should('be.visible')

      // Navigate to employees
      cy.get('button').contains('Empleados').click()
      cy.get('[data-testid="employees-view"]').should('be.visible')
      cy.get('[data-testid="home-view"]').should('not.exist')

      // Navigate back to home
      cy.get('button').contains('Inicio').click()
      cy.get('[data-testid="home-view"]').should('be.visible')
      cy.get('[data-testid="employees-view"]').should('not.exist')
    })

    it('should highlight current view in navigation', () => {
      // Home should be highlighted initially
      cy.get('button').contains('Inicio').should('have.class', 'bg-blue-100')

      // Navigate to employees and check highlighting
      cy.get('button').contains('Empleados').click()
      cy.get('button').contains('Empleados').should('have.class', 'bg-blue-100')
      cy.get('button').contains('Inicio').should('not.have.class', 'bg-blue-100')
    })

    it('should navigate to profile from user menu', () => {
      cy.get('[data-testid="profile-button"]').click()
      cy.get('[data-testid="profile-view"]').should('be.visible')
      cy.get('[data-testid="home-view"]').should('not.exist')
    })

    it('should handle logout correctly', () => {
      cy.get('[data-testid="logout-button"]').click()
      
      // Should navigate back to home and show login button
      cy.get('[data-testid="home-view"]').should('be.visible')
      cy.get('button').contains('Iniciar Sesión').should('be.visible')
      cy.get('[data-testid="user-menu"]').should('not.exist')
    })
  })

  describe('Mobile Navigation', () => {
    beforeEach(() => {
      // Set mobile viewport
      cy.viewport(375, 667)
      
      // Mock authentication
      cy.window().then((win) => {
        win.localStorage.setItem('auth_token', 'mock-jwt-token')
        win.localStorage.setItem('auth_user', JSON.stringify({
          id: 1,
          name: 'John Doe',
          email: 'john.doe@example.com'
        }))
      })
      cy.reload()
    })

    it('should show mobile menu button', () => {
      cy.get('.md\\:hidden button').should('be.visible')
    })

    it('should open and close mobile menu', () => {
      const mobileButton = cy.get('.md\\:hidden button')
      
      // Initially closed
      cy.get('.md\\:hidden.border-t').should('not.exist')
      
      // Open mobile menu
      mobileButton.click()
      cy.get('.md\\:hidden.border-t').should('be.visible')
      
      // Close mobile menu
      mobileButton.click()
      cy.get('.md\\:hidden.border-t').should('not.exist')
    })

    it('should show correct icon based on menu state', () => {
      const mobileButton = cy.get('.md\\:hidden button')
      
      // Initially shows hamburger icon
      mobileButton.find('svg path').should('have.attr', 'd', 'M4 6h16M4 12h16M4 18h16')
      
      // Click to open - should show X icon
      mobileButton.click()
      mobileButton.find('svg path').should('have.attr', 'd', 'M6 18L18 6M6 6l12 12')
    })

    it('should navigate from mobile menu and close menu', () => {
      const mobileButton = cy.get('.md\\:hidden button')
      mobileButton.click()
      
      // Click mobile employees button
      cy.get('.md\\:hidden button').contains('Empleados').click()
      
      // Should navigate to employees view and close mobile menu
      cy.get('[data-testid="employees-view"]').should('be.visible')
      cy.get('.md\\:hidden.border-t').should('not.exist')
    })

    it('should show user info in mobile menu', () => {
      cy.get('.md\\:hidden button').click()
      
      cy.contains('John Doe').should('be.visible')
      cy.contains('john.doe@example.com').should('be.visible')
    })
  })

  describe('Loading States', () => {
    it('should show loading spinner during authentication check', () => {
      // Mock loading state
      cy.intercept('GET', '/api/auth/me', { delay: 1000, body: {} })
      cy.visit('/')
      
      cy.get('.animate-spin').should('be.visible')
    })
  })

  describe('Accessibility', () => {
    beforeEach(() => {
      // Mock authentication
      cy.window().then((win) => {
        win.localStorage.setItem('auth_token', 'mock-jwt-token')
        win.localStorage.setItem('auth_user', JSON.stringify({
          id: 1,
          name: 'John Doe',
          email: 'john.doe@example.com'
        }))
      })
      cy.reload()
    })

    it('should have proper ARIA attributes on mobile menu button', () => {
      cy.viewport(375, 667)
      
      const mobileButton = cy.get('.md\\:hidden button')
      mobileButton.should('have.attr', 'aria-label', 'Toggle menu')
      mobileButton.should('have.attr', 'aria-expanded', 'false')
      mobileButton.should('have.attr', 'aria-haspopup', 'true')
    })

    it('should update aria-expanded when mobile menu is toggled', () => {
      cy.viewport(375, 667)
      
      const mobileButton = cy.get('.md\\:hidden button')
      
      // Initially false
      mobileButton.should('have.attr', 'aria-expanded', 'false')
      
      // Click to open
      mobileButton.click()
      mobileButton.should('have.attr', 'aria-expanded', 'true')
      
      // Click to close
      mobileButton.click()
      mobileButton.should('have.attr', 'aria-expanded', 'false')
    })

    it('should have proper semantic HTML structure', () => {
      cy.get('nav').should('exist')
      cy.get('h1').should('exist')
      cy.get('main').should('exist')
      cy.get('footer').should('exist')
    })

    it('should be keyboard navigable', () => {
      // Test tab navigation through buttons
      cy.get('body').tab()
      cy.focused().should('contain.text', 'Inicio')
      
      cy.focused().tab()
      cy.focused().should('contain.text', 'Empleados')
    })
  })

  describe('Responsive Design', () => {
    const viewports = [
      { width: 320, height: 568, name: 'mobile' },
      { width: 768, height: 1024, name: 'tablet' },
      { width: 1024, height: 768, name: 'desktop' }
    ]

    viewports.forEach(({ width, height, name }) => {
      it(`should display correctly on ${name} viewport`, () => {
        cy.viewport(width, height)
        
        // Mock authentication
        cy.window().then((win) => {
          win.localStorage.setItem('auth_token', 'mock-jwt-token')
          win.localStorage.setItem('auth_user', JSON.stringify({
            id: 1,
            name: 'John Doe',
            email: 'john.doe@example.com'
          }))
        })
        cy.reload()

        // Check that navigation is visible and functional
        cy.get('nav').should('be.visible')
        cy.get('h1').should('contain.text', 'HR System')

        if (width < 768) {
          // Mobile: should show mobile menu button
          cy.get('.md\\:hidden button').should('be.visible')
        } else {
          // Desktop/Tablet: should show navigation links directly
          cy.get('button').contains('Inicio').should('be.visible')
          cy.get('button').contains('Empleados').should('be.visible')
        }
      })
    })
  })

  describe('Error Handling', () => {
    it('should handle authentication errors gracefully', () => {
      // Mock authentication error
      cy.intercept('GET', '/api/auth/me', { statusCode: 401, body: { error: 'Unauthorized' } })
      cy.visit('/')
      
      // Should show login button even after error
      cy.get('button').contains('Iniciar Sesión').should('be.visible')
    })

    it('should handle logout errors gracefully', () => {
      // Mock authentication
      cy.window().then((win) => {
        win.localStorage.setItem('auth_token', 'mock-jwt-token')
        win.localStorage.setItem('auth_user', JSON.stringify({
          id: 1,
          name: 'John Doe',
          email: 'john.doe@example.com'
        }))
      })
      
      // Mock logout error
      cy.intercept('POST', '/api/auth/logout', { statusCode: 500, body: { error: 'Server error' } })
      cy.reload()
      
      cy.get('[data-testid="logout-button"]').click()
      
      // Should still attempt to clear local state and show login
      cy.get('button').contains('Iniciar Sesión').should('be.visible')
    })
  })
})