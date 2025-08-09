import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import UserProfile from '../UserProfile.vue'

// Mock the useAuth composable
vi.mock('@/composables/useAuth', () => ({
  useAuth: vi.fn()
}))

describe('UserProfile', () => {
  let wrapper
  let mockUser
  let mockAuthState
  let mockUseAuth

  beforeEach(async () => {
    const { useAuth } = await import('@/composables/useAuth')
    mockUseAuth = useAuth
    
    mockUser = {
      id: 1,
      name: 'John Doe',
      email: 'john.doe@example.com',
      roles: ['ROLE_USER', 'ROLE_HR'],
      createdAt: '2023-01-15T10:30:00Z',
      lastLoginAt: '2024-01-10T14:20:00Z'
    }

    mockAuthState = {
      user: ref(mockUser),
      isLoading: ref(false),
      error: ref(null)
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
    it('renders correctly with user data', () => {
      wrapper = mount(UserProfile)

      expect(wrapper.find('h1').text()).toBe('Perfil de Usuario')
      expect(wrapper.find('h3').text()).toBe('John Doe')
      expect(wrapper.text()).toContain('john.doe@example.com')
    })

    it('displays loading state', () => {
      mockAuthState.isLoading.value = true
      wrapper = mount(UserProfile)

      expect(wrapper.find('.animate-spin').exists()).toBe(true)
      expect(wrapper.text()).not.toContain('John Doe')
    })

    it('displays error state', () => {
      mockAuthState.error.value = 'Failed to load user data'
      wrapper = mount(UserProfile)

      expect(wrapper.find('.bg-red-50').exists()).toBe(true)
      expect(wrapper.text()).toContain('Failed to load user data')
    })

    it('displays no user state when user is null', () => {
      mockAuthState.user.value = null
      wrapper = mount(UserProfile)

      expect(wrapper.text()).toContain('No se pudo cargar el perfil')
      expect(wrapper.text()).toContain('Inicia sesión para ver tu información')
    })
  })

  describe('User Information Display', () => {
    beforeEach(() => {
      wrapper = mount(UserProfile)
    })

    it('displays user initials correctly', () => {
      const initialsElement = wrapper.find('.bg-blue-600 span')
      expect(initialsElement.text()).toBe('JD')
    })

    it('displays user roles with proper formatting', () => {
      const roleElements = wrapper.findAll('.bg-blue-100')
      expect(roleElements).toHaveLength(2)
      expect(roleElements[0].text()).toBe('Usuario')
      expect(roleElements[1].text()).toBe('Recursos Humanos')
    })

    it('displays formatted dates correctly', () => {
      expect(wrapper.text()).toContain('15 de enero de 2023')
      expect(wrapper.text()).toContain('10 de enero de 2024')
    })

    it('displays user details in the account section', () => {
      expect(wrapper.text()).toContain('john.doe@example.com')
      expect(wrapper.text()).toContain('1') // user ID
      expect(wrapper.text()).toContain('Activo')
    })
  })

  describe('User Initials Generation', () => {
    it('generates initials from full name', () => {
      wrapper = mount(UserProfile)
      const initialsElement = wrapper.find('.bg-blue-600 span')
      expect(initialsElement.text()).toBe('JD')
    })

    it('generates initials from single name', () => {
      mockAuthState.user.value = { ...mockUser, name: 'John' }
      wrapper = mount(UserProfile)
      const initialsElement = wrapper.find('.bg-blue-600 span')
      expect(initialsElement.text()).toBe('JO')
    })

    it('generates initials from email when name is not available', () => {
      mockAuthState.user.value = { ...mockUser, name: null }
      wrapper = mount(UserProfile)
      const initialsElement = wrapper.find('.bg-blue-600 span')
      expect(initialsElement.text()).toBe('JO') // from john.doe@example.com
    })

    it('shows question mark when no name or email', () => {
      mockAuthState.user.value = { ...mockUser, name: null, email: '' }
      wrapper = mount(UserProfile)
      const initialsElement = wrapper.find('.bg-blue-600 span')
      expect(initialsElement.text()).toBe('?')
    })
  })

  describe('Role Formatting', () => {
    it('formats standard roles correctly', () => {
      const roleElements = wrapper.findAll('.bg-blue-100')
      expect(roleElements[0].text()).toBe('Usuario') // ROLE_USER
      expect(roleElements[1].text()).toBe('Recursos Humanos') // ROLE_HR
    })

    it('handles unknown roles', () => {
      mockAuthState.user.value = { ...mockUser, roles: ['ROLE_CUSTOM'] }
      wrapper = mount(UserProfile)
      const roleElement = wrapper.find('.bg-blue-100')
      expect(roleElement.text()).toBe('custom')
    })
  })

  describe('Date Formatting', () => {
    it('formats valid dates correctly', () => {
      wrapper = mount(UserProfile)
      expect(wrapper.text()).toContain('15 de enero de 2023')
    })

    it('handles invalid dates', () => {
      mockAuthState.user.value = { ...mockUser, createdAt: 'invalid-date' }
      wrapper = mount(UserProfile)
      expect(wrapper.text()).toContain('Fecha inválida')
    })

    it('handles null dates', () => {
      mockAuthState.user.value = { ...mockUser, createdAt: null }
      wrapper = mount(UserProfile)
      expect(wrapper.text()).toContain('No disponible')
    })
  })

  describe('Action Buttons', () => {
    beforeEach(() => {
      wrapper = mount(UserProfile)
    })

    it('renders edit profile button', () => {
      const editButton = wrapper.find('button:contains("Editar Perfil")')
      expect(editButton.exists()).toBe(true)
    })

    it('renders change password button', () => {
      const changePasswordButton = wrapper.find('button:contains("Cambiar Contraseña")')
      expect(changePasswordButton.exists()).toBe(true)
    })

    it('handles edit profile click', async () => {
      const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {})
      const editButton = wrapper.find('button:contains("Editar Perfil")')
      
      await editButton.trigger('click')
      expect(consoleSpy).toHaveBeenCalledWith('Edit profile clicked')
      
      consoleSpy.mockRestore()
    })

    it('handles change password click', async () => {
      const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {})
      const changePasswordButton = wrapper.find('button:contains("Cambiar Contraseña")')
      
      await changePasswordButton.trigger('click')
      expect(consoleSpy).toHaveBeenCalledWith('Change password clicked')
      
      consoleSpy.mockRestore()
    })
  })

  describe('Navigation Events', () => {
    it('emits navigate event when login button is clicked in no user state', async () => {
      mockAuthState.user.value = null
      wrapper = mount(UserProfile)

      const loginButton = wrapper.find('button:contains("Iniciar Sesión")')
      await loginButton.trigger('click')

      expect(wrapper.emitted('navigate')).toBeTruthy()
      expect(wrapper.emitted('navigate')[0]).toEqual(['login'])
    })
  })

  describe('Accessibility', () => {
    beforeEach(() => {
      wrapper = mount(UserProfile)
    })

    it('has proper heading structure', () => {
      const h1 = wrapper.find('h1')
      const h2Elements = wrapper.findAll('h2')
      const h3 = wrapper.find('h3')

      expect(h1.exists()).toBe(true)
      expect(h2Elements.length).toBeGreaterThan(0)
      expect(h3.exists()).toBe(true)
    })

    it('has proper button structure with icons', () => {
      const buttons = wrapper.findAll('button')
      const editButton = buttons.find(btn => btn.text().includes('Editar Perfil'))
      const changePasswordButton = buttons.find(btn => btn.text().includes('Cambiar Contraseña'))

      expect(editButton.find('svg').exists()).toBe(true)
      expect(changePasswordButton.find('svg').exists()).toBe(true)
    })

    it('uses semantic HTML elements', () => {
      expect(wrapper.find('main').exists()).toBe(true)
      expect(wrapper.find('dl').exists()).toBe(true) // description list for user details
      expect(wrapper.findAll('dt').length).toBeGreaterThan(0) // description terms
      expect(wrapper.findAll('dd').length).toBeGreaterThan(0) // description details
    })
  })

  describe('Responsive Design', () => {
    beforeEach(() => {
      wrapper = mount(UserProfile)
    })

    it('has responsive classes for different screen sizes', () => {
      expect(wrapper.html()).toContain('sm:px-6')
      expect(wrapper.html()).toContain('lg:px-8')
      expect(wrapper.html()).toContain('sm:grid-cols-2')
      expect(wrapper.html()).toContain('sm:w-auto')
      expect(wrapper.html()).toContain('sm:ml-3')
    })

    it('has proper mobile-first responsive grid', () => {
      const gridElement = wrapper.find('.grid-cols-1')
      expect(gridElement.exists()).toBe(true)
      expect(gridElement.classes()).toContain('sm:grid-cols-2')
    })
  })
})