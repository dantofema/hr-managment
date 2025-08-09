import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import EmployeeDetail from '@/components/employees/EmployeeDetail.vue'

describe('EmployeeDetail.vue', () => {
  let wrapper

  const mockEmployee = {
    id: 1,
    firstName: 'John',
    lastName: 'Doe',
    email: 'john.doe@example.com',
    position: 'Senior Developer',
    salaryAmount: 75000,
    salaryCurrency: 'EUR',
    hiredAt: '2020-01-15T00:00:00+00:00'
  }

  const mockEmployeeWithInvalidData = {
    id: 2,
    firstName: '',
    lastName: '',
    email: 'invalid-email',
    position: '',
    salaryAmount: null,
    salaryCurrency: 'INVALID',
    hiredAt: 'invalid-date'
  }

  beforeEach(() => {
    // Mock Date for consistent testing
    vi.useFakeTimers()
    vi.setSystemTime(new Date('2024-01-15'))

    // Mock window.confirm
    global.confirm = vi.fn(() => true)

    // Mock console.warn to avoid noise in tests
    vi.spyOn(console, 'warn').mockImplementation(() => {})
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.useRealTimers()
    vi.clearAllMocks()
  })

  test('should display employee information correctly', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Senior Developer')
    expect(wrapper.text()).toContain('john.doe@example.com')
    expect(wrapper.text()).toContain('75.000,00 €')
    expect(wrapper.text()).toContain('15 de enero de 2020')
  })

  test('should calculate years of service correctly', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    // From 2020-01-15 to 2024-01-15 = 4 years
    expect(wrapper.text()).toContain('4')
    expect(wrapper.text()).toContain('Años de Servicio')
  })

  test('should calculate vacation days correctly', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    // From 2020-01-15 to 2024-01-15 = 48 months * 2.5 = 120 days
    expect(wrapper.text()).toContain('120')
    expect(wrapper.text()).toContain('Días de Vacación')
  })

  test('should calculate days worked correctly', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    // From 2020-01-15 to 2024-01-15 = 1461 days (including leap year)
    expect(wrapper.text()).toContain('1461')
    expect(wrapper.text()).toContain('Días Trabajados')
  })

  test('should format currency correctly', async () => {
    const testCases = [
      { amount: 50000, currency: 'EUR', expected: '50.000,00 €' },
      { amount: 60000, currency: 'USD', expected: '60.000,00 US$' },
      { amount: 45000, currency: 'GBP', expected: '45.000,00 £' },
      { amount: 5000000, currency: 'JPY', expected: '5.000.000 ¥' }
    ]

    for (const { amount, currency, expected } of testCases) {
      const employee = { ...mockEmployee, salaryAmount: amount, salaryCurrency: currency }
      wrapper = mount(EmployeeDetail, {
        props: {
          employee,
          loading: false
        }
      })

      await nextTick()

      expect(wrapper.text()).toContain(expected)
      wrapper.unmount()
    }
  })

  test('should handle invalid currency gracefully', async () => {
    const employee = { ...mockEmployee, salaryAmount: 50000, salaryCurrency: 'INVALID' }
    wrapper = mount(EmployeeDetail, {
      props: {
        employee,
        loading: false
      }
    })

    await nextTick()

    // Should default to EUR
    expect(wrapper.text()).toContain('50.000,00 €')
  })

  test('should format dates correctly', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.text()).toContain('15 de enero de 2020')
  })

  test('should handle invalid dates gracefully', async () => {
    const employee = { ...mockEmployee, hiredAt: 'invalid-date' }
    wrapper = mount(EmployeeDetail, {
      props: {
        employee,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.text()).toContain('Fecha inválida')
  })

  test('should generate correct initials', async () => {
    const testCases = [
      { firstName: 'John', lastName: 'Doe', expected: 'JD' },
      { firstName: 'María', lastName: 'García', expected: 'MG' },
      { firstName: 'jean-pierre', lastName: 'dupont', expected: 'JD' },
      { firstName: '', lastName: 'Smith', expected: '?S' },
      { firstName: 'John', lastName: '', expected: 'J?' }
    ]

    for (const { firstName, lastName, expected } of testCases) {
      const employee = { ...mockEmployee, firstName, lastName }
      wrapper = mount(EmployeeDetail, {
        props: {
          employee,
          loading: false
        }
      })

      await nextTick()

      const avatar = wrapper.find('[role="img"]')
      expect(avatar.text().trim()).toBe(expected)
      wrapper.unmount()
    }
  })

  test('should emit edit event with employee data', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    const editButton = wrapper.find('button:first-of-type')
    await editButton.trigger('click')

    expect(wrapper.emitted('edit')).toBeTruthy()
    expect(wrapper.emitted('edit')).toHaveLength(1)
    expect(wrapper.emitted('edit')[0][0]).toEqual(mockEmployee)
  })

  test('should emit delete event after confirmation', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    const deleteButton = wrapper.find('button:last-of-type')
    await deleteButton.trigger('click')

    expect(global.confirm).toHaveBeenCalledWith('¿Estás seguro de que quieres eliminar a John Doe?')
    expect(wrapper.emitted('delete')).toBeTruthy()
    expect(wrapper.emitted('delete')).toHaveLength(1)
    expect(wrapper.emitted('delete')[0][0]).toBe(mockEmployee.id)
  })

  test('should not emit delete event when confirmation is cancelled', async () => {
    global.confirm = vi.fn(() => false)

    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    const deleteButton = wrapper.find('button:last-of-type')
    await deleteButton.trigger('click')

    expect(global.confirm).toHaveBeenCalled()
    expect(wrapper.emitted('delete')).toBeFalsy()
  })

  test('should handle missing employee data gracefully', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: null,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.text()).toContain('Cargando información del empleado...')
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  test('should handle empty employee data gracefully', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployeeWithInvalidData,
        loading: false
      }
    })

    await nextTick()

    // Should show fallback values
    expect(wrapper.text()).toContain('Nombre no disponible')
    expect(wrapper.text()).toContain('??') // initials fallback
    expect(wrapper.text()).toContain('No especificado') // salary fallback
  })

  test('should disable buttons when loading', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: true
      }
    })

    await nextTick()

    const editButton = wrapper.find('button:first-of-type')
    const deleteButton = wrapper.find('button:last-of-type')

    expect(editButton.attributes('disabled')).toBeDefined()
    expect(deleteButton.attributes('disabled')).toBeDefined()
    expect(editButton.classes()).toContain('opacity-50')
    expect(editButton.classes()).toContain('cursor-not-allowed')
  })

  test('should calculate next anniversary correctly', async () => {
    // Employee hired on 2020-01-15, current date is 2024-01-15
    // Next anniversary should be 2025-01-15
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.text()).toContain('Próximo Aniversario')
    expect(wrapper.text()).toContain('15 de enero de 2025')
  })

  test('should handle future hired date correctly', async () => {
    const futureEmployee = { ...mockEmployee, hiredAt: '2025-01-01' }
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: futureEmployee,
        loading: false
      }
    })

    await nextTick()

    // Should show 0 for all calculations
    expect(wrapper.text()).toContain('0')
    expect(wrapper.text()).toContain('Años de Servicio')
  })

  test('should limit vacation days to maximum', async () => {
    // Employee hired very long ago to test maximum vacation days
    const oldEmployee = { ...mockEmployee, hiredAt: '1990-01-01' }
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: oldEmployee,
        loading: false
      }
    })

    await nextTick()

    // Should be capped at 365 days
    const vacationText = wrapper.text()
    const vacationMatch = vacationText.match(/(\d+)\s*Días de Vacación/)
    if (vacationMatch) {
      const vacationDays = parseInt(vacationMatch[1])
      expect(vacationDays).toBeLessThanOrEqual(365)
    }
  })

  test('should have proper accessibility attributes', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: mockEmployee,
        loading: false
      }
    })

    await nextTick()

    // Check main accessibility attributes
    expect(wrapper.find('[role="main"]').exists()).toBe(true)
    expect(wrapper.find('[aria-labelledby="employee-name"]').exists()).toBe(true)
    expect(wrapper.find('#employee-name').exists()).toBe(true)

    // Check avatar accessibility
    const avatar = wrapper.find('[role="img"]')
    expect(avatar.attributes('aria-label')).toContain('Avatar de John Doe')

    // Check statistics accessibility
    expect(wrapper.find('[aria-labelledby="years-service-label"]').exists()).toBe(true)
    expect(wrapper.find('[aria-labelledby="vacation-days-label"]').exists()).toBe(true)
    expect(wrapper.find('[aria-labelledby="days-worked-label"]').exists()).toBe(true)
  })

  test('should handle very old dates gracefully', async () => {
    const oldEmployee = { ...mockEmployee, hiredAt: '1800-01-01' }
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: oldEmployee,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.text()).toContain('Fecha fuera de rango')
  })

  test('should handle null salary amount', async () => {
    const employeeWithNullSalary = { ...mockEmployee, salaryAmount: null }
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: employeeWithNullSalary,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.text()).toContain('No especificado')
  })

  test('should format full name correctly with edge cases', async () => {
    const testCases = [
      { firstName: '  John  ', lastName: '  Doe  ', expected: 'John Doe' },
      { firstName: '', lastName: 'Doe', expected: 'Doe' },
      { firstName: 'John', lastName: '', expected: 'John' },
      { firstName: '', lastName: '', expected: 'Nombre no disponible' },
      { firstName: null, lastName: null, expected: 'Nombre no disponible' }
    ]

    for (const { firstName, lastName, expected } of testCases) {
      const employee = { ...mockEmployee, firstName, lastName }
      wrapper = mount(EmployeeDetail, {
        props: {
          employee,
          loading: false
        }
      })

      await nextTick()

      expect(wrapper.find('#employee-name').text()).toBe(expected)
      wrapper.unmount()
    }
  })

  test('should show loading state correctly', async () => {
    wrapper = mount(EmployeeDetail, {
      props: {
        employee: null,
        loading: false
      }
    })

    await nextTick()

    expect(wrapper.find('[role="status"]').exists()).toBe(true)
    expect(wrapper.find('[aria-live="polite"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Cargando información del empleado...')
  })
})