import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import UserProfile from '@/components/auth/UserProfile.vue'
import { useAuth } from '@/composables/useAuth'

// Mock the useAuth composable
vi.mock('@/composables/useAuth')

describe('UserProfile', () => {
  let wrapper
  let mockUseAuth

  const mockUser = {
    name: 'John Doe',
    email: 'john.doe@example.com',
    phone: '+1234567890',
    department: 'IT',
    position: 'Developer',
    role: 'admin'
  }

  beforeEach(() => {
    mockUseAuth = {
      user: vi.fn(() => mockUser),
      updateProfile: vi.fn()
    }
    
    useAuth.mockReturnValue(mockUseAuth)
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.clearAllMocks()
  })

  const createWrapper = () => {
    return mount(UserProfile, {
      global: {
        stubs: {
          // Stub any child components if needed
        }
      }
    })
  }

  describe('Basic Rendering', () => {
    test('should render the profile component', () => {
      wrapper = createWrapper()
      expect(wrapper.find('h1').text()).toBe('Mi Perfil')
    })

    test('should display user information in form fields', () => {
      wrapper = createWrapper()
      
      expect(wrapper.find('#name').element.value).toBe('John Doe')
      expect(wrapper.find('#email').element.value).toBe('john.doe@example.com')
      expect(wrapper.find('#phone').element.value).toBe('+1234567890')
      expect(wrapper.find('#department').element.value).toBe('IT')
      expect(wrapper.find('#position').element.value).toBe('Developer')
      expect(wrapper.find('#role').element.value).toBe('admin')
    })

    test('should display user initials in avatar', () => {
      wrapper = createWrapper()
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('JD')
    })

    test('should have form fields disabled by default', () => {
      wrapper = createWrapper()
      
      expect(wrapper.find('#name').attributes('disabled')).toBeDefined()
      expect(wrapper.find('#email').attributes('disabled')).toBeDefined()
      expect(wrapper.find('#phone').attributes('disabled')).toBeDefined()
    })

    test('should have role field always disabled', () => {
      wrapper = createWrapper()
      
      const roleField = wrapper.find('#role')
      expect(roleField.attributes('disabled')).toBeDefined()
    })
  })

  describe('Edit Mode', () => {
    test('should enable editing when edit button is clicked', async () => {
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const editButton = buttons.find(button => button.text().includes('Editar'))
      await editButton.trigger('click')
      
      expect(wrapper.find('#name').attributes('disabled')).toBeUndefined()
      expect(wrapper.find('#email').attributes('disabled')).toBeUndefined()
      expect(wrapper.find('#phone').attributes('disabled')).toBeUndefined()
    })

    test('should show save and cancel buttons in edit mode', async () => {
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const editButton = buttons.find(button => button.text().includes('Editar'))
      await editButton.trigger('click')
      
      const updatedButtons = wrapper.findAll('button')
      expect(updatedButtons.some(button => button.text().includes('Guardar Cambios'))).toBe(true)
      expect(updatedButtons.some(button => button.text().includes('Cancelar'))).toBe(true)
      expect(updatedButtons.some(button => button.text().includes('Editar'))).toBe(false)
    })

    test('should cancel editing and restore original values', async () => {
      wrapper = createWrapper()
      
      // Enter edit mode
      const buttons = wrapper.findAll('button')
      const editButton = buttons.find(button => button.text().includes('Editar'))
      await editButton.trigger('click')
      
      // Change a value
      const nameField = wrapper.find('#name')
      await nameField.setValue('Changed Name')
      
      // Cancel editing
      const updatedButtons = wrapper.findAll('button')
      const cancelButton = updatedButtons.find(button => button.text().includes('Cancelar'))
      await cancelButton.trigger('click')
      
      // Should restore original value and disable fields
      expect(wrapper.find('#name').element.value).toBe('John Doe')
      expect(wrapper.find('#name').attributes('disabled')).toBeDefined()
    })
  })

  describe('Form Validation', () => {
    beforeEach(async () => {
      wrapper = createWrapper()
      const buttons = wrapper.findAll('button')
      const editButton = buttons.find(button => button.text().includes('Editar'))
      await editButton.trigger('click')
    })

    test('should show error for empty name', async () => {
      const nameField = wrapper.find('#name')
      await nameField.setValue('')
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      expect(wrapper.text()).toContain('El nombre es requerido')
    })

    test('should show error for empty email', async () => {
      const emailField = wrapper.find('#email')
      await emailField.setValue('')
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      expect(wrapper.text()).toContain('El correo electrónico es requerido')
    })

    test('should show error for invalid email format', async () => {
      const emailField = wrapper.find('#email')
      await emailField.setValue('invalid-email')
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      expect(wrapper.text()).toContain('Formato de correo electrónico inválido')
    })

    test('should show error for invalid phone format', async () => {
      const phoneField = wrapper.find('#phone')
      await phoneField.setValue('invalid-phone')
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      expect(wrapper.text()).toContain('Formato de teléfono inválido')
    })

    test('should accept valid phone formats', async () => {
      const validPhones = ['+1234567890', '123-456-7890', '(123) 456-7890', '123 456 7890']
      
      for (const phone of validPhones) {
        const phoneField = wrapper.find('#phone')
        await phoneField.setValue(phone)
        
        const form = wrapper.find('form')
        await form.trigger('submit.prevent')
        
        expect(wrapper.text()).not.toContain('Formato de teléfono inválido')
      }
    })
  })

  describe('Form Submission', () => {
    beforeEach(async () => {
      wrapper = createWrapper()
      const buttons = wrapper.findAll('button')
      const editButton = buttons.find(button => button.text().includes('Editar'))
      await editButton.trigger('click')
    })

    test('should call updateProfile with form data on valid submission', async () => {
      mockUseAuth.updateProfile.mockResolvedValue()
      
      const nameField = wrapper.find('#name')
      await nameField.setValue('Updated Name')
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      expect(mockUseAuth.updateProfile).toHaveBeenCalledWith({
        name: 'Updated Name',
        email: 'john.doe@example.com',
        phone: '+1234567890',
        department: 'IT',
        position: 'Developer'
      })
    })

    test('should show success message after successful update', async () => {
      mockUseAuth.updateProfile.mockResolvedValue()
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      await wrapper.vm.$nextTick()
      
      expect(wrapper.text()).toContain('Perfil actualizado correctamente')
    })

    test('should exit edit mode after successful update', async () => {
      mockUseAuth.updateProfile.mockResolvedValue()
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      await wrapper.vm.$nextTick()
      
      expect(wrapper.find('#name').attributes('disabled')).toBeDefined()
      const buttons = wrapper.findAll('button')
      expect(buttons.some(button => button.text().includes('Editar'))).toBe(true)
    })

    test('should show loading state during submission', async () => {
      let resolvePromise
      const promise = new Promise(resolve => {
        resolvePromise = resolve
      })
      mockUseAuth.updateProfile.mockReturnValue(promise)
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      expect(wrapper.text()).toContain('Guardando...')
      
      resolvePromise()
      await promise
    })

    test('should handle update profile error', async () => {
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      mockUseAuth.updateProfile.mockRejectedValue(new Error('Update failed'))
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      await wrapper.vm.$nextTick()
      
      expect(consoleSpy).toHaveBeenCalledWith('Error updating profile:', expect.any(Error))
      
      consoleSpy.mockRestore()
    })
  })

  describe('User Initials Generation', () => {
    test('should generate correct initials for full name', () => {
      mockUseAuth.user.mockReturnValue({ name: 'Jane Smith Doe' })
      wrapper = createWrapper()
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('JS')
    })

    test('should use email initial when no name provided', () => {
      mockUseAuth.user.mockReturnValue({ email: 'alice@example.com' })
      wrapper = createWrapper()
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('A')
    })

    test('should show default initial when no name or email', () => {
      mockUseAuth.user.mockReturnValue({})
      wrapper = createWrapper()
      
      const avatar = wrapper.find('.bg-blue-600')
      expect(avatar.text()).toBe('U')
    })
  })

  describe('Security Section', () => {
    test('should display security section', () => {
      wrapper = createWrapper()
      
      expect(wrapper.text()).toContain('Seguridad de la Cuenta')
      expect(wrapper.text()).toContain('Contraseña')
      expect(wrapper.text()).toContain('Autenticación de dos factores')
    })

    test('should have change password button', () => {
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const changePasswordButton = buttons.find(button => button.text().includes('Cambiar'))
      expect(changePasswordButton).toBeTruthy()
    })

    test('should have two-factor authentication button', () => {
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const twoFactorButton = buttons.find(button => button.text().includes('Configurar'))
      expect(twoFactorButton).toBeTruthy()
    })

    test('should log when change password is clicked', async () => {
      const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {})
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const changePasswordButton = buttons.find(button => button.text().includes('Cambiar'))
      await changePasswordButton.trigger('click')
      
      expect(consoleSpy).toHaveBeenCalledWith('Change password clicked')
      
      consoleSpy.mockRestore()
    })

    test('should log when two-factor auth is clicked', async () => {
      const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {})
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const twoFactorButton = buttons.find(button => button.text().includes('Configurar'))
      await twoFactorButton.trigger('click')
      
      expect(consoleSpy).toHaveBeenCalledWith('Two-factor authentication clicked')
      
      consoleSpy.mockRestore()
    })
  })

  describe('Avatar Section', () => {
    test('should display avatar upload section', () => {
      wrapper = createWrapper()
      
      expect(wrapper.text()).toContain('Foto de Perfil')
      expect(wrapper.text()).toContain('Cambiar Foto')
    })

    test('should log when avatar upload is clicked', async () => {
      const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {})
      wrapper = createWrapper()
      
      const buttons = wrapper.findAll('button')
      const avatarButton = buttons.find(button => button.text().includes('Cambiar Foto'))
      await avatarButton.trigger('click')
      
      expect(consoleSpy).toHaveBeenCalledWith('Avatar upload clicked')
      
      consoleSpy.mockRestore()
    })
  })

  describe('Edge Cases', () => {
    test('should handle user with minimal data', () => {
      mockUseAuth.user.mockReturnValue({ email: 'minimal@example.com' })
      wrapper = createWrapper()
      
      expect(wrapper.find('#name').element.value).toBe('')
      expect(wrapper.find('#email').element.value).toBe('minimal@example.com')
      expect(wrapper.find('#phone').element.value).toBe('')
    })

    test('should handle null user', () => {
      mockUseAuth.user.mockReturnValue(null)
      wrapper = createWrapper()
      
      expect(wrapper.find('#name').element.value).toBe('')
      expect(wrapper.find('#email').element.value).toBe('')
    })

    test('should clear errors when canceling edit', async () => {
      wrapper = createWrapper()
      
      // Enter edit mode
      const buttons = wrapper.findAll('button')
      const editButton = buttons.find(button => button.text().includes('Editar'))
      await editButton.trigger('click')
      
      // Create validation error
      const nameField = wrapper.find('#name')
      await nameField.setValue('')
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      expect(wrapper.text()).toContain('El nombre es requerido')
      
      // Cancel editing
      const updatedButtons = wrapper.findAll('button')
      const cancelButton = updatedButtons.find(button => button.text().includes('Cancelar'))
      await cancelButton.trigger('click')
      
      // Error should be cleared
      expect(wrapper.text()).not.toContain('El nombre es requerido')
    })
  })

  describe('Accessibility', () => {
    test('should have proper labels for form fields', () => {
      wrapper = createWrapper()
      
      expect(wrapper.find('label[for="name"]').text()).toContain('Nombre Completo')
      expect(wrapper.find('label[for="email"]').text()).toContain('Correo Electrónico')
      expect(wrapper.find('label[for="phone"]').text()).toContain('Teléfono')
    })

    test('should associate error messages with form fields', async () => {
      wrapper = createWrapper()
      
      const editButton = wrapper.find('button:contains("Editar")')
      await editButton.trigger('click')
      
      const nameField = wrapper.find('#name')
      await nameField.setValue('')
      
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      
      const nameFieldWithError = wrapper.find('#name')
      expect(nameFieldWithError.classes()).toContain('border-red-300')
    })
  })
})