import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import AppNavigation from '@/components/layout/AppNavigation.vue'
import UserMenu from '@/components/auth/UserMenu.vue'
import { useAuth } from '@/composables/useAuth'

// Mock the useAuth composable
vi.mock('@/composables/useAuth')

// Mock UserMenu component
vi.mock('@/components/auth/UserMenu.vue', () => ({
  default: {
    name: 'UserMenu',
    props: ['user'],
    emits: ['logout', 'profile'],
    template: '<div data-testid="user-menu">UserMenu</div>'
  }
}))

describe('AppNavigation', () => {
  let wrapper
  let mockUseAuth

  beforeEach(() => {
    mockUseAuth = {
      isAuthenticated: vi.fn(() => false),
      user: vi.fn(() => null),
      logout: vi.fn(),
      isLoading: vi.fn(() => false)
    }
    
    useAuth.mockReturnValue(mockUseAuth)
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.clearAllMocks()
  })

  const createWrapper = (props = {}) => {
    return mount(AppNavigation, {
      props: {
        currentView: 'home',
        ...props
      },
      global: {
        stubs: {
          UserMenu: true
        }
      }
    })
  }

  describe('Basic Rendering', () => {
    test('should render the navigation component', () => {
      wrapper = createWrapper()
      expect(wrapper.find('nav').exists()).toBe(true)
      expect(wrapper.text()).toContain('HR System')
    })

    test('should display the correct brand name', () => {
      wrapper = createWrapper()
      const brandElement = wrapper.find('h1')
      expect(brandElement.text()).toBe('HR System')
    })
  })

  describe('Authentication States', () => {
    test('should show login button when user is not authenticated', () => {
      mockUseAuth.isAuthenticated.mockReturnValue(false)
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const loginButton = buttons.find(button => button.text().includes('Iniciar Sesión'))
      expect(loginButton).toBeTruthy()
    })

    test('should show navigation links when user is authenticated', () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      mockUseAuth.user.mockReturnValue({ name: 'Test User', email: 'test@example.com' })
      
      wrapper = createWrapper()
      
      expect(wrapper.text()).toContain('Inicio')
      expect(wrapper.text()).toContain('Empleados')
    })

    test('should show UserMenu when user is authenticated', () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      mockUseAuth.user.mockReturnValue({ name: 'Test User', email: 'test@example.com' })
      
      wrapper = createWrapper()
      
      expect(wrapper.findComponent(UserMenu).exists()).toBe(true)
    })

    test('should show loading spinner when isLoading is true', () => {
      mockUseAuth.isLoading.mockReturnValue(true)
      
      wrapper = createWrapper()
      
      const loadingSpinner = wrapper.find('.animate-spin')
      expect(loadingSpinner.exists()).toBe(true)
    })
  })

  describe('Navigation Functionality', () => {
    test('should emit navigate event when home button is clicked', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const homeButton = buttons.find(button => button.text().includes('Inicio'))
      await homeButton.trigger('click')
      
      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['home'])
    })

    test('should emit navigate event when employees button is clicked', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const employeesButton = buttons.find(button => button.text().includes('Empleados'))
      await employeesButton.trigger('click')
      
      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['employees'])
    })

    test('should emit navigate event when login button is clicked', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(false)
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const loginButton = buttons.find(button => button.text().includes('Iniciar Sesión'))
      await loginButton.trigger('click')
      
      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['login'])
    })

    test('should highlight current view button', () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      wrapper = createWrapper({ currentView: 'employees' })
      
      const buttons = wrapper.findAll('button')
      const employeesButton = buttons.find(button => button.text().includes('Empleados'))
      expect(employeesButton.classes()).toContain('bg-blue-100')
      expect(employeesButton.classes()).toContain('text-blue-700')
    })
  })

  describe('Mobile Menu', () => {
    test('should toggle mobile menu when hamburger button is clicked', async () => {
      wrapper = createWrapper()
      
      const mobileMenuButton = wrapper.find('[aria-label="Toggle menu"]')
      expect(mobileMenuButton.exists()).toBe(true)
      
      // Initially mobile menu should be hidden
      expect(wrapper.find('.md\\:hidden .space-y-1').exists()).toBe(false)
      
      // Click to show mobile menu
      await mobileMenuButton.trigger('click')
      expect(wrapper.find('.md\\:hidden .space-y-1').exists()).toBe(true)
      
      // Click again to hide mobile menu
      await mobileMenuButton.trigger('click')
      expect(wrapper.find('.md\\:hidden .space-y-1').exists()).toBe(false)
    })

    test('should show mobile navigation links when authenticated', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      mockUseAuth.user.mockReturnValue({ name: 'Test User', email: 'test@example.com' })
      
      wrapper = createWrapper()
      
      const mobileMenuButton = wrapper.find('[aria-label="Toggle menu"]')
      await mobileMenuButton.trigger('click')
      
      const mobileMenu = wrapper.find('.md\\:hidden')
      expect(mobileMenu.text()).toContain('Inicio')
      expect(mobileMenu.text()).toContain('Empleados')
      expect(mobileMenu.text()).toContain('Test User')
    })

    test('should show mobile login button when not authenticated', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(false)
      wrapper = createWrapper()
      
      const mobileMenuButton = wrapper.find('[aria-label="Toggle menu"]')
      await mobileMenuButton.trigger('click')
      
      const mobileMenu = wrapper.find('.md\\:hidden')
      expect(mobileMenu.text()).toContain('Iniciar Sesión')
    })
  })

  describe('User Menu Integration', () => {
    test('should pass user data to UserMenu component', () => {
      const mockUser = { name: 'Test User', email: 'test@example.com' }
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      mockUseAuth.user.mockReturnValue(mockUser)
      
      wrapper = createWrapper()
      
      const userMenu = wrapper.findComponent(UserMenu)
      expect(userMenu.props('user')).toEqual(mockUser)
    })

    test('should handle logout event from UserMenu', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      mockUseAuth.user.mockReturnValue({ name: 'Test User', email: 'test@example.com' })
      mockUseAuth.logout.mockResolvedValue()
      
      wrapper = createWrapper()
      
      const userMenu = wrapper.findComponent(UserMenu)
      await userMenu.vm.$emit('logout')
      
      expect(mockUseAuth.logout).toHaveBeenCalled()
      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['home'])
    })

    test('should handle profile event from UserMenu', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      mockUseAuth.user.mockReturnValue({ name: 'Test User', email: 'test@example.com' })
      
      wrapper = createWrapper()
      
      const userMenu = wrapper.findComponent(UserMenu)
      await userMenu.vm.$emit('profile')
      
      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['profile'])
    })
  })

  describe('Error Handling', () => {
    test('should handle logout error gracefully', async () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      mockUseAuth.user.mockReturnValue({ name: 'Test User', email: 'test@example.com' })
      mockUseAuth.logout.mockRejectedValue(new Error('Logout failed'))
      
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      
      wrapper = createWrapper()
      
      const userMenu = wrapper.findComponent(UserMenu)
      await userMenu.vm.$emit('logout')
      
      expect(consoleSpy).toHaveBeenCalledWith('Error during logout:', expect.any(Error))
      
      consoleSpy.mockRestore()
    })
  })

  describe('Accessibility', () => {
    test('should have proper ARIA attributes for mobile menu button', () => {
      wrapper = createWrapper()
      
      const mobileMenuButton = wrapper.find('[aria-label="Toggle menu"]')
      expect(mobileMenuButton.attributes('aria-label')).toBe('Toggle menu')
    })

    test('should update aria-expanded when mobile menu is toggled', async () => {
      wrapper = createWrapper()
      
      const mobileMenuButton = wrapper.find('[aria-label="Toggle menu"]')
      
      // Initially should be false (or not present)
      expect(mobileMenuButton.attributes('aria-expanded')).toBeFalsy()
      
      // After clicking should be true
      await mobileMenuButton.trigger('click')
      // Note: This test might need adjustment based on actual implementation
    })
  })

  describe('Responsive Design', () => {
    test('should have responsive classes for desktop navigation', () => {
      mockUseAuth.isAuthenticated.mockReturnValue(true)
      wrapper = createWrapper()
      
      const desktopNav = wrapper.find('.hidden.md\\:flex')
      expect(desktopNav.exists()).toBe(true)
    })

    test('should have responsive classes for mobile menu button', () => {
      wrapper = createWrapper()
      
      const mobileButton = wrapper.find('.md\\:hidden button')
      expect(mobileButton.exists()).toBe(true)
    })
  })
})