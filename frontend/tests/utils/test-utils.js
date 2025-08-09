import { mount, shallowMount } from '@vue/test-utils'
import { vi } from 'vitest'

/**
 * Crea un wrapper de componente con configuración común
 */
export const createWrapper = (component, options = {}) => {
  const defaultOptions = {
    global: {
      mocks: {
        $t: (key) => key
      },
      stubs: {
        teleport: true
      }
    }
  }

  return mount(component, {
    ...defaultOptions,
    ...options,
    global: {
      ...defaultOptions.global,
      ...options.global
    }
  })
}

/**
 * Crea un shallow wrapper de componente
 */
export const createShallowWrapper = (component, options = {}) => {
  const defaultOptions = {
    global: {
      mocks: {
        $t: (key) => key
      }
    }
  }

  return shallowMount(component, {
    ...defaultOptions,
    ...options,
    global: {
      ...defaultOptions.global,
      ...options.global
    }
  })
}

/**
 * Simula eventos de teclado
 */
export const triggerKeyboardEvent = (element, key, type = 'keydown') => {
  const event = new KeyboardEvent(type, {
    key,
    code: key,
    keyCode: getKeyCode(key),
    which: getKeyCode(key),
    bubbles: true,
    cancelable: true
  })
  
  element.dispatchEvent(event)
  return event
}

/**
 * Obtiene el código de tecla para eventos de teclado
 */
const getKeyCode = (key) => {
  const keyCodes = {
    'Escape': 27,
    'Enter': 13,
    'Tab': 9,
    'Space': 32,
    'ArrowUp': 38,
    'ArrowDown': 40,
    'ArrowLeft': 37,
    'ArrowRight': 39
  }
  return keyCodes[key] || 0
}

/**
 * Espera a que se resuelvan las promesas pendientes
 */
export const flushPromises = () => {
  return new Promise(resolve => setTimeout(resolve, 0))
}

/**
 * Simula un delay asíncrono
 */
export const delay = (ms = 0) => {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * Crea un mock de fetch con respuesta personalizada
 */
export const createFetchMock = (response, options = {}) => {
  const { status = 200, ok = true, statusText = 'OK' } = options
  
  return vi.fn().mockResolvedValue({
    ok,
    status,
    statusText,
    json: vi.fn().mockResolvedValue(response),
    text: vi.fn().mockResolvedValue(JSON.stringify(response)),
    headers: new Headers(),
    clone: vi.fn()
  })
}

/**
 * Crea un mock de fetch que falla
 */
export const createFetchErrorMock = (error = new Error('Network error')) => {
  return vi.fn().mockRejectedValue(error)
}

/**
 * Busca un elemento por texto de contenido
 */
export const findByText = (wrapper, text) => {
  return wrapper.findAll('*').find(node => {
    return node.text().includes(text)
  })
}

/**
 * Busca un elemento por atributo data-testid
 */
export const findByTestId = (wrapper, testId) => {
  return wrapper.find(`[data-testid="${testId}"]`)
}

/**
 * Verifica si un elemento está visible
 */
export const isVisible = (element) => {
  if (!element.exists()) return false
  
  const style = element.attributes('style') || ''
  return !style.includes('display: none') && !style.includes('visibility: hidden')
}

/**
 * Simula entrada de usuario en un input
 */
export const userInput = async (wrapper, selector, value) => {
  const input = wrapper.find(selector)
  await input.setValue(value)
  await input.trigger('input')
  await input.trigger('change')
}

/**
 * Simula click de usuario
 */
export const userClick = async (wrapper, selector) => {
  const element = wrapper.find(selector)
  await element.trigger('click')
}

/**
 * Crea un mock de composable
 */
export const createComposableMock = (returnValue) => {
  return vi.fn().mockReturnValue(returnValue)
}

/**
 * Crea datos de formulario válidos para empleado
 */
export const createValidEmployeeFormData = (overrides = {}) => ({
  firstName: 'John',
  lastName: 'Doe',
  email: 'john.doe@example.com',
  position: 'Developer',
  salaryAmount: 50000,
  salaryCurrency: 'EUR',
  hiredAt: '2023-01-15',
  ...overrides
})

/**
 * Crea datos de formulario inválidos para empleado
 */
export const createInvalidEmployeeFormData = () => ({
  firstName: '',
  lastName: '',
  email: 'invalid-email',
  position: '',
  salaryAmount: -1000,
  salaryCurrency: '',
  hiredAt: '2025-12-31' // Fecha futura
})

/**
 * Verifica que un elemento tenga las clases CSS especificadas
 */
export const hasClasses = (element, classes) => {
  const classList = element.classes()
  return classes.every(cls => classList.includes(cls))
}

/**
 * Espera a que un elemento aparezca en el DOM
 */
export const waitForElement = async (wrapper, selector, timeout = 1000) => {
  const start = Date.now()
  
  while (Date.now() - start < timeout) {
    await flushPromises()
    const element = wrapper.find(selector)
    if (element.exists()) {
      return element
    }
    await delay(10)
  }
  
  throw new Error(`Element ${selector} not found within ${timeout}ms`)
}

/**
 * Espera a que un elemento desaparezca del DOM
 */
export const waitForElementToDisappear = async (wrapper, selector, timeout = 1000) => {
  const start = Date.now()
  
  while (Date.now() - start < timeout) {
    await flushPromises()
    const element = wrapper.find(selector)
    if (!element.exists()) {
      return true
    }
    await delay(10)
  }
  
  throw new Error(`Element ${selector} still exists after ${timeout}ms`)
}