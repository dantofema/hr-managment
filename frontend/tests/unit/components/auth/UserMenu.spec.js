import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import UserMenu from '@/components/auth/UserMenu.vue'

describe('UserMenu', () => {
  let wrapper

  const mockUser = {
    name: 'John Doe',
    email: 'john.doe@example.com',
    role: 'admin'
  }

  beforeEach(() => {
    // Mock DOM methods
    global.document.addEventListener = vi.fn()
    global.document.removeEventListener = vi.fn()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.clearAllMocks()
  })

  const createWrapper = (props = {}) => {
    return mount(UserMenu, {
      props: {
        user: mockUser,
        ...props
      }
    })
  }

  describe('Basic Rendering', () => {
    test('should render the user menu component', () => {
      wrapper = createWrapper()
      expect(wrapper.find('button').exists()).toBe(true)
    })

    test('should display user initials in avatar', () => {
      wrapper = createWrapper()
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('JD')
    })

    test('should display user name', () => {
      wrapper = createWrapper()
      expect(wrapper.text()).toContain('John Doe')
    })

    test('should handle user with only email', () => {
      const userWithEmailOnly = { email: 'test@example.com' }
      wrapper = createWrapper({ user: userWithEmailOnly })
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('T')
      expect(wrapper.text()).toContain('test')
    })

    test('should handle user with no name or email', () => {
      const emptyUser = {}
      wrapper = createWrapper({ user: emptyUser })
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('U')
      expect(wrapper.text()).toContain('Usuario')
    })
  })

  describe('User Initials Generation', () => {
    test('should generate correct initials for full name', () => {
      const user = { name: 'Jane Smith Doe' }
      wrapper = createWrapper({ user })
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('JS')
    })

    test('should generate single initial for single name', () => {
      const user = { name: 'John' }
      wrapper = createWrapper({ user })
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('J')
    })

    test('should use email initial when no name provided', () => {
      const user = { email: 'alice@example.com' }
      wrapper = createWrapper({ user })
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('A')
    })
  })

  describe('Dropdown Menu Functionality', () => {
    test('should initially have dropdown closed', () => {
      wrapper = createWrapper()
      expect(wrapper.find('[role="menu"]').exists()).toBe(false)
    })

    test('should open dropdown when button is clicked', async () => {
      wrapper = createWrapper()
      
      const button = wrapper.find('button')
      await button.trigger('click')
      
      expect(wrapper.find('[role="menu"]').exists()).toBe(true)
    })

    test('should close dropdown when button is clicked again', async () => {
      wrapper = createWrapper()
      
      const button = wrapper.find('button')
      await button.trigger('click')
      expect(wrapper.find('[role="menu"]').exists()).toBe(true)
      
      await button.trigger('click')
      expect(wrapper.find('[role="menu"]').exists()).toBe(false)
    })

    test('should rotate arrow icon when dropdown is open', async () => {
      wrapper = createWrapper()
      
      const arrow = wrapper.find('svg')
      expect(arrow.classes()).not.toContain('rotate-180')
      
      const button = wrapper.find('button')
      await button.trigger('click')
      
      expect(arrow.classes()).toContain('rotate-180')
    })
  })

  describe('Menu Items', () => {
    beforeEach(async () => {
      wrapper = createWrapper()
      const button = wrapper.find('button')
      await button.trigger('click')
    })

    test('should display user information in dropdown', () => {
      const dropdown = wrapper.find('[role="menu"]')
      expect(dropdown.text()).toContain('John Doe')
      expect(dropdown.text()).toContain('john.doe@example.com')
      expect(dropdown.text()).toContain('admin')
    })

    test('should have profile menu item', () => {
      const buttons = wrapper.findAll('button')
      const profileButton = buttons.find(button => button.text().includes('Mi Perfil'))
      expect(profileButton).toBeTruthy()
    })

    test('should have settings menu item', () => {
      const buttons = wrapper.findAll('button')
      const settingsButton = buttons.find(button => button.text().includes('Configuración'))
      expect(settingsButton).toBeTruthy()
    })

    test('should have logout menu item', () => {
      const buttons = wrapper.findAll('button')
      const logoutButton = buttons.find(button => button.text().includes('Cerrar Sesión'))
      expect(logoutButton).toBeTruthy()
    })

    test('should emit profile event when profile is clicked', async () => {
      const buttons = wrapper.findAll('button')
      const profileButton = buttons.find(button => button.text().includes('Mi Perfil'))
      await profileButton.trigger('click')
      
      expect(wrapper.emitted('profile')).toBeTruthy()
      expect(wrapper.find('[role="menu"]').exists()).toBe(false)
    })

    test('should emit settings event when settings is clicked', async () => {
      const buttons = wrapper.findAll('button')
      const settingsButton = buttons.find(button => button.text().includes('Configuración'))
      await settingsButton.trigger('click')
      
      expect(wrapper.emitted('settings')).toBeTruthy()
      expect(wrapper.find('[role="menu"]').exists()).toBe(false)
    })

    test('should emit logout event when logout is clicked', async () => {
      const buttons = wrapper.findAll('button')
      const logoutButton = buttons.find(button => button.text().includes('Cerrar Sesión'))
      await logoutButton.trigger('click')
      
      expect(wrapper.emitted('logout')).toBeTruthy()
      expect(wrapper.find('[role="menu"]').exists()).toBe(false)
    })
  })

  describe('Keyboard Navigation', () => {
    test('should close dropdown on escape key', async () => {
      wrapper = createWrapper()
      
      const button = wrapper.find('button')
      await button.trigger('click')
      expect(wrapper.find('[role="menu"]').exists()).toBe(true)
      
      await button.trigger('keydown.escape')
      expect(wrapper.find('[role="menu"]').exists()).toBe(false)
    })

    test('should have proper ARIA attributes', async () => {
      wrapper = createWrapper()
      
      const button = wrapper.find('button')
      expect(button.attributes('aria-haspopup')).toBe('true')
      expect(button.attributes('aria-expanded')).toBe('false')
      
      await button.trigger('click')
      expect(button.attributes('aria-expanded')).toBe('true')
    })
  })

  describe('Menu Items Accessibility', () => {
    beforeEach(async () => {
      wrapper = createWrapper()
      const button = wrapper.find('button')
      await button.trigger('click')
    })

    test('should have proper role attributes', () => {
      const menu = wrapper.find('[role="menu"]')
      expect(menu.exists()).toBe(true)
      expect(menu.attributes('aria-orientation')).toBe('vertical')
    })

    test('should have menuitem role for menu buttons', () => {
      const menuItems = wrapper.findAll('[role="menuitem"]')
      expect(menuItems.length).toBeGreaterThan(0)
    })
  })

  describe('Visual States', () => {
    test('should have hover states for menu items', async () => {
      wrapper = createWrapper()
      const button = wrapper.find('button')
      await button.trigger('click')
      
      const profileButton = wrapper.find('button:contains("Mi Perfil")')
      expect(profileButton.classes()).toContain('hover:bg-gray-100')
    })

    test('should have different styling for logout button', async () => {
      wrapper = createWrapper()
      const button = wrapper.find('button')
      await button.trigger('click')
      
      const logoutButton = wrapper.find('button:contains("Cerrar Sesión")')
      expect(logoutButton.classes()).toContain('text-red-700')
      expect(logoutButton.classes()).toContain('hover:bg-red-50')
    })
  })

  describe('Responsive Design', () => {
    test('should have mobile backdrop when open', async () => {
      wrapper = createWrapper()
      
      const button = wrapper.find('button')
      await button.trigger('click')
      
      const backdrop = wrapper.find('.fixed.inset-0.z-40.md\\:hidden')
      expect(backdrop.exists()).toBe(true)
    })

    test('should hide user name on small screens', () => {
      wrapper = createWrapper()
      
      const userName = wrapper.find('.hidden.md\\:block')
      expect(userName.exists()).toBe(true)
      expect(userName.text()).toContain('John Doe')
    })
  })

  describe('Transitions', () => {
    test('should have transition classes for dropdown', async () => {
      wrapper = createWrapper()
      
      const button = wrapper.find('button')
      await button.trigger('click')
      
      const dropdown = wrapper.find('[role="menu"]')
      expect(dropdown.exists()).toBe(true)
      // Note: Testing actual transitions might require more complex setup
    })
  })

  describe('Edge Cases', () => {
    test('should handle very long user names', () => {
      const userWithLongName = {
        name: 'Very Long User Name That Should Be Handled Properly',
        email: 'long@example.com'
      }
      wrapper = createWrapper({ user: userWithLongName })
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('VL') // Should only show first two initials
    })

    test('should handle special characters in name', () => {
      const userWithSpecialChars = {
        name: 'José María Ñoño',
        email: 'jose@example.com'
      }
      wrapper = createWrapper({ user: userWithSpecialChars })
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('JM')
    })

    test('should handle missing user role', () => {
      const userWithoutRole = {
        name: 'John Doe',
        email: 'john@example.com'
      }
      wrapper = createWrapper({ user: userWithoutRole })
      
      // Should not crash and should not display role
      expect(wrapper.text()).toContain('John Doe')
      expect(wrapper.text()).toContain('john@example.com')
    })
  })
})