import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setupServer } from 'msw/node'
import { handlers, resetEmployeesData, setEmployeesData } from '../mocks/api.js'
import EmployeesList from '@/components/EmployeesList.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import EmployeeForm from '@/components/employees/EmployeeForm.vue'
import EmployeeDetail from '@/components/employees/EmployeeDetail.vue'
import { createWrapper, userInput, flushPromises, waitForElement, waitForElementToDisappear } from '../utils/test-utils.js'
import { mockEmployees, createValidEmployeeFormData } from '../fixtures/employees.js'

// Setup MSW server
const server = setupServer(...handlers)

describe('Employee CRUD Flow Integration', () => {
  let wrapper

  beforeAll(() => {
    server.listen({ onUnhandledRequest: 'error' })
  })

  beforeEach(() => {
    // Reset API data before each test
    resetEmployeesData()
    
    // Mock window.confirm
    global.confirm = vi.fn(() => true)
    
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

  test('should complete full CRUD cycle', async () => {
    // 1. Cargar lista inicial
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

    // Verificar que se carga la lista inicial
    expect(wrapper.text()).toContain('Empleados')
    expect(wrapper.find('table').exists()).toBe(true)
    
    // Verificar que se muestran los empleados mock
    await waitForElement(wrapper, 'tbody tr')
    const initialRows = wrapper.findAll('tbody tr')
    expect(initialRows.length).toBe(mockEmployees.length)

    // 2. Crear nuevo empleado
    const newEmployeeData = createValidEmployeeFormData({
      firstName: 'Integration',
      lastName: 'Test',
      email: 'integration.test@example.com',
      position: 'QA Tester'
    })

    // Abrir modal de creación
    const createButton = wrapper.find('button:contains("Nuevo Empleado")')
    await createButton.trigger('click')
    await flushPromises()

    // Verificar que se abre el modal
    expect(wrapper.text()).toContain('Crear Nuevo Empleado')

    // Llenar formulario
    await userInput(wrapper, '#firstName', newEmployeeData.firstName)
    await userInput(wrapper, '#lastName', newEmployeeData.lastName)
    await userInput(wrapper, '#email', newEmployeeData.email)
    await userInput(wrapper, '#position', newEmployeeData.position)
    await userInput(wrapper, '#salaryAmount', newEmployeeData.salaryAmount.toString())
    await wrapper.find('#salaryCurrency').setValue(newEmployeeData.salaryCurrency)
    await userInput(wrapper, '#hiredAt', newEmployeeData.hiredAt)

    await flushPromises()

    // Enviar formulario
    const submitButton = wrapper.find('button[type="submit"]')
    await submitButton.trigger('click')
    await flushPromises()

    // Esperar a que se cierre el modal
    await waitForElementToDisappear(wrapper, '[role="dialog"]')

    // 3. Verificar que aparece en la lista
    await flushPromises()
    const updatedRows = wrapper.findAll('tbody tr')
    expect(updatedRows.length).toBe(mockEmployees.length + 1)
    expect(wrapper.text()).toContain('Integration Test')
    expect(wrapper.text()).toContain('integration.test@example.com')

    // 4. Editar empleado
    // Buscar el empleado recién creado y hacer clic en editar
    const editButtons = wrapper.findAll('button[title="Editar empleado"]')
    const newEmployeeEditButton = editButtons[0] // Debería ser el primero (más reciente)
    await newEmployeeEditButton.trigger('click')
    await flushPromises()

    // Verificar que se abre el modal de edición
    expect(wrapper.text()).toContain('Editar Empleado')

    // Modificar datos
    const updatedFirstName = 'Updated Integration'
    await userInput(wrapper, '#firstName', updatedFirstName)
    await flushPromises()

    // Enviar formulario de edición
    const updateSubmitButton = wrapper.find('button[type="submit"]')
    await updateSubmitButton.trigger('click')
    await flushPromises()

    // Esperar a que se cierre el modal
    await waitForElementToDisappear(wrapper, '[role="dialog"]')

    // 5. Verificar cambios
    await flushPromises()
    expect(wrapper.text()).toContain('Updated Integration Test')
    expect(wrapper.text()).not.toContain('Integration Test') // El nombre original no debería estar

    // 6. Ver detalles del empleado
    const viewButtons = wrapper.findAll('button[title="Ver detalles"]')
    const newEmployeeViewButton = viewButtons[0]
    await newEmployeeViewButton.trigger('click')
    await flushPromises()

    // Verificar que se abre el modal de detalles
    expect(wrapper.text()).toContain('Detalles del Empleado')
    expect(wrapper.text()).toContain('Updated Integration Test')
    expect(wrapper.text()).toContain('QA Tester')

    // Cerrar modal de detalles
    const closeButton = wrapper.find('[aria-label="Cerrar modal"]')
    await closeButton.trigger('click')
    await flushPromises()

    // 7. Eliminar empleado
    const deleteButtons = wrapper.findAll('button[title="Eliminar empleado"]')
    const newEmployeeDeleteButton = deleteButtons[0]
    await newEmployeeDeleteButton.trigger('click')
    await flushPromises()

    // Verificar que se mostró la confirmación
    expect(global.confirm).toHaveBeenCalledWith(
      expect.stringContaining('¿Estás seguro de que deseas eliminar')
    )

    // 8. Verificar que desaparece de la lista
    await flushPromises()
    const finalRows = wrapper.findAll('tbody tr')
    expect(finalRows.length).toBe(mockEmployees.length)
    expect(wrapper.text()).not.toContain('Updated Integration Test')
    expect(wrapper.text()).not.toContain('integration.test@example.com')
  })

  test('should handle validation errors during creation', async () => {
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

    // Abrir modal de creación
    const createButton = wrapper.find('button:contains("Nuevo Empleado")')
    await createButton.trigger('click')
    await flushPromises()

    // Intentar enviar formulario vacío
    const submitButton = wrapper.find('button[type="submit"]')
    await submitButton.trigger('click')
    await flushPromises()

    // Verificar que aparecen errores de validación
    expect(wrapper.text()).toContain('obligatorio')
    
    // Verificar que el modal sigue abierto
    expect(wrapper.text()).toContain('Crear Nuevo Empleado')

    // Llenar solo algunos campos
    await userInput(wrapper, '#firstName', 'Test')
    await userInput(wrapper, '#email', 'invalid-email')
    await flushPromises()

    // Intentar enviar de nuevo
    await submitButton.trigger('click')
    await flushPromises()

    // Verificar errores específicos
    expect(wrapper.text()).toContain('email válido')
    expect(wrapper.text()).toContain('obligatorio')
  })

  test('should handle API errors gracefully', async () => {
    // Configurar el servidor para devolver errores
    server.use(
      rest.post('/api/employees', (req, res, ctx) => {
        return res(ctx.status(500), ctx.json({ error: 'Server error' }))
      })
    )

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

    // Intentar crear empleado
    const createButton = wrapper.find('button:contains("Nuevo Empleado")')
    await createButton.trigger('click')
    await flushPromises()

    // Llenar formulario con datos válidos
    const validData = createValidEmployeeFormData()
    await userInput(wrapper, '#firstName', validData.firstName)
    await userInput(wrapper, '#lastName', validData.lastName)
    await userInput(wrapper, '#email', validData.email)
    await userInput(wrapper, '#position', validData.position)
    await userInput(wrapper, '#salaryAmount', validData.salaryAmount.toString())
    await wrapper.find('#salaryCurrency').setValue(validData.salaryCurrency)
    await userInput(wrapper, '#hiredAt', validData.hiredAt)

    await flushPromises()

    // Enviar formulario
    const submitButton = wrapper.find('button[type="submit"]')
    await submitButton.trigger('click')
    await flushPromises()

    // Verificar que se muestra el error
    expect(wrapper.text()).toContain('Error')
    
    // Verificar que el modal sigue abierto (no se cerró por el error)
    expect(wrapper.text()).toContain('Crear Nuevo Empleado')
  })

  test('should maintain state consistency', async () => {
    // Configurar datos iniciales específicos
    const initialEmployees = [
      {
        id: 1,
        firstName: 'State',
        lastName: 'Test',
        email: 'state.test@example.com',
        position: 'Tester',
        salaryAmount: 40000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-01T00:00:00+00:00'
      }
    ]
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
    expect(wrapper.findAll('tbody tr')).toHaveLength(1)
    expect(wrapper.text()).toContain('State Test')

    // Crear nuevo empleado
    const createButton = wrapper.find('button:contains("Nuevo Empleado")')
    await createButton.trigger('click')
    await flushPromises()

    const newEmployeeData = createValidEmployeeFormData({
      firstName: 'Consistency',
      lastName: 'Check',
      email: 'consistency.check@example.com'
    })

    // Llenar y enviar formulario
    await userInput(wrapper, '#firstName', newEmployeeData.firstName)
    await userInput(wrapper, '#lastName', newEmployeeData.lastName)
    await userInput(wrapper, '#email', newEmployeeData.email)
    await userInput(wrapper, '#position', newEmployeeData.position)
    await userInput(wrapper, '#salaryAmount', newEmployeeData.salaryAmount.toString())
    await wrapper.find('#salaryCurrency').setValue(newEmployeeData.salaryCurrency)
    await userInput(wrapper, '#hiredAt', newEmployeeData.hiredAt)

    await flushPromises()

    const submitButton = wrapper.find('button[type="submit"]')
    await submitButton.trigger('click')
    await flushPromises()

    // Esperar a que se actualice la lista
    await waitForElementToDisappear(wrapper, '[role="dialog"]')
    await flushPromises()

    // Verificar que el estado se mantiene consistente
    const finalRows = wrapper.findAll('tbody tr')
    expect(finalRows).toHaveLength(2)
    expect(wrapper.text()).toContain('State Test') // Empleado original
    expect(wrapper.text()).toContain('Consistency Check') // Nuevo empleado

    // Verificar información de paginación
    expect(wrapper.text()).toContain('de 2 empleados')
  })

  test('should handle concurrent operations', async () => {
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

    // Simular múltiples operaciones rápidas
    const createButton = wrapper.find('button:contains("Nuevo Empleado")')
    
    // Abrir y cerrar modal rápidamente
    await createButton.trigger('click')
    await flushPromises()
    
    const closeButton = wrapper.find('[aria-label="Cerrar modal"]')
    await closeButton.trigger('click')
    await flushPromises()

    // Abrir de nuevo
    await createButton.trigger('click')
    await flushPromises()

    // Verificar que el estado es consistente
    expect(wrapper.text()).toContain('Crear Nuevo Empleado')
    
    // Cerrar y verificar que se cierra correctamente
    const cancelButton = wrapper.find('button:contains("Cancelar")')
    await cancelButton.trigger('click')
    await flushPromises()

    expect(wrapper.text()).not.toContain('Crear Nuevo Empleado')
  })

  test('should preserve filters during CRUD operations', async () => {
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

    // Aplicar filtro
    const searchInput = wrapper.find('#search')
    await searchInput.setValue('John')
    await searchInput.trigger('input')
    await flushPromises()

    // Verificar que el filtro se aplicó
    const filteredRows = wrapper.findAll('tbody tr')
    expect(filteredRows.length).toBeLessThan(mockEmployees.length)

    // Crear nuevo empleado que coincida con el filtro
    const createButton = wrapper.find('button:contains("Nuevo Empleado")')
    await createButton.trigger('click')
    await flushPromises()

    const newEmployeeData = createValidEmployeeFormData({
      firstName: 'Johnny',
      lastName: 'Filter',
      email: 'johnny.filter@example.com'
    })

    // Llenar formulario
    await userInput(wrapper, '#firstName', newEmployeeData.firstName)
    await userInput(wrapper, '#lastName', newEmployeeData.lastName)
    await userInput(wrapper, '#email', newEmployeeData.email)
    await userInput(wrapper, '#position', newEmployeeData.position)
    await userInput(wrapper, '#salaryAmount', newEmployeeData.salaryAmount.toString())
    await wrapper.find('#salaryCurrency').setValue(newEmployeeData.salaryCurrency)
    await userInput(wrapper, '#hiredAt', newEmployeeData.hiredAt)

    await flushPromises()

    // Enviar formulario
    const submitButton = wrapper.find('button[type="submit"]')
    await submitButton.trigger('click')
    await flushPromises()

    // Esperar a que se cierre el modal
    await waitForElementToDisappear(wrapper, '[role="dialog"]')
    await flushPromises()

    // Verificar que el filtro sigue activo
    expect(wrapper.find('#search').element.value).toBe('John')
    
    // Verificar que el nuevo empleado aparece (porque coincide con el filtro)
    expect(wrapper.text()).toContain('Johnny Filter')
  })
})