import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import BaseModal from '@/components/ui/BaseModal.vue'

describe('BaseModal.vue', () => {
  let wrapper
  let mockBodyStyle

  beforeEach(() => {
    // Mock document.body.style
    mockBodyStyle = {
      overflow: '',
      paddingRight: ''
    }
    Object.defineProperty(document.body, 'style', {
      value: mockBodyStyle,
      writable: true
    })

    // Mock window dimensions
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 1024
    })
    Object.defineProperty(document.documentElement, 'clientWidth', {
      writable: true,
      configurable: true,
      value: 1000
    })

    // Mock focus methods
    HTMLElement.prototype.focus = vi.fn()
    HTMLElement.prototype.blur = vi.fn()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.clearAllMocks()
  })

  test('should render when isOpen is true', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    expect(wrapper.find('[role="dialog"]').exists()).toBe(true)
    expect(wrapper.find('.fixed.inset-0').exists()).toBe(true)
    expect(wrapper.text()).toContain('Test Modal')
  })

  test('should not render when isOpen is false', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: false,
        title: 'Test Modal'
      }
    })

    await nextTick()

    expect(wrapper.find('[role="dialog"]').exists()).toBe(false)
    expect(wrapper.find('.fixed.inset-0').exists()).toBe(false)
  })

  test('should emit close event when backdrop is clicked', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    const backdrop = wrapper.find('[role="dialog"]')
    await backdrop.trigger('click')

    expect(wrapper.emitted('close')).toBeTruthy()
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  test('should not emit close event when modal content is clicked', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    const modalContent = wrapper.find('.bg-white.rounded-lg')
    await modalContent.trigger('click')

    expect(wrapper.emitted('close')).toBeFalsy()
  })

  test('should emit close event when ESC key is pressed', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    // Simulate ESC key press
    const escapeEvent = new KeyboardEvent('keydown', { key: 'Escape' })
    document.dispatchEvent(escapeEvent)

    await nextTick()

    expect(wrapper.emitted('close')).toBeTruthy()
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  test('should emit close event when close button is clicked', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    const closeButton = wrapper.find('button[aria-label="Cerrar modal"]')
    await closeButton.trigger('click')

    expect(wrapper.emitted('close')).toBeTruthy()
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  test('should apply correct size classes', async () => {
    const testCases = [
      { size: 'sm', expectedClass: 'max-w-sm' },
      { size: 'md', expectedClass: 'max-w-md' },
      { size: 'lg', expectedClass: 'max-w-lg' },
      { size: 'xl', expectedClass: 'max-w-xl' }
    ]

    for (const { size, expectedClass } of testCases) {
      wrapper = mount(BaseModal, {
        props: {
          isOpen: true,
          title: 'Test Modal',
          size
        },
        attachTo: document.body
      })

      await nextTick()

      const modalContent = wrapper.find('.bg-white.rounded-lg')
      expect(modalContent.classes()).toContain(expectedClass)

      wrapper.unmount()
    }
  })

  test('should use default size when no size prop is provided', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    const modalContent = wrapper.find('.bg-white.rounded-lg')
    expect(modalContent.classes()).toContain('max-w-md')
  })

  test('should render title correctly', async () => {
    const testTitle = 'My Custom Modal Title'
    
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: testTitle
      },
      attachTo: document.body
    })

    await nextTick()

    const titleElement = wrapper.find('h2')
    expect(titleElement.text()).toBe(testTitle)
    expect(titleElement.attributes('id')).toMatch(/^modal-title-/)
  })

  test('should render slots content', async () => {
    const defaultSlotContent = '<p>Default slot content</p>'
    const footerSlotContent = '<button>Footer Button</button>'

    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      slots: {
        default: defaultSlotContent,
        footer: footerSlotContent
      },
      attachTo: document.body
    })

    await nextTick()

    expect(wrapper.html()).toContain('Default slot content')
    expect(wrapper.html()).toContain('Footer Button')
    expect(wrapper.find('.bg-gray-50').exists()).toBe(true) // Footer background
  })

  test('should not render footer when no footer slot is provided', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      slots: {
        default: '<p>Content only</p>'
      },
      attachTo: document.body
    })

    await nextTick()

    expect(wrapper.find('.bg-gray-50').exists()).toBe(false)
  })

  test('should trap focus within modal', async () => {
    const modalContent = `
      <input id="first-input" type="text" />
      <button id="middle-button">Middle</button>
      <input id="last-input" type="text" />
    `

    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      slots: {
        default: modalContent
      },
      attachTo: document.body
    })

    await nextTick()
    await nextTick() // Wait for focus trap setup

    // Verify first focusable element gets focus
    const firstInput = document.getElementById('first-input')
    expect(firstInput.focus).toHaveBeenCalled()
  })

  test('should handle Tab key for focus trap', async () => {
    const modalContent = `
      <input id="first-input" type="text" />
      <input id="last-input" type="text" />
    `

    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      slots: {
        default: modalContent
      },
      attachTo: document.body
    })

    await nextTick()
    await nextTick()

    const lastInput = document.getElementById('last-input')
    const firstInput = document.getElementById('first-input')

    // Mock activeElement to be the last input
    Object.defineProperty(document, 'activeElement', {
      value: lastInput,
      writable: true
    })

    // Simulate Tab key press on last element
    const tabEvent = new KeyboardEvent('keydown', { 
      key: 'Tab',
      bubbles: true,
      cancelable: true
    })
    
    const preventDefaultSpy = vi.spyOn(tabEvent, 'preventDefault')
    document.dispatchEvent(tabEvent)

    expect(preventDefaultSpy).toHaveBeenCalled()
    expect(firstInput.focus).toHaveBeenCalled()
  })

  test('should handle Shift+Tab key for focus trap', async () => {
    const modalContent = `
      <input id="first-input" type="text" />
      <input id="last-input" type="text" />
    `

    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      slots: {
        default: modalContent
      },
      attachTo: document.body
    })

    await nextTick()
    await nextTick()

    const firstInput = document.getElementById('first-input')
    const lastInput = document.getElementById('last-input')

    // Mock activeElement to be the first input
    Object.defineProperty(document, 'activeElement', {
      value: firstInput,
      writable: true
    })

    // Simulate Shift+Tab key press on first element
    const shiftTabEvent = new KeyboardEvent('keydown', { 
      key: 'Tab',
      shiftKey: true,
      bubbles: true,
      cancelable: true
    })
    
    const preventDefaultSpy = vi.spyOn(shiftTabEvent, 'preventDefault')
    document.dispatchEvent(shiftTabEvent)

    expect(preventDefaultSpy).toHaveBeenCalled()
    expect(lastInput.focus).toHaveBeenCalled()
  })

  test('should lock body scroll when modal opens', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    expect(mockBodyStyle.overflow).toBe('hidden')
    expect(mockBodyStyle.paddingRight).toBe('24px') // 1024 - 1000 = 24px
  })

  test('should unlock body scroll when modal closes', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()
    expect(mockBodyStyle.overflow).toBe('hidden')

    await wrapper.setProps({ isOpen: false })
    await nextTick()

    expect(mockBodyStyle.overflow).toBe('')
    expect(mockBodyStyle.paddingRight).toBe('')
  })

  test('should have proper accessibility attributes', async () => {
    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Accessible Modal'
      },
      attachTo: document.body
    })

    await nextTick()

    const dialog = wrapper.find('[role="dialog"]')
    const title = wrapper.find('h2')
    
    expect(dialog.attributes('aria-modal')).toBe('true')
    expect(dialog.attributes('aria-labelledby')).toBe(title.attributes('id'))
    expect(title.attributes('id')).toMatch(/^modal-title-/)
  })

  test('should cleanup event listeners on unmount', async () => {
    const removeEventListenerSpy = vi.spyOn(document, 'removeEventListener')

    wrapper = mount(BaseModal, {
      props: {
        isOpen: true,
        title: 'Test Modal'
      },
      attachTo: document.body
    })

    await nextTick()
    wrapper.unmount()

    expect(removeEventListenerSpy).toHaveBeenCalledWith('keydown', expect.any(Function))
    expect(mockBodyStyle.overflow).toBe('')
    expect(mockBodyStyle.paddingRight).toBe('')
  })
})