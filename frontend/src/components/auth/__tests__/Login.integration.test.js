import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import Login from '../Login.vue'
import authService from '@/services/authService'
import * as tokenStorage from '@/utils/tokenStorage'

// Mock dependencies
vi.mock('@/services/authService')
vi.mock('@/utils/tokenStorage')

// Mock window.location
Object.defineProperty(window, 'location', {
  value: {
    href: ''
  },
  writable: true
})

describe('Login Integration Tests', () => {
  let wrapper
  let mockOnLoginSuccess

  beforeEach(() => {
    // Reset window.location.href
    window.location.href = ''

    // Mock login success callback
    mockOnLoginSuccess = vi.fn()

    // Mock token storage
    vi.mocked(tokenStorage.getAuthData).mockReturnValue({
      token: null,
      refreshToken: null,
      user: null
    })
    vi.mocked(tokenStorage.isAuthenticated).mockReturnValue(false)
    vi.mocked(tokenStorage.isTokenExpired).mockReturnValue(true)

    // Mount component
    wrapper = mount(Login, {
      props: {
        onLoginSuccess: mockOnLoginSuccess,
        redirectUrl: '/dashboard'
      }
    })
  })

  afterEach(() => {
    wrapper.unmount()
    vi.clearAllMocks()
  })

  describe('Successful Login Flow', () => {
    it('completes full login flow successfully', async () => {
      // Mock successful login response
      const mockLoginResponse = {
        success: true,
        data: {
          token: 'mock-jwt-token',
          refreshToken: 'mock-refresh-token',
          user: {
            id: 1,
            email: 'test@example.com',
            name: 'Test User',
            roles: ['ROLE_USER']
          }
        }
      }
      vi.mocked(authService.login).mockResolvedValue(mockLoginResponse)

      // Fill and submit form
      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      // Verify auth service was called
      expect(authService.login).toHaveBeenCalledWith('test@example.com', 'password123')

      // Verify token storage was called
      expect(tokenStorage.setAuthData).toHaveBeenCalledWith({
        token: 'mock-jwt-token',
        refreshToken: 'mock-refresh-token',
        user: mockLoginResponse.data.user
      })

      // Verify callback was called
      expect(mockOnLoginSuccess).toHaveBeenCalledWith(mockLoginResponse.data.user, '/dashboard')
    })

    it('redirects to intended route after successful login', async () => {
      // Mount component with different redirect URL
      wrapper.unmount()
      wrapper = mount(Login, {
        props: {
          onLoginSuccess: mockOnLoginSuccess,
          redirectUrl: '/employees'
        }
      })

      const mockLoginResponse = {
        success: true,
        data: {
          token: 'mock-jwt-token',
          user: { id: 1, email: 'test@example.com' }
        }
      }
      vi.mocked(authService.login).mockResolvedValue(mockLoginResponse)

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      expect(mockOnLoginSuccess).toHaveBeenCalledWith(mockLoginResponse.data.user, '/employees')
    })
  })

  describe('Failed Login Flow', () => {
    it('handles login failure with error message', async () => {
      const mockErrorResponse = {
        success: false,
        error: 'Invalid credentials',
        status: 401
      }
      vi.mocked(authService.login).mockResolvedValue(mockErrorResponse)

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('wrongpassword')
      await form.trigger('submit.prevent')
      await nextTick()

      // Verify error is displayed
      expect(wrapper.text()).toContain('Invalid credentials')

      // Verify no token storage occurred
      expect(tokenStorage.setAuthData).not.toHaveBeenCalled()

      // Verify no callback was called
      expect(mockOnLoginSuccess).not.toHaveBeenCalled()
    })

    it('handles network errors gracefully', async () => {
      vi.mocked(authService.login).mockRejectedValue(new Error('Network error'))

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      // Should handle error gracefully without crashing
      expect(wrapper.exists()).toBe(true)
    })

    it('handles server errors (500)', async () => {
      const mockErrorResponse = {
        success: false,
        error: 'Internal server error',
        status: 500
      }
      vi.mocked(authService.login).mockResolvedValue(mockErrorResponse)

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      expect(wrapper.text()).toContain('Internal server error')
    })
  })

  describe('Loading States Integration', () => {
    it('shows loading state during authentication', async () => {
      // Create a promise that we can control
      let resolveLogin
      const loginPromise = new Promise((resolve) => {
        resolveLogin = resolve
      })
      vi.mocked(authService.login).mockReturnValue(loginPromise)

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      // Should show loading state
      expect(wrapper.find('button[type="submit"]').text()).toBe('Signing in...')
      expect(wrapper.find('.animate-spin').exists()).toBe(true)

      // Form should be disabled
      expect(wrapper.find('#email').attributes('disabled')).toBeDefined()
      expect(wrapper.find('#password').attributes('disabled')).toBeDefined()

      // Resolve the login
      resolveLogin({ success: true, data: { token: 'token', user: {} } })
      await nextTick()

      // Loading state should be cleared
      expect(wrapper.find('button[type="submit"]').text()).toBe('Sign in')
    })
  })

  describe('Form Validation Integration', () => {
    it('prevents API call with invalid form data', async () => {
      const form = wrapper.find('form')

      // Submit with empty form
      await form.trigger('submit.prevent')
      await nextTick()

      // Should not call auth service
      expect(authService.login).not.toHaveBeenCalled()

      // Should show validation errors
      expect(wrapper.text()).toContain('Email is required')
      expect(wrapper.text()).toContain('Password is required')
    })

    it('prevents API call with invalid email format', async () => {
      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('invalid-email')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      // Should not call auth service
      expect(authService.login).not.toHaveBeenCalled()

      // Should show email validation error
      expect(wrapper.text()).toContain('Please enter a valid email address')
    })
  })

  describe('Error Recovery', () => {
    it('allows retry after failed login', async () => {
      // First attempt fails
      vi.mocked(authService.login).mockResolvedValueOnce({
        success: false,
        error: 'Invalid credentials'
      })

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('wrongpassword')
      await form.trigger('submit.prevent')
      await nextTick()

      expect(wrapper.text()).toContain('Invalid credentials')

      // Second attempt succeeds
      vi.mocked(authService.login).mockResolvedValueOnce({
        success: true,
        data: { token: 'token', user: { id: 1 } }
      })

      await passwordInput.setValue('correctpassword')
      await form.trigger('submit.prevent')
      await nextTick()

      expect(mockOnLoginSuccess).toHaveBeenCalledWith({ id: 1 }, '/dashboard')
    })

    it('clears previous errors on new submission', async () => {
      // First attempt fails
      vi.mocked(authService.login).mockResolvedValueOnce({
        success: false,
        error: 'Invalid credentials'
      })

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('wrongpassword')
      await form.trigger('submit.prevent')
      await nextTick()

      expect(wrapper.text()).toContain('Invalid credentials')

      // Second attempt (even if it fails differently)
      vi.mocked(authService.login).mockResolvedValueOnce({
        success: false,
        error: 'Account locked'
      })

      await form.trigger('submit.prevent')
      await nextTick()

      // Should show new error, not old one
      expect(wrapper.text()).toContain('Account locked')
      expect(wrapper.text()).not.toContain('Invalid credentials')
    })
  })

  describe('Remember Me Integration', () => {
    it('includes remember me state in login flow', async () => {
      const mockLoginResponse = {
        success: true,
        data: { token: 'token', user: { id: 1 } }
      }
      vi.mocked(authService.login).mockResolvedValue(mockLoginResponse)

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const rememberCheckbox = wrapper.find('#remember-me')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await rememberCheckbox.setChecked(true)
      await form.trigger('submit.prevent')
      await nextTick()

      // Verify form data includes remember me
      expect(wrapper.vm.form.remember).toBe(true)
    })
  })
})