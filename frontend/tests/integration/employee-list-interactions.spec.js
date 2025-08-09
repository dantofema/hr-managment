import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setupServer } from 'msw/node'
import { handlers, resetEmployeesData, setEmployeesData } from '../mocks/api.js'
import EmployeesList from '@/components/EmployeesList.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import EmployeeForm from '@/components/employees/EmployeeForm.vue'
import EmployeeDetail from '@/components/employees/EmployeeDetail.vue'
import { createWrapper, userInput, flushPromises, waitForElement } from '../utils/test-utils.js'
import { createMockEmployees } from '../fixtures/employees.js'

// Setup MSW server
const server = setupServer(...handlers)

describe('Employee List Interactions Integration', () => {
  let wrapper

  beforeAll(() => {
    server.listen({ onUnhandledRequest: 'error' })
  })

  beforeEach(() => {
    // Reset API data before each test
    resetEmployeesData()
    
    // Mock console methods
    vi.spyOn(console, 'error').mockImplementation(() => {})
    vi.spyOn(console, 'warn').mockImplementation(() => {})
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.clearAllMocks()
    vi.restoreAllMocks()
    server.resetHandlers()
  })

  afterAll(() => {
    server.close()
  })

  test('should filter employees by position', async () => {
    // Configurar datos de prueba con diferentes posiciones
    const testEmployees = [
      {
        id: 1,
        firstName: 'John',
        lastName: 'Developer',
        email: 'john.dev@example.com',
        position: 'Developer',
        salaryAmount: 50000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-01T00:00:00+00:00'
      },
      {
        id: 2,
        firstName: 'Jane',
        lastName: 'Designer',
        email: 'jane.design@example.com',
        position: 'Designer',
        salaryAmount: 45000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-02-01T00:00:00+00:00'
      },
      {
        id: 3,
        firstName: 'Bob',
        lastName: 'Manager',
        email: 'bob.manager@example.com',
        position: 'Manager',
        salaryAmount: 60000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-03-01T00:00:00+00:00'
      }
    ]
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Verificar que se muestran todos los empleados inicialmente
    expect(wrapper.findAll('tbody tr')).toHaveLength(3)
    expect(wrapper.text()).toContain('John Developer')
    expect(wrapper.text()).toContain('Jane Designer')
    expect(wrapper.text()).toContain('Bob Manager')

    // Aplicar filtro por posición
    const positionSelect = wrapper.find('#position-filter')
    await positionSelect.setValue('Developer')
    await positionSelect.trigger('change')
    await flushPromises()

    // Verificar que solo se muestra el desarrollador
    const filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows).toHaveLength(1)
    expect(wrapper.text()).toContain('John Developer')
    expect(wrapper.text()).not.toContain('Jane Designer')
    expect(wrapper.text()).not.toContain('Bob Manager')

    // Cambiar a otro filtro
    await positionSelect.setValue('Designer')
    await positionSelect.trigger('change')
    await flushPromises()

    // Verificar que solo se muestra el diseñador
    const designerRows = wrapper.findAll('tbody tr')
    expect(designerRows).toHaveLength(1)
    expect(wrapper.text()).toContain('Jane Designer')
    expect(wrapper.text()).not.toContain('John Developer')
    expect(wrapper.text()).not.toContain('Bob Manager')

    // Limpiar filtro
    await positionSelect.setValue('')
    await positionSelect.trigger('change')
    await flushPromises()

    // Verificar que se muestran todos los empleados de nuevo
    expect(wrapper.findAll('tbody tr')).toHaveLength(3)
  })

  test('should filter employees by salary range', async () => {
    const testEmployees = createMockEmployees(5).map((emp, index) => ({
      ...emp,
      salaryAmount: 30000 + (index * 10000) // 30k, 40k, 50k, 60k, 70k
    }))
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Verificar estado inicial
    expect(wrapper.findAll('tbody tr')).toHaveLength(5)

    // Aplicar filtro de salario mínimo
    const salaryMinInput = wrapper.find('input[type="number"]')
    await salaryMinInput.setValue('50000')
    await salaryMinInput.trigger('input')
    
    // Esperar el debounce
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que solo se muestran empleados con salario >= 50k
    const filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows.length).toBeLessThanOrEqual(3) // Empleados con 50k, 60k, 70k

    // Verificar que los salarios mostrados son correctos
    const salaryTexts = wrapper.findAll('tbody tr').map(row => row.text())
    salaryTexts.forEach(text => {
      const salaryMatch = text.match(/(\d+)\.000,00/)
      if (salaryMatch) {
        const salary = parseInt(salaryMatch[1]) * 1000
        expect(salary).toBeGreaterThanOrEqual(50000)
      }
    })
  })

  test('should search employees by name/email', async () => {
    const testEmployees = [
      {
        id: 1,
        firstName: 'Alice',
        lastName: 'Johnson',
        email: 'alice.johnson@example.com',
        position: 'Developer',
        salaryAmount: 50000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-01T00:00:00+00:00'
      },
      {
        id: 2,
        firstName: 'Bob',
        lastName: 'Smith',
        email: 'bob.smith@company.com',
        position: 'Designer',
        salaryAmount: 45000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-02-01T00:00:00+00:00'
      },
      {
        id: 3,
        firstName: 'Charlie',
        lastName: 'Brown',
        email: 'charlie@example.org',
        position: 'Manager',
        salaryAmount: 60000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-03-01T00:00:00+00:00'
      }
    ]
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Buscar por nombre
    const searchInput = wrapper.find('#search')
    await searchInput.setValue('Alice')
    await searchInput.trigger('input')
    
    // Esperar el debounce
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que solo se muestra Alice
    let filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows).toHaveLength(1)
    expect(wrapper.text()).toContain('Alice Johnson')
    expect(wrapper.text()).not.toContain('Bob Smith')

    // Buscar por email
    await searchInput.setValue('company.com')
    await searchInput.trigger('input')
    
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que solo se muestra Bob
    filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows).toHaveLength(1)
    expect(wrapper.text()).toContain('Bob Smith')
    expect(wrapper.text()).not.toContain('Alice Johnson')

    // Buscar por posición
    await searchInput.setValue('Manager')
    await searchInput.trigger('input')
    
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que solo se muestra Charlie
    filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows).toHaveLength(1)
    expect(wrapper.text()).toContain('Charlie Brown')

    // Búsqueda que no coincide
    await searchInput.setValue('NoMatch')
    await searchInput.trigger('input')
    
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que no se muestra ningún empleado
    filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows).toHaveLength(0)
  })

  test('should sort employees by different columns', async () => {
    const testEmployees = [
      {
        id: 1,
        firstName: 'Charlie',
        lastName: 'Alpha',
        email: 'charlie@example.com',
        position: 'Developer',
        salaryAmount: 30000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-03-01T00:00:00+00:00'
      },
      {
        id: 2,
        firstName: 'Alice',
        lastName: 'Beta',
        email: 'alice@example.com',
        position: 'Designer',
        salaryAmount: 50000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-01T00:00:00+00:00'
      },
      {
        id: 3,
        firstName: 'Bob',
        lastName: 'Gamma',
        email: 'bob@example.com',
        position: 'Manager',
        salaryAmount: 40000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-02-01T00:00:00+00:00'
      }
    ]
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Verificar orden inicial (por firstName ascendente por defecto)
    let rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('Alice Beta')
    expect(rows[1].text()).toContain('Bob Gamma')
    expect(rows[2].text()).toContain('Charlie Alpha')

    // Ordenar por email
    const emailHeader = wrapper.find('th:contains("Email")')
    await emailHeader.trigger('click')
    await flushPromises()

    // Verificar orden por email ascendente
    rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('alice@example.com')
    expect(rows[1].text()).toContain('bob@example.com')
    expect(rows[2].text()).toContain('charlie@example.com')

    // Hacer clic de nuevo para orden descendente
    await emailHeader.trigger('click')
    await flushPromises()

    // Verificar orden por email descendente
    rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('charlie@example.com')
    expect(rows[1].text()).toContain('bob@example.com')
    expect(rows[2].text()).toContain('alice@example.com')

    // Ordenar por salario
    const salaryHeader = wrapper.find('th:contains("Salario")')
    await salaryHeader.trigger('click')
    await flushPromises()

    // Verificar orden por salario ascendente
    rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('30.000,00') // Charlie
    expect(rows[1].text()).toContain('40.000,00') // Bob
    expect(rows[2].text()).toContain('50.000,00') // Alice
  })

  test('should navigate through pages', async () => {
    // Crear muchos empleados para probar paginación
    const manyEmployees = createMockEmployees(50)
    setEmployeesData(manyEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Verificar información de paginación
    expect(wrapper.text()).toContain('de 50 empleados')
    expect(wrapper.text()).toContain('Mostrando 1 a')

    // Verificar botones de paginación
    const nextButton = wrapper.find('button:contains("Siguiente")')
    const prevButton = wrapper.find('button:contains("Anterior")')
    
    expect(prevButton.attributes('disabled')).toBeDefined() // Debería estar deshabilitado en la primera página
    expect(nextButton.attributes('disabled')).toBeUndefined() // Debería estar habilitado

    // Ir a la siguiente página
    await nextButton.trigger('click')
    await flushPromises()

    // Verificar que cambió la página
    expect(wrapper.text()).toContain('Mostrando 21 a') // Segunda página
    expect(prevButton.attributes('disabled')).toBeUndefined() // Ahora debería estar habilitado

    // Volver a la página anterior
    await prevButton.trigger('click')
    await flushPromises()

    // Verificar que volvió a la primera página
    expect(wrapper.text()).toContain('Mostrando 1 a')
    expect(prevButton.attributes('disabled')).toBeDefined()
  })

  test('should refresh data after operations', async () => {
    const initialEmployees = createMockEmployees(3)
    setEmployeesData(initialEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Verificar estado inicial
    expect(wrapper.findAll('tbody tr')).toHaveLength(3)

    // Simular que se agregó un empleado externamente
    const updatedEmployees = [...initialEmployees, {
      id: 99,
      firstName: 'External',
      lastName: 'Addition',
      email: 'external@example.com',
      position: 'Tester',
      salaryAmount: 35000,
      salaryCurrency: 'EUR',
      hiredAt: '2023-04-01T00:00:00+00:00'
    }]
    setEmployeesData(updatedEmployees)

    // Refrescar manualmente (simular F5 o refresh button)
    await wrapper.vm.fetchEmployees()
    await flushPromises()

    // Verificar que se actualizó la lista
    expect(wrapper.findAll('tbody tr')).toHaveLength(4)
    expect(wrapper.text()).toContain('External Addition')
  })

  test('should combine multiple filters', async () => {
    const testEmployees = [
      {
        id: 1,
        firstName: 'John',
        lastName: 'Developer',
        email: 'john.dev@example.com',
        position: 'Developer',
        salaryAmount: 60000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-01T00:00:00+00:00'
      },
      {
        id: 2,
        firstName: 'Jane',
        lastName: 'Developer',
        email: 'jane.dev@example.com',
        position: 'Developer',
        salaryAmount: 40000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-02-01T00:00:00+00:00'
      },
      {
        id: 3,
        firstName: 'Bob',
        lastName: 'Designer',
        email: 'bob.design@example.com',
        position: 'Designer',
        salaryAmount: 55000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-03-01T00:00:00+00:00'
      }
    ]
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Aplicar filtro por posición
    const positionSelect = wrapper.find('#position-filter')
    await positionSelect.setValue('Developer')
    await positionSelect.trigger('change')
    await flushPromises()

    // Verificar que se muestran ambos desarrolladores
    expect(wrapper.findAll('tbody tr')).toHaveLength(2)

    // Aplicar filtro adicional por salario mínimo
    const salaryMinInput = wrapper.find('input[type="number"]')
    await salaryMinInput.setValue('50000')
    await salaryMinInput.trigger('input')
    
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que solo se muestra John (Developer con salario >= 50k)
    const filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows).toHaveLength(1)
    expect(wrapper.text()).toContain('John Developer')
    expect(wrapper.text()).not.toContain('Jane Developer')

    // Aplicar filtro adicional por búsqueda
    const searchInput = wrapper.find('#search')
    await searchInput.setValue('jane')
    await searchInput.trigger('input')
    
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que no se muestra ningún empleado (Jane no cumple el filtro de salario)
    expect(wrapper.findAll('tbody tr')).toHaveLength(0)

    // Limpiar filtro de salario
    await salaryMinInput.setValue('')
    await salaryMinInput.trigger('input')
    
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Ahora debería mostrar Jane (Developer que coincide con "jane")
    expect(wrapper.findAll('tbody tr')).toHaveLength(1)
    expect(wrapper.text()).toContain('Jane Developer')
  })

  test('should clear all filters', async () => {
    const testEmployees = createMockEmployees(5)
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Aplicar varios filtros
    const searchInput = wrapper.find('#search')
    await searchInput.setValue('Employee1')
    await searchInput.trigger('input')

    const positionSelect = wrapper.find('#position-filter')
    await positionSelect.setValue('Developer')
    await positionSelect.trigger('change')

    const salaryMinInput = wrapper.find('input[type="number"]')
    await salaryMinInput.setValue('45000')
    await salaryMinInput.trigger('input')

    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que los filtros están aplicados
    expect(searchInput.element.value).toBe('Employee1')
    expect(positionSelect.element.value).toBe('Developer')
    expect(salaryMinInput.element.value).toBe('45000')

    // Limpiar todos los filtros
    const clearButton = wrapper.find('button:contains("Limpiar Filtros")')
    await clearButton.trigger('click')
    await flushPromises()

    // Verificar que todos los filtros se limpiaron
    expect(searchInput.element.value).toBe('')
    expect(positionSelect.element.value).toBe('')
    expect(salaryMinInput.element.value).toBe('')

    // Verificar que se muestran todos los empleados
    expect(wrapper.findAll('tbody tr')).toHaveLength(5)
  })

  test('should handle empty search results', async () => {
    const testEmployees = createMockEmployees(3)
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Buscar algo que no existe
    const searchInput = wrapper.find('#search')
    await searchInput.setValue('NonExistentEmployee')
    await searchInput.trigger('input')
    
    await new Promise(resolve => setTimeout(resolve, 350))
    await flushPromises()

    // Verificar que no se muestran empleados
    expect(wrapper.findAll('tbody tr')).toHaveLength(0)
    
    // Verificar que no se muestra el estado vacío general (porque hay empleados, solo están filtrados)
    expect(wrapper.text()).not.toContain('No hay empleados')
  })

  test('should maintain sort order when filtering', async () => {
    const testEmployees = [
      {
        id: 1,
        firstName: 'Alice',
        lastName: 'Developer',
        email: 'alice@example.com',
        position: 'Developer',
        salaryAmount: 60000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-01T00:00:00+00:00'
      },
      {
        id: 2,
        firstName: 'Bob',
        lastName: 'Developer',
        email: 'bob@example.com',
        position: 'Developer',
        salaryAmount: 40000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-02-01T00:00:00+00:00'
      },
      {
        id: 3,
        firstName: 'Charlie',
        lastName: 'Designer',
        email: 'charlie@example.com',
        position: 'Designer',
        salaryAmount: 50000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-03-01T00:00:00+00:00'
      }
    ]
    setEmployeesData(testEmployees)

    wrapper = createWrapper(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await flushPromises()

    // Ordenar por salario descendente
    const salaryHeader = wrapper.find('th:contains("Salario")')
    await salaryHeader.trigger('click') // Ascendente
    await salaryHeader.trigger('click') // Descendente
    await flushPromises()

    // Verificar orden inicial por salario descendente
    let rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('60.000,00') // Alice
    expect(rows[1].text()).toContain('50.000,00') // Charlie
    expect(rows[2].text()).toContain('40.000,00') // Bob

    // Aplicar filtro por posición
    const positionSelect = wrapper.find('#position-filter')
    await positionSelect.setValue('Developer')
    await positionSelect.trigger('change')
    await flushPromises()

    // Verificar que el orden se mantiene para los desarrolladores filtrados
    rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(2)
    expect(rows[0].text()).toContain('Alice') // 60k primero
    expect(rows[1].text()).toContain('Bob')   // 40k segundo
  })
})