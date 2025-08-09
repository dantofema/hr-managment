import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import Login from '../Login.vue'
import { useAuth } from '@/composables/useAuth'

// Mock the composables
vi.mock('@/composables/useAuth')

// Mock window.location
Object.defineProperty(window, 'location', {
  value: {
    href: ''
  },
  writable: true
})

describe('Login.vue', () => {
  let wrapper
  let mockAuth
  let mockOnLoginSuccess

  beforeEach(() => {
    // Reset window.location.href
    window.location.href = ''

    // Mock useAuth composable
    mockAuth = {
      login: vi.fn(),
      isLoading: { value: false },
      error: { value: null },
      clearError: vi.fn()
    }
    vi.mocked(useAuth).mockReturnValue(mockAuth)

    // Mock login success callback
    mockOnLoginSuccess = vi.fn()

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

  describe('Component Rendering', () => {
    it('renders login form correctly', () => {
      expect(wrapper.find('h2').text()).toBe('Sign in to your account')
      expect(wrapper.find('input[type="email"]').exists()).toBe(true)
      expect(wrapper.find('input[type="password"]').exists()).toBe(true)
      expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
    })

    it('renders all form fields with correct attributes', () => {
      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const submitButton = wrapper.find('button[type="submit"]')

      expect(emailInput.attributes('type')).toBe('email')
      expect(emailInput.attributes('required')).toBeDefined()
      expect(emailInput.attributes('placeholder')).toBe('Email address')

      expect(passwordInput.attributes('type')).toBe('password')
      expect(passwordInput.attributes('required')).toBeDefined()
      expect(passwordInput.attributes('placeholder')).toBe('Password')

      expect(submitButton.text()).toBe('Sign in')
    })

    it('renders remember me checkbox', () => {
      const checkbox = wrapper.find('#remember-me')
      expect(checkbox.exists()).toBe(true)
      expect(checkbox.attributes('type')).toBe('checkbox')
    })
  })

  describe('Form Validation', () => {
    it('validates email field correctly', async () => {
      const emailInput = wrapper.find('#email')
      
      // Test empty email
      await emailInput.setValue('')
      await emailInput.trigger('blur')
      await nextTick()
      
      expect(wrapper.text()).toContain('Email is required')

      // Test invalid email format
      await emailInput.setValue('invalid-email')
      await emailInput.trigger('blur')
      await nextTick()
      
      expect(wrapper.text()).toContain('Please enter a valid email address')

      // Test valid email
      await emailInput.setValue('test@example.com')
      await emailInput.trigger('blur')
      await nextTick()
      
      expect(wrapper.text()).not.toContain('Email is required')
      expect(wrapper.text()).not.toContain('Please enter a valid email address')
    })

    it('validates password field correctly', async () => {
      const passwordInput = wrapper.find('#password')
      
      // Test empty password
      await passwordInput.setValue('')
      await passwordInput.trigger('blur')
      await nextTick()
      
      expect(wrapper.text()).toContain('Password is required')

      // Test short password
      await passwordInput.setValue('123')
      await passwordInput.trigger('blur')
      await nextTick()
      
      expect(wrapper.text()).toContain('Password must be at least 6 characters long')

      // Test valid password
      await passwordInput.setValue('password123')
      await passwordInput.trigger('blur')
      await nextTick()
      
      expect(wrapper.text()).not.toContain('Password is required')
      expect(wrapper.text()).not.toContain('Password must be at least 6 characters long')
    })

    it('clears field errors on input', async () => {
      const emailInput = wrapper.find('#email')
      
      // Trigger validation error
      await emailInput.setValue('')
      await emailInput.trigger('blur')
      await nextTick()
      expect(wrapper.text()).toContain('Email is required')

      // Clear error by typing
      await emailInput.setValue('t')
      await emailInput.trigger('input')
      await nextTick()
      expect(wrapper.text()).not.toContain('Email is required')
    })
  })

  describe('Password Visibility Toggle', () => {
    it('toggles password visibility', async () => {
      const passwordInput = wrapper.find('#password')
      const toggleButton = wrapper.find('button[type="button"]')

      // Initially password type
      expect(passwordInput.attributes('type')).toBe('password')

      // Click toggle button
      await toggleButton.trigger('click')
      await nextTick()

      expect(passwordInput.attributes('type')).toBe('text')

      // Click again to hide
      await toggleButton.trigger('click')
      await nextTick()

      expect(passwordInput.attributes('type')).toBe('password')
    })
  })

  describe('Form Submission', () => {
    it('prevents submission with invalid form', async () => {
      const form = wrapper.find('form')
      
      // Submit empty form
      await form.trigger('submit.prevent')
      await nextTick()

      expect(mockAuth.login).not.toHaveBeenCalled()
      expect(wrapper.text()).toContain('Email is required')
      expect(wrapper.text()).toContain('Password is required')
    })

    it('submits form with valid data', async () => {
      mockAuth.login.mockResolvedValue({ success: true })

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      // Fill form with valid data
      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await nextTick()

      // Submit form
      await form.trigger('submit.prevent')
      await nextTick()

      expect(mockAuth.login).toHaveBeenCalledWith('test@example.com', 'password123')
    })

    it('calls onLoginSuccess callback on successful login', async () => {
      const mockUser = { id: 1, email: 'test@example.com' }
      mockAuth.login.mockResolvedValue({ success: true, user: mockUser })

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      expect(mockOnLoginSuccess).toHaveBeenCalledWith(mockUser, '/dashboard')
    })

    it('uses window.location.href when no callback provided', async () => {
      // Mount component without onLoginSuccess prop
      wrapper.unmount()
      wrapper = mount(Login, {
        props: {
          redirectUrl: '/employees'
        }
      })

      const mockUser = { id: 1, email: 'test@example.com' }
      mockAuth.login.mockResolvedValue({ success: true, user: mockUser })

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const form = wrapper.find('form')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await form.trigger('submit.prevent')
      await nextTick()

      expect(window.location.href).toBe('/employees')
    })
  })

  describe('Loading States', () => {
    it('disables form during loading', async () => {
      mockAuth.isLoading.value = true
      await nextTick()

      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const submitButton = wrapper.find('button[type="submit"]')
      const toggleButton = wrapper.find('button[type="button"]')
      const checkbox = wrapper.find('#remember-me')

      expect(emailInput.attributes('disabled')).toBeDefined()
      expect(passwordInput.attributes('disabled')).toBeDefined()
      expect(submitButton.attributes('disabled')).toBeDefined()
      expect(toggleButton.attributes('disabled')).toBeDefined()
      expect(checkbox.attributes('disabled')).toBeDefined()
    })

    it('shows loading text and spinner during submission', async () => {
      mockAuth.isLoading.value = true
      await nextTick()

      const submitButton = wrapper.find('button[type="submit"]')
      expect(submitButton.text()).toBe('Signing in...')
      expect(wrapper.find('.animate-spin').exists()).toBe(true)
    })
  })

  describe('Error Handling', () => {
    it('displays authentication error', async () => {
      mockAuth.error.value = 'Invalid credentials'
      await nextTick()

      expect(wrapper.text()).toContain('Authentication Error')
      expect(wrapper.text()).toContain('Invalid credentials')
    })

    it('clears error when dismiss button is clicked', async () => {
      mockAuth.error.value = 'Invalid credentials'
      await nextTick()

      const dismissButton = wrapper.find('button[aria-label="Dismiss"], button:has(svg)')
      await dismissButton.trigger('click')

      expect(mockAuth.clearError).toHaveBeenCalled()
    })
  })

  describe('Accessibility', () => {
    it('has proper ARIA attributes', () => {
      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')

      expect(wrapper.find('label[for="email"]').exists()).toBe(true)
      expect(wrapper.find('label[for="password"]').exists()).toBe(true)
      expect(emailInput.attributes('autocomplete')).toBe('email')
      expect(passwordInput.attributes('autocomplete')).toBe('current-password')
    })

    it('shows error messages with role="alert"', async () => {
      const emailInput = wrapper.find('#email')
      await emailInput.setValue('')
      await emailInput.trigger('blur')
      await nextTick()

      const errorMessage = wrapper.find('[role="alert"]')
      expect(errorMessage.exists()).toBe(true)
    })
  })

  describe('Form State Management', () => {
    it('updates form data correctly', async () => {
      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')
      const checkbox = wrapper.find('#remember-me')

      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await checkbox.setChecked(true)

      expect(wrapper.vm.form.email).toBe('test@example.com')
      expect(wrapper.vm.form.password).toBe('password123')
      expect(wrapper.vm.form.remember).toBe(true)
    })

    it('computes form validity correctly', async () => {
      const emailInput = wrapper.find('#email')
      const passwordInput = wrapper.find('#password')

      // Initially invalid
      expect(wrapper.vm.isFormValid).toBe(false)

      // Valid after filling both fields
      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')
      await nextTick()

      expect(wrapper.vm.isFormValid).toBe(true)
    })
  })
})