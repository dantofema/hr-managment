import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import UserProfile from '../UserProfile.vue'

// Simple mock for useAuth
vi.mock('@/composables/useAuth', () => ({
  useAuth: () => ({
    user: ref({
      id: 1,
      name: 'John Doe',
      email: 'john.doe@example.com',
      roles: ['ROLE_USER'],
      createdAt: '2023-01-15T10:30:00Z',
      lastLoginAt: '2024-01-10T14:20:00Z'
    }),
    isLoading: ref(false),
    error: ref(null)
  })
}))

describe('UserProfile - Basic Tests', () => {
  it('renders the component without errors', () => {
    const wrapper = mount(UserProfile)
    expect(wrapper.exists()).toBe(true)
  })

  it('displays the profile title', () => {
    const wrapper = mount(UserProfile)
    expect(wrapper.find('h1').text()).toBe('Perfil de Usuario')
  })

  it('displays user information when available', () => {
    const wrapper = mount(UserProfile)
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('john.doe@example.com')
  })

  it('displays user roles', () => {
    const wrapper = mount(UserProfile)
    expect(wrapper.text()).toContain('Usuario')
  })

  it('displays action buttons', () => {
    const wrapper = mount(UserProfile)
    expect(wrapper.text()).toContain('Editar Perfil')
    expect(wrapper.text()).toContain('Cambiar Contraseña')
  })
})