import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import AppNavigation from '../AppNavigation.vue'

// Mock the useAuth composable
const mockUseAuth = vi.fn()
vi.mock('@/composables/useAuth', () => ({
  useAuth: mockUseAuth
}))

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
  let mockAuthState

  beforeEach(() => {
    mockAuthState = {
      isAuthenticated: ref(false),
      user: ref(null),
      logout: vi.fn(),
      isLoading: ref(false)
    }

    mockUseAuth.mockReturnValue(mockAuthState)
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.clearAllMocks()
  })

  describe('Component Rendering', () => {
    it('renders the brand/logo correctly', () => {
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })

      expect(wrapper.find('h1').text()).toBe('HR System')
    })

    it('renders navigation structure correctly', () => {
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })

      expect(wrapper.find('nav').exists()).toBe(true)
      expect(wrapper.find('.max-w-7xl').exists()).toBe(true)
      expect(wrapper.find('.flex.justify-between').exists()).toBe(true)
    })
  })

  describe('Authentication States', () => {
    describe('Non-authenticated state', () => {
      beforeEach(() => {
        mockAuthState.isAuthenticated.value = false
        mockAuthState.user.value = null
        wrapper = mount(AppNavigation, {
          props: { currentView: 'home' }
        })
      })

      it('shows login button when not authenticated', () => {
        const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
        expect(loginButton.exists()).toBe(true)
      })

      it('does not show navigation links when not authenticated', () => {
        const navLinks = wrapper.find('.hidden.md\\:flex')
        expect(navLinks.exists()).toBe(false)
      })

      it('does not show user menu when not authenticated', () => {
        const userMenu = wrapper.find('[data-testid="user-menu"]')
        expect(userMenu.exists()).toBe(false)
      })

      it('emits navigate event when login button is clicked', async () => {
        const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
        await loginButton.trigger('click')

        expect(wrapper.emitted('navigate')).toBeTruthy()
        expect(wrapper.emitted('navigate')[0]).toEqual(['login'])
      })
    })

    describe('Authenticated state', () => {
      beforeEach(() => {
        mockAuthState.isAuthenticated.value = true
        mockAuthState.user.value = {
          id: 1,
          name: 'John Doe',
          email: 'john.doe@example.com',
          roles: ['ROLE_USER']
        }
        wrapper = mount(AppNavigation, {
          props: { currentView: 'home' }
        })
      })

      it('shows navigation links when authenticated', () => {
        const homeButton = wrapper.find('button:contains("Inicio")')
        const employeesButton = wrapper.find('button:contains("Empleados")')
        
        expect(homeButton.exists()).toBe(true)
        expect(employeesButton.exists()).toBe(true)
      })

      it('shows user menu when authenticated', () => {
        const userMenu = wrapper.find('[data-testid="user-menu"]')
        expect(userMenu.exists()).toBe(true)
      })

      it('does not show login button when authenticated', () => {
        const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
        expect(loginButton.exists()).toBe(false)
      })

      it('highlights current view correctly', () => {
        const homeButton = wrapper.find('button:contains("Inicio")')
        expect(homeButton.classes()).toContain('bg-blue-100')
        expect(homeButton.classes()).toContain('text-blue-700')
      })

      it('emits navigate event when navigation buttons are clicked', async () => {
        const employeesButton = wrapper.find('button:contains("Empleados")')
        await employeesButton.trigger('click')

        expect(wrapper.emitted('navigate')).toBeTruthy()
        expect(wrapper.emitted('navigate')[0]).toEqual(['employees'])
      })
    })

    describe('Loading state', () => {
      beforeEach(() => {
        mockAuthState.isLoading.value = true
        wrapper = mount(AppNavigation, {
          props: { currentView: 'home' }
        })
      })

      it('shows loading spinner when loading', () => {
        const spinner = wrapper.find('.animate-spin')
        expect(spinner.exists()).toBe(true)
      })

      it('does not show user menu or login button when loading', () => {
        const userMenu = wrapper.find('[data-testid="user-menu"]')
        const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
        
        expect(userMenu.exists()).toBe(false)
        expect(loginButton.exists()).toBe(false)
      })
    })
  })

  describe('Mobile Navigation', () => {
    beforeEach(() => {
      mockAuthState.isAuthenticated.value = true
      mockAuthState.user.value = {
        id: 1,
        name: 'John Doe',
        email: 'john.doe@example.com'
      }
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })
    })

    it('shows mobile menu button', () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      expect(mobileButton.exists()).toBe(true)
    })

    it('toggles mobile menu when button is clicked', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      
      // Initially closed
      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(false)
      
      // Click to open
      await mobileButton.trigger('click')
      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(true)
      
      // Click to close
      await mobileButton.trigger('click')
      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(false)
    })

    it('shows correct icon based on menu state', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      
      // Initially shows hamburger icon
      let paths = wrapper.findAll('.md\\:hidden button svg path')
      expect(paths[0].attributes('d')).toBe('M4 6h16M4 12h16M4 18h16')
      
      // Click to open - should show X icon
      await mobileButton.trigger('click')
      paths = wrapper.findAll('.md\\:hidden button svg path')
      expect(paths[0].attributes('d')).toBe('M6 18L18 6M6 6l12 12')
    })

    it('shows mobile navigation links when authenticated', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      await mobileButton.trigger('click')

      const mobileHomeButton = wrapper.find('.md\\:hidden button:contains("Inicio")')
      const mobileEmployeesButton = wrapper.find('.md\\:hidden button:contains("Empleados")')
      
      expect(mobileHomeButton.exists()).toBe(true)
      expect(mobileEmployeesButton.exists()).toBe(true)
    })

    it('shows user info in mobile menu when authenticated', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      await mobileButton.trigger('click')

      expect(wrapper.text()).toContain('John Doe')
      expect(wrapper.text()).toContain('john.doe@example.com')
    })

    it('closes mobile menu when navigation item is clicked', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      await mobileButton.trigger('click')
      
      const mobileEmployeesButton = wrapper.find('.md\\:hidden button:contains("Empleados")')
      await mobileEmployeesButton.trigger('click')

      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(false)
      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['employees'])
    })
  })

  describe('User Menu Integration', () => {
    beforeEach(() => {
      mockAuthState.isAuthenticated.value = true
      mockAuthState.user.value = {
        id: 1,
        name: 'John Doe',
        email: 'john.doe@example.com'
      }
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })
    })

    it('passes user data to UserMenu component', () => {
      const userMenu = wrapper.findComponent({ name: 'UserMenu' })
      expect(userMenu.props('user')).toEqual(mockAuthState.user.value)
    })

    it('handles logout event from UserMenu', async () => {
      const userMenu = wrapper.findComponent({ name: 'UserMenu' })
      await userMenu.vm.$emit('logout')

      expect(mockAuthState.logout).toHaveBeenCalled()
      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['home'])
    })

    it('handles profile event from UserMenu', async () => {
      const userMenu = wrapper.findComponent({ name: 'UserMenu' })
      await userMenu.vm.$emit('profile')

      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['profile'])
    })
  })

  describe('Current View Highlighting', () => {
    it('highlights home view correctly', () => {
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })

      const homeButton = wrapper.find('button:contains("Inicio")')
      expect(homeButton.classes()).toContain('bg-blue-100')
      expect(homeButton.classes()).toContain('text-blue-700')
    })

    it('highlights employees view correctly', () => {
      mockAuthState.isAuthenticated.value = true
      wrapper = mount(AppNavigation, {
        props: { currentView: 'employees' }
      })

      const employeesButton = wrapper.find('button:contains("Empleados")')
      expect(employeesButton.classes()).toContain('bg-blue-100')
      expect(employeesButton.classes()).toContain('text-blue-700')
    })

    it('applies default styles to non-active navigation items', () => {
      mockAuthState.isAuthenticated.value = true
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })

      const employeesButton = wrapper.find('button:contains("Empleados")')
      expect(employeesButton.classes()).toContain('text-gray-500')
      expect(employeesButton.classes()).toContain('hover:text-gray-700')
    })
  })

  describe('Accessibility', () => {
    beforeEach(() => {
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })
    })

    it('has proper ARIA attributes on mobile menu button', () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      expect(mobileButton.attributes('aria-label')).toBe('Toggle menu')
      expect(mobileButton.attributes('aria-expanded')).toBe('false')
      expect(mobileButton.attributes('aria-haspopup')).toBe('true')
    })

    it('updates aria-expanded when mobile menu is toggled', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      
      await mobileButton.trigger('click')
      expect(mobileButton.attributes('aria-expanded')).toBe('true')
      
      await mobileButton.trigger('click')
      expect(mobileButton.attributes('aria-expanded')).toBe('false')
    })

    it('has proper semantic HTML structure', () => {
      expect(wrapper.find('nav').exists()).toBe(true)
      expect(wrapper.find('h1').exists()).toBe(true)
    })

    it('has focus management for buttons', () => {
      const buttons = wrapper.findAll('button')
      buttons.forEach(button => {
        expect(button.classes()).toContain('focus:outline-none')
      })
    })
  })

  describe('Error Handling', () => {
    it('handles logout errors gracefully', async () => {
      mockAuthState.isAuthenticated.value = true
      mockAuthState.user.value = { id: 1, name: 'John Doe', email: 'test@example.com' }
      mockAuthState.logout.mockRejectedValue(new Error('Logout failed'))
      
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      
      wrapper = mount(AppNavigation, {
        props: { currentView: 'home' }
      })

      const userMenu = wrapper.findComponent({ name: 'UserMenu' })
      await userMenu.vm.$emit('logout')

      expect(consoleSpy).toHaveBeenCalledWith('Error during logout:', expect.any(Error))
      consoleSpy.mockRestore()
    })
  })
})