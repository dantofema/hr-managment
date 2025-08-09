import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import EmployeeForm from '@/components/employees/EmployeeForm.vue'

describe('EmployeeForm.vue', () => {
  let wrapper

  const mockEmployee = {
    id: 1,
    firstName: 'John',
    lastName: 'Doe',
    email: 'john.doe@example.com',
    position: 'Developer',
    salaryAmount: 50000,
    salaryCurrency: 'EUR',
    hiredAt: '2023-01-15'
  }

  beforeEach(() => {
    // Mock Date for consistent testing
    vi.useFakeTimers()
    vi.setSystemTime(new Date('2024-01-15'))
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.useRealTimers()
    vi.clearAllMocks()
  })

  test('should render in create mode', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.find('form').exists()).toBe(true)
    expect(wrapper.find('#firstName').exists()).toBe(true)
    expect(wrapper.find('#lastName').exists()).toBe(true)
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#position').exists()).toBe(true)
    expect(wrapper.find('#salaryAmount').exists()).toBe(true)
    expect(wrapper.find('#salaryCurrency').exists()).toBe(true)
    expect(wrapper.find('#hiredAt').exists()).toBe(true)
    
    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.text()).toContain('Crear Empleado')
  })

  test('should render in edit mode with employee data', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'edit',
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.find('#firstName').element.value).toBe(mockEmployee.firstName)
    expect(wrapper.find('#lastName').element.value).toBe(mockEmployee.lastName)
    expect(wrapper.find('#email').element.value).toBe(mockEmployee.email)
    expect(wrapper.find('#position').element.value).toBe(mockEmployee.position)
    expect(wrapper.find('#salaryAmount').element.value).toBe(mockEmployee.salaryAmount.toString())
    expect(wrapper.find('#salaryCurrency').element.value).toBe(mockEmployee.salaryCurrency)
    expect(wrapper.find('#hiredAt').element.value).toBe(mockEmployee.hiredAt)
    
    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.text()).toContain('Actualizar Empleado')
  })

  test('should validate required fields', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    // Trigger validation by blurring empty fields
    const firstName = wrapper.find('#firstName')
    await firstName.trigger('blur')
    await nextTick()

    const lastName = wrapper.find('#lastName')
    await lastName.trigger('blur')
    await nextTick()

    const email = wrapper.find('#email')
    await email.trigger('blur')
    await nextTick()

    const position = wrapper.find('#position')
    await position.trigger('blur')
    await nextTick()

    const salaryAmount = wrapper.find('#salaryAmount')
    await salaryAmount.trigger('blur')
    await nextTick()

    const salaryCurrency = wrapper.find('#salaryCurrency')
    await salaryCurrency.trigger('change')
    await nextTick()

    const hiredAt = wrapper.find('#hiredAt')
    await hiredAt.trigger('blur')
    await nextTick()

    // Check for error messages
    expect(wrapper.text()).toContain('El nombre es obligatorio')
    expect(wrapper.text()).toContain('El apellido es obligatorio')
    expect(wrapper.text()).toContain('El email es obligatorio')
    expect(wrapper.text()).toContain('La posición es obligatoria')
    expect(wrapper.text()).toContain('El salario es obligatorio')
    expect(wrapper.text()).toContain('La moneda es obligatoria')
    expect(wrapper.text()).toContain('La fecha de contratación es obligatoria')
  })

  test('should validate email format', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const emailInput = wrapper.find('#email')
    
    // Test invalid email
    await emailInput.setValue('invalid-email')
    await emailInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('Ingrese un email válido')

    // Test valid email
    await emailInput.setValue('valid@example.com')
    await emailInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).not.toContain('Ingrese un email válido')
  })

  test('should validate salary as positive number', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const salaryInput = wrapper.find('#salaryAmount')
    
    // Test negative salary
    await salaryInput.setValue(-1000)
    await salaryInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('El salario debe ser un número positivo')

    // Test zero salary
    await salaryInput.setValue(0)
    await salaryInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('El salario debe ser un número positivo')

    // Test valid salary
    await salaryInput.setValue(50000)
    await salaryInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).not.toContain('El salario debe ser un número positivo')
  })

  test('should validate salary decimal places', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const salaryInput = wrapper.find('#salaryAmount')
    
    // Test too many decimal places
    await salaryInput.setValue('50000.123')
    await salaryInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('El salario puede tener máximo 2 decimales')

    // Test valid decimal places
    await salaryInput.setValue('50000.50')
    await salaryInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).not.toContain('El salario puede tener máximo 2 decimales')
  })

  test('should validate hired date not in future', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const hiredAtInput = wrapper.find('#hiredAt')
    
    // Test future date
    await hiredAtInput.setValue('2025-01-01')
    await hiredAtInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('La fecha de contratación no puede ser futura')

    // Test valid date (today)
    await hiredAtInput.setValue('2024-01-15')
    await hiredAtInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).not.toContain('La fecha de contratación no puede ser futura')
  })

  test('should validate name fields contain only letters', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const firstNameInput = wrapper.find('#firstName')
    const lastNameInput = wrapper.find('#lastName')
    
    // Test invalid characters in firstName
    await firstNameInput.setValue('John123')
    await firstNameInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('El nombre solo puede contener letras y espacios')

    // Test invalid characters in lastName
    await lastNameInput.setValue('Doe@#$')
    await lastNameInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('El apellido solo puede contener letras y espacios')

    // Test valid names
    await firstNameInput.setValue('John')
    await firstNameInput.trigger('blur')
    await nextTick()

    await lastNameInput.setValue('Doe')
    await lastNameInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).not.toContain('El nombre solo puede contener letras y espacios')
    expect(wrapper.text()).not.toContain('El apellido solo puede contener letras y espacios')
  })

  test('should validate minimum length for names', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const firstNameInput = wrapper.find('#firstName')
    const lastNameInput = wrapper.find('#lastName')
    
    // Test too short names
    await firstNameInput.setValue('J')
    await firstNameInput.trigger('blur')
    await nextTick()

    await lastNameInput.setValue('D')
    await lastNameInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('El nombre debe tener al menos 2 caracteres')
    expect(wrapper.text()).toContain('El apellido debe tener al menos 2 caracteres')
  })

  test('should validate position maximum length', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const positionInput = wrapper.find('#position')
    const longPosition = 'A'.repeat(101) // 101 characters
    
    await positionInput.setValue(longPosition)
    await positionInput.trigger('blur')
    await nextTick()

    expect(wrapper.text()).toContain('La posición no puede exceder 100 caracteres')
  })

  test('should emit submit event with valid data', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    // Fill form with valid data
    await wrapper.find('#firstName').setValue('John')
    await wrapper.find('#lastName').setValue('Doe')
    await wrapper.find('#email').setValue('john.doe@example.com')
    await wrapper.find('#position').setValue('Developer')
    await wrapper.find('#salaryAmount').setValue(50000)
    await wrapper.find('#salaryCurrency').setValue('EUR')
    await wrapper.find('#hiredAt').setValue('2024-01-15')

    await nextTick()

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    expect(wrapper.emitted('submit')).toBeTruthy()
    expect(wrapper.emitted('submit')).toHaveLength(1)
    
    const emittedData = wrapper.emitted('submit')[0][0]
    expect(emittedData).toEqual({
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      position: 'Developer',
      salaryAmount: 50000,
      salaryCurrency: 'EUR',
      hiredAt: '2024-01-15'
    })
  })

  test('should emit cancel event', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const cancelButton = wrapper.find('button[type="button"]')
    await cancelButton.trigger('click')

    expect(wrapper.emitted('cancel')).toBeTruthy()
    expect(wrapper.emitted('cancel')).toHaveLength(1)
  })

  test('should disable submit when form is invalid', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.attributes('disabled')).toBeDefined()
    expect(submitButton.classes()).toContain('bg-gray-400')
    expect(submitButton.classes()).toContain('cursor-not-allowed')
  })

  test('should enable submit when form is valid', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    // Fill form with valid data
    await wrapper.find('#firstName').setValue('John')
    await wrapper.find('#lastName').setValue('Doe')
    await wrapper.find('#email').setValue('john.doe@example.com')
    await wrapper.find('#position').setValue('Developer')
    await wrapper.find('#salaryAmount').setValue(50000)
    await wrapper.find('#salaryCurrency').setValue('EUR')
    await wrapper.find('#hiredAt').setValue('2024-01-15')

    await nextTick()

    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.attributes('disabled')).toBeUndefined()
    expect(submitButton.classes()).toContain('bg-blue-600')
    expect(submitButton.classes()).not.toContain('cursor-not-allowed')
  })

  test('should show loading state', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: true
      }
    })

    await nextTick()

    const submitButton = wrapper.find('button[type="submit"]')
    const cancelButton = wrapper.find('button[type="button"]')
    
    expect(submitButton.attributes('disabled')).toBeDefined()
    expect(cancelButton.attributes('disabled')).toBeDefined()
    expect(submitButton.text()).toContain('Procesando...')
    expect(wrapper.find('.animate-spin').exists()).toBe(true)

    // Check that inputs are disabled
    expect(wrapper.find('#firstName').attributes('disabled')).toBeDefined()
    expect(wrapper.find('#lastName').attributes('disabled')).toBeDefined()
    expect(wrapper.find('#email').attributes('disabled')).toBeDefined()
  })

  test('should reset form after successful submit', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    // Fill form with data
    await wrapper.find('#firstName').setValue('John')
    await wrapper.find('#lastName').setValue('Doe')
    await wrapper.find('#email').setValue('john.doe@example.com')

    // Verify data is there
    expect(wrapper.find('#firstName').element.value).toBe('John')
    expect(wrapper.find('#lastName').element.value).toBe('Doe')
    expect(wrapper.find('#email').element.value).toBe('john.doe@example.com')

    // Simulate successful submit by changing mode to create (reset trigger)
    await wrapper.setProps({ mode: 'create' })
    await nextTick()

    // In create mode, form should be empty initially
    expect(wrapper.vm.formData.firstName).toBe('')
    expect(wrapper.vm.formData.lastName).toBe('')
    expect(wrapper.vm.formData.email).toBe('')
  })

  test('should show character count for position field', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const positionInput = wrapper.find('#position')
    await positionInput.setValue('Developer')
    await nextTick()

    expect(wrapper.text()).toContain('9/100 caracteres')
  })

  test('should validate currency selection', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const currencySelect = wrapper.find('#salaryCurrency')
    
    // Test invalid currency (should not happen in normal use but test validation)
    await currencySelect.setValue('INVALID')
    await currencySelect.trigger('change')
    await nextTick()

    expect(wrapper.text()).toContain('Seleccione una moneda válida')

    // Test valid currency
    await currencySelect.setValue('EUR')
    await currencySelect.trigger('change')
    await nextTick()

    expect(wrapper.text()).not.toContain('Seleccione una moneda válida')
  })

  test('should have proper accessibility attributes', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    // Check aria-describedby attributes
    expect(wrapper.find('#firstName').attributes('aria-describedby')).toBe('firstName-error')
    expect(wrapper.find('#lastName').attributes('aria-describedby')).toBe('lastName-error')
    expect(wrapper.find('#email').attributes('aria-describedby')).toBe('email-error')
    expect(wrapper.find('#position').attributes('aria-describedby')).toBe('position-error')
    expect(wrapper.find('#salaryAmount').attributes('aria-describedby')).toBe('salaryAmount-error')
    expect(wrapper.find('#salaryCurrency').attributes('aria-describedby')).toBe('salaryCurrency-error')
    expect(wrapper.find('#hiredAt').attributes('aria-describedby')).toBe('hiredAt-error')

    // Check required field indicators
    expect(wrapper.text()).toContain('*')
  })

  test('should set max date attribute correctly', async () => {
    wrapper = mount(EmployeeForm, {
      props: {
        mode: 'create',
        loading: false
      }
    })

    await nextTick()

    const hiredAtInput = wrapper.find('#hiredAt')
    expect(hiredAtInput.attributes('max')).toBe('2024-01-15') // Current date from mocked time
  })
})