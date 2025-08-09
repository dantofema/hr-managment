import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import App from '@/App.vue'

// Mock the useAuth composable
const mockUseAuth = vi.fn()
vi.mock('@/composables/useAuth', () => ({
  useAuth: mockUseAuth
}))

// Mock child components to focus on navigation flow
vi.mock('@/components/Home.vue', () => ({
  default: {
    name: 'Home',
    emits: ['navigate'],
    template: '<div data-testid="home-view">Home Component</div>'
  }
}))

vi.mock('@/components/EmployeesList.vue', () => ({
  default: {
    name: 'EmployeesList',
    template: '<div data-testid="employees-view">Employees List Component</div>'
  }
}))

vi.mock('@/components/auth/Login.vue', () => ({
  default: {
    name: 'Login',
    emits: ['navigate'],
    template: '<div data-testid="login-view">Login Component</div>'
  }
}))

vi.mock('@/components/auth/UserProfile.vue', () => ({
  default: {
    name: 'UserProfile',
    emits: ['navigate'],
    template: '<div data-testid="profile-view">User Profile Component</div>'
  }
}))

vi.mock('@/components/auth/UserMenu.vue', () => ({
  default: {
    name: 'UserMenu',
    props: ['user'],
    emits: ['logout', 'profile'],
    template: `
      <div data-testid="user-menu">
        <button @click="$emit('profile')" data-testid="profile-button">Profile</button>
        <button @click="$emit('logout')" data-testid="logout-button">Logout</button>
      </div>
    `
  }
}))

vi.mock('@/components/layout/AppFooter.vue', () => ({
  default: {
    name: 'AppFooter',
    template: '<footer data-testid="app-footer">Footer</footer>'
  }
}))

describe('Navigation Flow Integration Tests', () => {
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

  describe('Initial App State', () => {
    it('renders the app with navigation and home view by default', () => {
      wrapper = mount(App)

      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(true)
      expect(wrapper.find('nav').exists()).toBe(true)
      expect(wrapper.find('[data-testid="app-footer"]').exists()).toBe(true)
    })

    it('shows login button when not authenticated', () => {
      wrapper = mount(App)

      const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
      expect(loginButton.exists()).toBe(true)
    })
  })

  describe('Non-Authenticated User Flow', () => {
    beforeEach(() => {
      mockAuthState.isAuthenticated.value = false
      mockAuthState.user.value = null
      wrapper = mount(App)
    })

    it('navigates to login view when login button is clicked', async () => {
      const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
      await loginButton.trigger('click')

      expect(wrapper.find('[data-testid="login-view"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(false)
    })

    it('does not show protected navigation links', () => {
      const employeesButton = wrapper.find('button:contains("Empleados")')
      expect(employeesButton.exists()).toBe(false)
    })

    it('can navigate back to home from login', async () => {
      // Go to login
      const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
      await loginButton.trigger('click')
      expect(wrapper.find('[data-testid="login-view"]').exists()).toBe(true)

      // Navigate back to home programmatically
      await wrapper.vm.handleNavigation('home')
      await wrapper.vm.$nextTick()

      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="login-view"]').exists()).toBe(false)
    })
  })

  describe('Authenticated User Flow', () => {
    beforeEach(() => {
      mockAuthState.isAuthenticated.value = true
      mockAuthState.user.value = {
        id: 1,
        name: 'John Doe',
        email: 'john.doe@example.com',
        roles: ['ROLE_USER']
      }
      wrapper = mount(App)
    })

    it('shows navigation links when authenticated', () => {
      const homeButton = wrapper.find('button:contains("Inicio")')
      const employeesButton = wrapper.find('button:contains("Empleados")')
      
      expect(homeButton.exists()).toBe(true)
      expect(employeesButton.exists()).toBe(true)
    })

    it('shows user menu instead of login button', () => {
      const userMenu = wrapper.find('[data-testid="user-menu"]')
      const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
      
      expect(userMenu.exists()).toBe(true)
      expect(loginButton.exists()).toBe(false)
    })

    it('navigates between different views correctly', async () => {
      // Start at home
      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(true)

      // Navigate to employees
      const employeesButton = wrapper.find('button:contains("Empleados")')
      await employeesButton.trigger('click')
      expect(wrapper.find('[data-testid="employees-view"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(false)

      // Navigate back to home
      const homeButton = wrapper.find('button:contains("Inicio")')
      await homeButton.trigger('click')
      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="employees-view"]').exists()).toBe(false)
    })

    it('navigates to profile from user menu', async () => {
      const profileButton = wrapper.find('[data-testid="profile-button"]')
      await profileButton.trigger('click')

      expect(wrapper.find('[data-testid="profile-view"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(false)
    })

    it('handles logout flow correctly', async () => {
      const logoutButton = wrapper.find('[data-testid="logout-button"]')
      await logoutButton.trigger('click')

      expect(mockAuthState.logout).toHaveBeenCalled()
      // After logout, should navigate back to home
      expect(wrapper.find('[data-testid="home-view"]').exists()).toBe(true)
    })
  })

  describe('Mobile Navigation Flow', () => {
    beforeEach(() => {
      mockAuthState.isAuthenticated.value = true
      mockAuthState.user.value = {
        id: 1,
        name: 'John Doe',
        email: 'john.doe@example.com'
      }
      wrapper = mount(App)
    })

    it('opens and closes mobile menu correctly', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      
      // Initially closed
      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(false)
      
      // Open mobile menu
      await mobileButton.trigger('click')
      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(true)
      
      // Close mobile menu
      await mobileButton.trigger('click')
      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(false)
    })

    it('navigates from mobile menu and closes menu', async () => {
      const mobileButton = wrapper.find('.md\\:hidden button')
      await mobileButton.trigger('click')
      
      // Find mobile employees button and click it
      const mobileEmployeesButton = wrapper.find('.md\\:hidden button:contains("Empleados")')
      await mobileEmployeesButton.trigger('click')
      
      // Should navigate to employees view and close mobile menu
      expect(wrapper.find('[data-testid="employees-view"]').exists()).toBe(true)
      expect(wrapper.find('.md\\:hidden.border-t').exists()).toBe(false)
    })
  })

  describe('Loading States', () => {
    it('shows loading state in navigation', () => {
      mockAuthState.isLoading.value = true
      wrapper = mount(App)

      const spinner = wrapper.find('.animate-spin')
      expect(spinner.exists()).toBe(true)
    })
  })

  describe('Error Handling', () => {
    it('handles logout errors gracefully', async () => {
      mockAuthState.isAuthenticated.value = true
      mockAuthState.user.value = { id: 1, name: 'John Doe', email: 'test@example.com' }
      mockAuthState.logout.mockRejectedValue(new Error('Logout failed'))
      
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      
      wrapper = mount(App)
      
      const logoutButton = wrapper.find('[data-testid="logout-button"]')
      await logoutButton.trigger('click')

      expect(consoleSpy).toHaveBeenCalledWith('Error during logout:', expect.any(Error))
      consoleSpy.mockRestore()
    })
  })
})