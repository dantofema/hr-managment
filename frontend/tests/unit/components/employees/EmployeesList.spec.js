import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import EmployeesList from '@/components/EmployeesList.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import EmployeeForm from '@/components/employees/EmployeeForm.vue'
import EmployeeDetail from '@/components/employees/EmployeeDetail.vue'
import employeeService from '@/services/employeeService.js'

// Mock the service
vi.mock('@/services/employeeService.js', () => ({
  default: {
    getEmployees: vi.fn(),
    createEmployee: vi.fn(),
    updateEmployee: vi.fn(),
    deleteEmployee: vi.fn(),
    getEmployee: vi.fn()
  }
}))

describe('EmployeesList.vue', () => {
  let wrapper

  const mockEmployees = [
    {
      id: 1,
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      position: 'Developer',
      salaryAmount: 50000,
      salaryCurrency: 'EUR',
      hiredAt: '2023-01-15T00:00:00+00:00'
    },
    {
      id: 2,
      firstName: 'Jane',
      lastName: 'Smith',
      email: 'jane.smith@example.com',
      position: 'Designer',
      salaryAmount: 45000,
      salaryCurrency: 'EUR',
      hiredAt: '2023-02-01T00:00:00+00:00'
    },
    {
      id: 3,
      firstName: 'Bob',
      lastName: 'Johnson',
      email: 'bob.johnson@example.com',
      position: 'Manager',
      salaryAmount: 60000,
      salaryCurrency: 'USD',
      hiredAt: '2022-12-01T00:00:00+00:00'
    }
  ]

  const mockPaginationResponse = {
    'hydra:member': mockEmployees,
    'hydra:totalItems': 3,
    'hydra:view': {
      '@id': '/api/employees?page=1',
      'hydra:first': '/api/employees?page=1',
      'hydra:last': '/api/employees?page=1'
    }
  }

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()
    
    // Default successful response
    employeeService.getEmployees.mockResolvedValue(mockPaginationResponse)
    employeeService.createEmployee.mockResolvedValue(mockEmployees[0])
    employeeService.updateEmployee.mockResolvedValue(mockEmployees[0])
    employeeService.deleteEmployee.mockResolvedValue()
    employeeService.getEmployee.mockResolvedValue(mockEmployees[0])

    // Mock window.confirm
    global.confirm = vi.fn(() => true)
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.clearAllMocks()
  })

  test('should render employees list', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for async data loading

    expect(wrapper.find('h1').text()).toBe('Empleados')
    expect(wrapper.text()).toContain('Gestión de empleados del sistema HR')
    expect(employeeService.getEmployees).toHaveBeenCalled()
  })

  test('should open create modal', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    const createButton = wrapper.find('button:first-of-type')
    await createButton.trigger('click')
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(true)
    const modal = wrapper.findComponent(BaseModal)
    expect(modal.props('isOpen')).toBe(true)
    expect(modal.props('title')).toBe('Crear Nuevo Empleado')
  })

  test('should open edit modal with selected employee', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    // Mock the edit button click
    await wrapper.vm.editEmployee(1)
    await nextTick()

    expect(wrapper.vm.showEditModal).toBe(true)
    expect(wrapper.vm.selectedEmployee).toEqual(mockEmployees[0])
    
    const modals = wrapper.findAllComponents(BaseModal)
    const editModal = modals.find(modal => modal.props('title') === 'Editar Empleado')
    expect(editModal.props('isOpen')).toBe(true)
  })

  test('should open view modal with selected employee', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    // Mock the view button click
    await wrapper.vm.viewEmployee(1)
    await nextTick()

    expect(wrapper.vm.showViewModal).toBe(true)
    expect(wrapper.vm.selectedEmployee).toEqual(mockEmployees[0])
    
    const modals = wrapper.findAllComponents(BaseModal)
    const viewModal = modals.find(modal => modal.props('title') === 'Detalles del Empleado')
    expect(viewModal.props('isOpen')).toBe(true)
  })

  test('should handle pagination correctly', async () => {
    const paginatedResponse = {
      'hydra:member': mockEmployees,
      'hydra:totalItems': 50,
      'hydra:view': {
        '@id': '/api/employees?page=1',
        'hydra:first': '/api/employees?page=1',
        'hydra:last': '/api/employees?page=3',
        'hydra:next': '/api/employees?page=2'
      }
    }

    employeeService.getEmployees.mockResolvedValue(paginatedResponse)

    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    expect(wrapper.vm.pagination.totalItems).toBe(50)
    expect(wrapper.vm.pagination.totalPages).toBe(3)
    expect(wrapper.text()).toContain('Mostrando 1 a 3 de 50 empleados')

    // Test pagination buttons
    const nextButton = wrapper.find('button:last-of-type')
    expect(nextButton.text()).toBe('Siguiente')
    expect(nextButton.attributes('disabled')).toBeUndefined()
  })

  test('should apply filters correctly', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    // Test search filter
    const searchInput = wrapper.find('#search')
    await searchInput.setValue('John')
    await nextTick()

    expect(wrapper.vm.filters.search).toBe('John')

    // Test position filter
    const positionSelect = wrapper.find('#position-filter')
    await positionSelect.setValue('Developer')
    await nextTick()

    expect(wrapper.vm.filters.position).toBe('Developer')

    // Test salary filter
    const salaryInput = wrapper.find('input[type="number"]')
    await salaryInput.setValue(40000)
    await nextTick()

    expect(wrapper.vm.filters.salaryMin).toBe(40000)
  })

  test('should sort by columns', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    // Test sorting by firstName
    const nameHeader = wrapper.find('th:first-of-type')
    await nameHeader.trigger('click')
    await nextTick()

    expect(wrapper.vm.sortBy).toBe('firstName')
    expect(wrapper.vm.sortOrder).toBe('asc')

    // Click again to reverse order
    await nameHeader.trigger('click')
    await nextTick()

    expect(wrapper.vm.sortOrder).toBe('desc')
  })

  test('should refresh after CRUD operations', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for initial data loading

    expect(employeeService.getEmployees).toHaveBeenCalledTimes(1)

    // Test create operation
    await wrapper.vm.handleCreateEmployee({
      firstName: 'New',
      lastName: 'Employee',
      email: 'new@example.com',
      position: 'Tester',
      salaryAmount: 40000,
      salaryCurrency: 'EUR',
      hiredAt: '2024-01-01'
    })

    await nextTick()

    expect(employeeService.createEmployee).toHaveBeenCalled()
    expect(employeeService.getEmployees).toHaveBeenCalledTimes(2) // Refreshed after create
  })

  test('should show loading states', async () => {
    // Mock a delayed response
    employeeService.getEmployees.mockImplementation(() => 
      new Promise(resolve => setTimeout(() => resolve(mockPaginationResponse), 100))
    )

    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    expect(wrapper.find('.animate-spin').exists()).toBe(true)
    expect(wrapper.text()).toContain('Cargando empleados...')
  })

  test('should handle empty state', async () => {
    employeeService.getEmployees.mockResolvedValue({
      'hydra:member': [],
      'hydra:totalItems': 0,
      'hydra:view': {
        '@id': '/api/employees?page=1',
        'hydra:first': '/api/employees?page=1',
        'hydra:last': '/api/employees?page=1'
      }
    })

    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    expect(wrapper.text()).toContain('No hay empleados')
    expect(wrapper.text()).toContain('No se encontraron empleados en el sistema.')
  })

  test('should handle error state', async () => {
    const errorMessage = 'Network error'
    employeeService.getEmployees.mockRejectedValue(new Error(errorMessage))

    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for error handling

    expect(wrapper.text()).toContain('Error al cargar empleados')
    expect(wrapper.text()).toContain(errorMessage)
    expect(wrapper.find('button').text()).toContain('Reintentar')
  })

  test('should retry loading on error', async () => {
    const errorMessage = 'Network error'
    employeeService.getEmployees
      .mockRejectedValueOnce(new Error(errorMessage))
      .mockResolvedValueOnce(mockPaginationResponse)

    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for error

    expect(wrapper.text()).toContain('Error al cargar empleados')

    // Click retry button
    const retryButton = wrapper.find('.bg-red-100')
    await retryButton.trigger('click')
    await nextTick()

    expect(employeeService.getEmployees).toHaveBeenCalledTimes(2)
  })

  test('should clear filters', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    // Set some filters
    await wrapper.find('#search').setValue('John')
    await wrapper.find('#position-filter').setValue('Developer')
    await wrapper.find('input[type="number"]').setValue(40000)
    await nextTick()

    expect(wrapper.vm.filters.search).toBe('John')
    expect(wrapper.vm.filters.position).toBe('Developer')
    expect(wrapper.vm.filters.salaryMin).toBe(40000)

    // Clear filters
    const clearButton = wrapper.find('.bg-gray-100')
    await clearButton.trigger('click')
    await nextTick()

    expect(wrapper.vm.filters.search).toBe('')
    expect(wrapper.vm.filters.position).toBe('')
    expect(wrapper.vm.filters.salaryMin).toBeNull()
  })

  test('should handle delete confirmation', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    await wrapper.vm.confirmDelete(mockEmployees[0])
    await nextTick()

    expect(global.confirm).toHaveBeenCalledWith(
      expect.stringContaining('John Doe')
    )
    expect(employeeService.deleteEmployee).toHaveBeenCalledWith(1)
  })

  test('should not delete when confirmation is cancelled', async () => {
    global.confirm = vi.fn(() => false)

    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    await wrapper.vm.confirmDelete(mockEmployees[0])
    await nextTick()

    expect(global.confirm).toHaveBeenCalled()
    expect(employeeService.deleteEmployee).not.toHaveBeenCalled()
  })

  test('should close modals', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    // Open create modal
    wrapper.vm.showCreateModal = true
    wrapper.vm.selectedEmployee = mockEmployees[0]
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(true)

    // Close modals
    await wrapper.vm.closeModals()
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.showEditModal).toBe(false)
    expect(wrapper.vm.showViewModal).toBe(false)
    expect(wrapper.vm.selectedEmployee).toBeNull()
  })

  test('should handle edit from detail modal', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    wrapper.vm.showViewModal = true
    wrapper.vm.selectedEmployee = mockEmployees[0]
    await nextTick()

    await wrapper.vm.handleEditFromDetail(mockEmployees[0])
    await nextTick()

    expect(wrapper.vm.showViewModal).toBe(false)
    expect(wrapper.vm.showEditModal).toBe(true)
    expect(wrapper.vm.selectedEmployee).toEqual(mockEmployees[0])
  })

  test('should handle delete from detail modal', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    wrapper.vm.showViewModal = true
    wrapper.vm.selectedEmployee = mockEmployees[0]
    await nextTick()

    await wrapper.vm.handleDeleteFromDetail(1)
    await nextTick()

    expect(employeeService.deleteEmployee).toHaveBeenCalledWith(1)
    expect(wrapper.vm.showViewModal).toBe(false)
  })

  test('should format currency correctly', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    const formatted = wrapper.vm.formatCurrency(50000, 'EUR')
    expect(formatted).toContain('50.000')
    expect(formatted).toContain('€')
  })

  test('should format date correctly', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()

    const formatted = wrapper.vm.formatDate('2023-01-15T00:00:00+00:00')
    expect(formatted).toContain('2023')
    expect(formatted).toContain('enero')
  })

  test('should show unique positions in filter', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    const uniquePositions = wrapper.vm.uniquePositions
    expect(uniquePositions).toContain('Developer')
    expect(uniquePositions).toContain('Designer')
    expect(uniquePositions).toContain('Manager')
    expect(uniquePositions).toHaveLength(3)
  })

  test('should handle mobile view', async () => {
    wrapper = mount(EmployeesList, {
      global: {
        components: {
          BaseModal,
          EmployeeForm,
          EmployeeDetail
        }
      }
    })

    await nextTick()
    await nextTick() // Wait for data loading

    // Mobile cards should exist
    expect(wrapper.find('.md\\:hidden').exists()).toBe(true)
  })
})