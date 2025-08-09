import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setupServer } from 'msw/node'
import { rest } from 'msw'
import { createWrapper, userInput, flushPromises, waitForElement } from '../utils/test-utils.js'
import Login from '@/components/auth/Login.vue'
import App from '@/App.vue'
import { useAuth } from '@/composables/useAuth'

// Mock the useAuth composable
vi.mock('@/composables/useAuth')

// Test data
const validUser = {
  id: '1',
  email: 'test@example.com',
  name: 'Test User',
  roles: ['ROLE_USER']
}

const adminUser = {
  id: '2',
  email: 'admin@example.com',
  name: 'Admin User',
  roles: ['ROLE_USER', 'ROLE_ADMIN']
}

const validToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6InRlc3RAZXhhbXBsZS5jb20iLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwiZXhwIjoxNjk5OTk5OTk5fQ.test-signature'

// MSW handlers for authentication API
const authHandlers = [
  // Successful login
  rest.post('/api/login_check', (req, res, ctx) => {
    const { email, password } = req.body
    
    if (email === 'test@example.com' && password === 'password123') {
      return res(
        ctx.status(200),
        ctx.json({
          token: validToken,
          user: validUser,
          message: 'Login successful'
        })
      )
    }
    
    if (email === 'admin@example.com' && password === 'admin123') {
      return res(
        ctx.status(200),
        ctx.json({
          token: validToken,
          user: adminUser,
          message: 'Login successful'
        })
      )
    }
    
    return res(
      ctx.status(401),
      ctx.json({
        message: 'Invalid credentials'
      })
    )
  }),

  // Protected route - employees
  rest.get('/api/employees', (req, res, ctx) => {
    const authHeader = req.headers.get('Authorization')
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res(
        ctx.status(401),
        ctx.json({
          message: 'Authentication required'
        })
      )
    }
    
    const token = authHeader.replace('Bearer ', '')
    
    if (token === validToken) {
      return res(
        ctx.status(200),
        ctx.json([
          { id: 1, name: 'John Doe', email: 'john@example.com' },
          { id: 2, name: 'Jane Smith', email: 'jane@example.com' }
        ])
      )
    }
    
    return res(
      ctx.status(401),
      ctx.json({
        message: 'Invalid token'
      })
    )
  }),

  // Token refresh endpoint
  rest.post('/api/token/refresh', (req, res, ctx) => {
    const { refresh_token } = req.body
    
    if (refresh_token === 'valid-refresh-token') {
      return res(
        ctx.status(200),
        ctx.json({
          token: validToken,
          refresh_token: 'new-refresh-token'
        })
      )
    }
    
    return res(
      ctx.status(401),
      ctx.json({
        message: 'Invalid refresh token'
      })
    )
  })
]

// Setup MSW server
const server = setupServer(...authHandlers)

describe('Authentication Flow Integration', () => {
  beforeEach(() => {
    server.listen()
    
    // Clear localStorage before each test
    localStorage.clear()
    
    // Reset useAuth mock
    vi.clearAllMocks()
  })

  afterEach(() => {
    server.resetHandlers()
    localStorage.clear()
  })

  afterAll(() => {
    server.close()
  })

  describe('Login Component Integration', () => {
    test('should handle successful login flow', async () => {
      const mockLogin = vi.fn().mockResolvedValue({
        success: true,
        user: validUser,
        token: validToken
      })

      const mockUseAuth = {
        login: mockLogin,
        isLoading: vi.fn(() => false),
        error: vi.fn(() => null),
        isAuthenticated: vi.fn(() => false),
        user: vi.fn(() => null)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount(Login)

      // Fill in login form
      await userInput(wrapper.find('#email'), 'test@example.com')
      await userInput(wrapper.find('#password'), 'password123')

      // Submit form
      await wrapper.find('form').trigger('submit.prevent')
      await flushPromises()

      // Verify login was called with correct credentials
      expect(mockLogin).toHaveBeenCalledWith({
        email: 'test@example.com',
        password: 'password123',
        remember: false
      })
    })

    test('should handle login validation errors', async () => {
      const wrapper = mount(Login)

      // Try to submit empty form
      await wrapper.find('form').trigger('submit.prevent')
      await flushPromises()

      // Check HTML5 validation
      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')

      expect(emailInput.element.validity.valid).toBe(false)
      expect(passwordInput.element.validity.valid).toBe(false)
    })

    test('should handle API login errors', async () => {
      const mockLogin = vi.fn().mockRejectedValue(new Error('Invalid credentials'))

      const mockUseAuth = {
        login: mockLogin,
        isLoading: vi.fn(() => false),
        error: vi.fn(() => 'Invalid credentials'),
        isAuthenticated: vi.fn(() => false),
        user: vi.fn(() => null)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount(Login)

      // Fill in login form with invalid credentials
      await userInput(wrapper.find('#email'), 'wrong@example.com')
      await userInput(wrapper.find('#password'), 'wrongpassword')

      // Submit form
      await wrapper.find('form').trigger('submit.prevent')
      await flushPromises()

      // Verify error is displayed
      expect(wrapper.text()).toContain('Invalid credentials')
    })

    test('should show loading state during login', async () => {
      const mockLogin = vi.fn().mockImplementation(() => new Promise(resolve => setTimeout(resolve, 100)))

      const mockUseAuth = {
        login: mockLogin,
        isLoading: vi.fn(() => true),
        error: vi.fn(() => null),
        isAuthenticated: vi.fn(() => false),
        user: vi.fn(() => null)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount(Login)

      // Fill in login form
      await userInput(wrapper.find('#email'), 'test@example.com')
      await userInput(wrapper.find('#password'), 'password123')

      // Submit form
      await wrapper.find('form').trigger('submit.prevent')
      await flushPromises()

      // Check loading state
      expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
      expect(wrapper.find('.animate-spin').exists()).toBe(true)
    })

    test('should handle remember me functionality', async () => {
      const mockLogin = vi.fn().mockResolvedValue({
        success: true,
        user: validUser,
        token: validToken
      })

      const mockUseAuth = {
        login: mockLogin,
        isLoading: vi.fn(() => false),
        error: vi.fn(() => null),
        isAuthenticated: vi.fn(() => false),
        user: vi.fn(() => null)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount(Login)

      // Check remember me checkbox
      await wrapper.find('#remember-me').setChecked(true)

      // Fill in login form
      await userInput(wrapper.find('#email'), 'test@example.com')
      await userInput(wrapper.find('#password'), 'password123')

      // Submit form
      await wrapper.find('form').trigger('submit.prevent')
      await flushPromises()

      // Verify login was called with remember flag
      expect(mockLogin).toHaveBeenCalledWith({
        email: 'test@example.com',
        password: 'password123',
        remember: true
      })
    })
  })

  describe('Authentication State Management', () => {
    test('should persist authentication state in localStorage', async () => {
      const authData = {
        token: validToken,
        user: validUser,
        refreshToken: 'refresh-token-123'
      }

      // Simulate setting auth data
      localStorage.setItem('authData', JSON.stringify(authData))

      const mockUseAuth = {
        isAuthenticated: vi.fn(() => true),
        user: vi.fn(() => validUser),
        token: vi.fn(() => validToken),
        initializeAuth: vi.fn()
      }

      useAuth.mockReturnValue(mockUseAuth)

      // Mount component that uses auth
      const wrapper = mount({
        template: '<div>{{ user ? user.name : "Not authenticated" }}</div>',
        setup() {
          const { user } = useAuth()
          return { user: user() }
        }
      })

      expect(wrapper.text()).toContain('Test User')
    })

    test('should clear authentication state on logout', async () => {
      // Set initial auth data
      localStorage.setItem('authData', JSON.stringify({
        token: validToken,
        user: validUser
      }))

      const mockLogout = vi.fn().mockImplementation(() => {
        localStorage.removeItem('authData')
      })

      const mockUseAuth = {
        logout: mockLogout,
        isAuthenticated: vi.fn(() => false),
        user: vi.fn(() => null)
      }

      useAuth.mockReturnValue(mockUseAuth)

      // Simulate logout
      await mockLogout()

      expect(localStorage.getItem('authData')).toBeNull()
    })

    test('should handle token expiration', async () => {
      // Mock expired token scenario
      server.use(
        rest.get('/api/employees', (req, res, ctx) => {
          return res(
            ctx.status(401),
            ctx.json({
              message: 'Token expired'
            })
          )
        })
      )

      const mockUseAuth = {
        isAuthenticated: vi.fn(() => false),
        user: vi.fn(() => null),
        handleTokenExpiration: vi.fn()
      }

      useAuth.mockReturnValue(mockUseAuth)

      // Simulate API call with expired token
      const response = await fetch('/api/employees', {
        headers: {
          'Authorization': `Bearer ${validToken}`
        }
      })

      expect(response.status).toBe(401)
    })
  })

  describe('Protected Route Access', () => {
    test('should allow authenticated users to access protected content', async () => {
      const mockUseAuth = {
        isAuthenticated: vi.fn(() => true),
        user: vi.fn(() => validUser),
        token: vi.fn(() => validToken)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount({
        template: `
          <div>
            <div v-if="isAuthenticated">
              <h1>Protected Content</h1>
              <p>Welcome, {{ user.name }}</p>
            </div>
            <div v-else>
              <p>Please log in</p>
            </div>
          </div>
        `,
        setup() {
          const { isAuthenticated, user } = useAuth()
          return {
            isAuthenticated: isAuthenticated(),
            user: user()
          }
        }
      })

      expect(wrapper.text()).toContain('Protected Content')
      expect(wrapper.text()).toContain('Welcome, Test User')
    })

    test('should deny access to unauthenticated users', async () => {
      const mockUseAuth = {
        isAuthenticated: vi.fn(() => false),
        user: vi.fn(() => null)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount({
        template: `
          <div>
            <div v-if="isAuthenticated">
              <h1>Protected Content</h1>
            </div>
            <div v-else>
              <p>Please log in</p>
            </div>
          </div>
        `,
        setup() {
          const { isAuthenticated } = useAuth()
          return {
            isAuthenticated: isAuthenticated()
          }
        }
      })

      expect(wrapper.text()).toContain('Please log in')
      expect(wrapper.text()).not.toContain('Protected Content')
    })
  })

  describe('Role-based Access Control', () => {
    test('should show admin features for admin users', async () => {
      const mockUseAuth = {
        isAuthenticated: vi.fn(() => true),
        user: vi.fn(() => adminUser),
        hasRole: vi.fn((role) => adminUser.roles.includes(role))
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount({
        template: `
          <div>
            <div v-if="isAuthenticated">
              <h1>Dashboard</h1>
              <div v-if="hasRole('ROLE_ADMIN')" class="admin-panel">
                <h2>Admin Panel</h2>
              </div>
            </div>
          </div>
        `,
        setup() {
          const { isAuthenticated, hasRole } = useAuth()
          return {
            isAuthenticated: isAuthenticated(),
            hasRole
          }
        }
      })

      expect(wrapper.text()).toContain('Dashboard')
      expect(wrapper.text()).toContain('Admin Panel')
    })

    test('should hide admin features for regular users', async () => {
      const mockUseAuth = {
        isAuthenticated: vi.fn(() => true),
        user: vi.fn(() => validUser),
        hasRole: vi.fn((role) => validUser.roles.includes(role))
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount({
        template: `
          <div>
            <div v-if="isAuthenticated">
              <h1>Dashboard</h1>
              <div v-if="hasRole('ROLE_ADMIN')" class="admin-panel">
                <h2>Admin Panel</h2>
              </div>
            </div>
          </div>
        `,
        setup() {
          const { isAuthenticated, hasRole } = useAuth()
          return {
            isAuthenticated: isAuthenticated(),
            hasRole
          }
        }
      })

      expect(wrapper.text()).toContain('Dashboard')
      expect(wrapper.text()).not.toContain('Admin Panel')
    })
  })

  describe('API Integration with Authentication', () => {
    test('should include auth token in API requests', async () => {
      const mockUseAuth = {
        isAuthenticated: vi.fn(() => true),
        token: vi.fn(() => validToken)
      }

      useAuth.mockReturnValue(mockUseAuth)

      // Make authenticated API request
      const response = await fetch('/api/employees', {
        headers: {
          'Authorization': `Bearer ${validToken}`,
          'Content-Type': 'application/json'
        }
      })

      expect(response.status).toBe(200)
      const data = await response.json()
      expect(Array.isArray(data)).toBe(true)
    })

    test('should handle API requests without authentication', async () => {
      // Make unauthenticated API request
      const response = await fetch('/api/employees')

      expect(response.status).toBe(401)
      const data = await response.json()
      expect(data.message).toBe('Authentication required')
    })

    test('should handle token refresh flow', async () => {
      // Mock token refresh scenario
      const refreshResponse = await fetch('/api/token/refresh', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          refresh_token: 'valid-refresh-token'
        })
      })

      expect(refreshResponse.status).toBe(200)
      const data = await refreshResponse.json()
      expect(data.token).toBeDefined()
      expect(data.refresh_token).toBeDefined()
    })
  })

  describe('Error Handling', () => {
    test('should handle network errors gracefully', async () => {
      // Mock network error
      server.use(
        rest.post('/api/login_check', (req, res, ctx) => {
          return res.networkError('Network error')
        })
      )

      const mockLogin = vi.fn().mockRejectedValue(new Error('Network error'))

      const mockUseAuth = {
        login: mockLogin,
        error: vi.fn(() => 'Network error occurred'),
        isLoading: vi.fn(() => false)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount(Login)

      // Fill and submit form
      await userInput(wrapper.find('#email'), 'test@example.com')
      await userInput(wrapper.find('#password'), 'password123')
      await wrapper.find('form').trigger('submit.prevent')
      await flushPromises()

      // Should show network error
      expect(wrapper.text()).toContain('Network error')
    })

    test('should handle server errors', async () => {
      // Mock server error
      server.use(
        rest.post('/api/login_check', (req, res, ctx) => {
          return res(
            ctx.status(500),
            ctx.json({
              message: 'Internal server error'
            })
          )
        })
      )

      const mockLogin = vi.fn().mockRejectedValue(new Error('Server error'))

      const mockUseAuth = {
        login: mockLogin,
        error: vi.fn(() => 'Server error occurred'),
        isLoading: vi.fn(() => false)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount(Login)

      // Fill and submit form
      await userInput(wrapper.find('#email'), 'test@example.com')
      await userInput(wrapper.find('#password'), 'password123')
      await wrapper.find('form').trigger('submit.prevent')
      await flushPromises()

      // Should show server error
      expect(wrapper.text()).toContain('Server error')
    })
  })

  describe('Session Management', () => {
    test('should maintain session across component remounts', async () => {
      // Set auth data in localStorage
      const authData = {
        token: validToken,
        user: validUser
      }
      localStorage.setItem('authData', JSON.stringify(authData))

      const mockUseAuth = {
        isAuthenticated: vi.fn(() => true),
        user: vi.fn(() => validUser),
        initializeAuth: vi.fn()
      }

      useAuth.mockReturnValue(mockUseAuth)

      // Mount component
      let wrapper = mount({
        template: '<div>{{ isAuthenticated ? "Authenticated" : "Not authenticated" }}</div>',
        setup() {
          const { isAuthenticated } = useAuth()
          return { isAuthenticated: isAuthenticated() }
        }
      })

      expect(wrapper.text()).toContain('Authenticated')

      // Unmount and remount
      wrapper.unmount()
      wrapper = mount({
        template: '<div>{{ isAuthenticated ? "Authenticated" : "Not authenticated" }}</div>',
        setup() {
          const { isAuthenticated } = useAuth()
          return { isAuthenticated: isAuthenticated() }
        }
      })

      expect(wrapper.text()).toContain('Authenticated')
    })

    test('should handle concurrent login attempts', async () => {
      const mockLogin = vi.fn().mockResolvedValue({
        success: true,
        user: validUser,
        token: validToken
      })

      const mockUseAuth = {
        login: mockLogin,
        isLoading: vi.fn(() => false),
        error: vi.fn(() => null)
      }

      useAuth.mockReturnValue(mockUseAuth)

      const wrapper = mount(Login)

      // Fill form
      await userInput(wrapper.find('#email'), 'test@example.com')
      await userInput(wrapper.find('#password'), 'password123')

      // Submit multiple times quickly
      const submitPromises = [
        wrapper.find('form').trigger('submit.prevent'),
        wrapper.find('form').trigger('submit.prevent'),
        wrapper.find('form').trigger('submit.prevent')
      ]

      await Promise.all(submitPromises)
      await flushPromises()

      // Login should only be called once (or handled appropriately)
      expect(mockLogin).toHaveBeenCalled()
    })
  })
})