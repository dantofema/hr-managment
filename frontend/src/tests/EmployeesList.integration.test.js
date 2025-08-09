/**
 * Integration Tests for EmployeesList Component
 * Tests the complete CRUD flows with mocked API responses
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import EmployeesList from '@/components/EmployeesList.vue'
import employeeService from '@/services/employeeService.js'

// Mock the employee service
vi.mock('@/services/employeeService.js', () => ({
  default: {
    fetchEmployees: vi.fn(),
    getEmployee: vi.fn(),
    createEmployee: vi.fn(),
    updateEmployee: vi.fn(),
    deleteEmployee: vi.fn()
  }
}))

// Mock components to avoid dependency issues in tests
vi.mock('@/components/ui/BaseModal.vue', () => ({
  default: {
    name: 'BaseModal',
    template: '<div class="mock-modal"><slot /></div>',
    props: ['isOpen', 'title', 'size'],
    emits: ['close']
  }
}))

vi.mock('@/components/employees/EmployeeForm.vue', () => ({
  default: {
    name: 'EmployeeForm',
    template: '<div class="mock-form">Employee Form</div>',
    props: ['mode', 'employee', 'loading'],
    emits: ['submit', 'cancel']
  }
}))

describe('EmployeesList Integration Tests', () => {
  let wrapper
  
  const mockEmployees = [
    {
      id: 1,
      firstName: 'Juan',
      lastName: 'Pérez',
      fullName: 'Juan Pérez',
      email: 'juan.perez@example.com',
      position: 'Desarrollador Frontend',
      salaryAmount: 45000,
      salaryCurrency: 'EUR',
      hiredAt: '2023-01-15T00:00:00Z'
    },
    {
      id: 2,
      firstName: 'María',
      lastName: 'García',
      fullName: 'María García',
      email: 'maria.garcia@example.com',
      position: 'Diseñadora UX',
      salaryAmount: 42000,
      salaryCurrency: 'EUR',
      hiredAt: '2023-03-20T00:00:00Z'
    }
  ]

  const mockPagination = {
    currentPage: 1,
    totalItems: 2,
    totalPages: 1
  }

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()
    
    // Setup default mock responses
    employeeService.fetchEmployees.mockResolvedValue({
      data: mockEmployees,
      pagination: mockPagination
    })
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  describe('READ Operations', () => {
    it('should load and display employees on mount', async () => {
      wrapper = mount(EmployeesList)
      
      // Wait for component to mount and fetch data
      await nextTick()
      await new Promise(resolve => setTimeout(resolve, 0))
      
      expect(employeeService.fetchEmployees).toHaveBeenCalledWith(1)
      expect(wrapper.text()).toContain('Juan Pérez')
      expect(wrapper.text()).toContain('María García')
    })

    it('should handle empty employee list', async () => {
      employeeService.fetchEmployees.mockResolvedValue({
        data: [],
        pagination: { currentPage: 1, totalItems: 0, totalPages: 0 }
      })
      
      wrapper = mount(EmployeesList)
      await nextTick()
      await new Promise(resolve => setTimeout(resolve, 0))
      
      expect(wrapper.text()).toContain('No hay empleados')
    })

    it('should handle API errors gracefully', async () => {
      employeeService.fetchEmployees.mockRejectedValue(new Error('API Error'))
      
      wrapper = mount(EmployeesList)
      await nextTick()
      await new Promise(resolve => setTimeout(resolve, 0))
      
      expect(wrapper.text()).toContain('Error al cargar empleados')
    })
  })

  describe('CREATE Operations', () => {
    it('should open create modal when "Nuevo Empleado" button is clicked', async () => {
      wrapper = mount(EmployeesList)
      await nextTick()
      
      const createButton = wrapper.find('button:contains("Nuevo Empleado")')
      expect(createButton.exists()).toBe(true)
      
      await createButton.trigger('click')
      await nextTick()
      
      expect(wrapper.vm.showCreateModal).toBe(true)
    })

    it('should create new employee and refresh list', async () => {
      const newEmployee = {
        firstName: 'Carlos',
        lastName: 'López',
        email: 'carlos.lopez@example.com',
        position: 'Backend Developer',
        salaryAmount: 48000,
        salaryCurrency: 'EUR',
        hiredAt: '2024-01-01'
      }

      employeeService.createEmployee.mockResolvedValue({
        id: 3,
        ...newEmployee,
        fullName: 'Carlos López'
      })

      wrapper = mount(EmployeesList)
      await nextTick()
      
      // Simulate form submission
      await wrapper.vm.handleCreateEmployee(newEmployee)
      await nextTick()
      
      expect(employeeService.createEmployee).toHaveBeenCalledWith(newEmployee)
      expect(employeeService.fetchEmployees).toHaveBeenCalledTimes(2) // Initial load + refresh
      expect(wrapper.vm.showCreateModal).toBe(false)
    })

    it('should handle create errors', async () => {
      employeeService.createEmployee.mockRejectedValue(new Error('Validation Error'))
      
      wrapper = mount(EmployeesList)
      await nextTick()
      
      await wrapper.vm.handleCreateEmployee({})
      await nextTick()
      
      expect(wrapper.vm.error).toContain('Error al crear el empleado')
    })
  })

  describe('UPDATE Operations', () => {
    it('should open edit modal with employee data', async () => {
      const employee = mockEmployees[0]
      employeeService.getEmployee.mockResolvedValue(employee)
      
      wrapper = mount(EmployeesList)
      await nextTick()
      
      await wrapper.vm.openEditModal(employee)
      await nextTick()
      
      expect(employeeService.getEmployee).toHaveBeenCalledWith(employee.id)
      expect(wrapper.vm.showEditModal).toBe(true)
      expect(wrapper.vm.selectedEmployee).toEqual(employee)
    })

    it('should update employee and refresh list', async () => {
      const updatedData = {
        firstName: 'Juan Carlos',
        lastName: 'Pérez',
        email: 'juan.carlos.perez@example.com',
        position: 'Senior Frontend Developer',
        salaryAmount: 50000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-15'
      }

      wrapper = mount(EmployeesList)
      wrapper.vm.selectedEmployee = mockEmployees[0]
      await nextTick()
      
      await wrapper.vm.handleUpdateEmployee(updatedData)
      await nextTick()
      
      expect(employeeService.updateEmployee).toHaveBeenCalledWith(1, updatedData)
      expect(employeeService.fetchEmployees).toHaveBeenCalledTimes(2)
      expect(wrapper.vm.showEditModal).toBe(false)
    })
  })

  describe('DELETE Operations', () => {
    it('should delete employee after confirmation', async () => {
      // Mock window.confirm
      const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true)
      
      wrapper = mount(EmployeesList)
      await nextTick()
      
      await wrapper.vm.confirmDelete(mockEmployees[0])
      await nextTick()
      
      expect(confirmSpy).toHaveBeenCalledWith('¿Estás seguro de que deseas eliminar al empleado Juan Pérez?')
      expect(employeeService.deleteEmployee).toHaveBeenCalledWith(1)
      expect(employeeService.fetchEmployees).toHaveBeenCalledTimes(2)
      
      confirmSpy.mockRestore()
    })

    it('should not delete if user cancels confirmation', async () => {
      const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(false)
      
      wrapper = mount(EmployeesList)
      await nextTick()
      
      await wrapper.vm.confirmDelete(mockEmployees[0])
      await nextTick()
      
      expect(employeeService.deleteEmployee).not.toHaveBeenCalled()
      
      confirmSpy.mockRestore()
    })
  })

  describe('VIEW Operations', () => {
    it('should open view modal with employee details', async () => {
      const employee = mockEmployees[0]
      employeeService.getEmployee.mockResolvedValue(employee)
      
      wrapper = mount(EmployeesList)
      await nextTick()
      
      await wrapper.vm.openViewModal(employee)
      await nextTick()
      
      expect(employeeService.getEmployee).toHaveBeenCalledWith(employee.id)
      expect(wrapper.vm.showViewModal).toBe(true)
      expect(wrapper.vm.selectedEmployee).toEqual(employee)
    })
  })

  describe('Filtering and Sorting', () => {
    it('should filter employees by search term', async () => {
      wrapper = mount(EmployeesList)
      await nextTick()
      await new Promise(resolve => setTimeout(resolve, 0))
      
      // Set search filter
      wrapper.vm.filters.search = 'juan'
      await nextTick()
      
      const filteredEmployees = wrapper.vm.employees
      expect(filteredEmployees).toHaveLength(1)
      expect(filteredEmployees[0].firstName).toBe('Juan')
    })

    it('should filter employees by position', async () => {
      wrapper = mount(EmployeesList)
      await nextTick()
      await new Promise(resolve => setTimeout(resolve, 0))
      
      // Set position filter
      wrapper.vm.filters.position = 'Desarrollador Frontend'
      await nextTick()
      
      const filteredEmployees = wrapper.vm.employees
      expect(filteredEmployees).toHaveLength(1)
      expect(filteredEmployees[0].position).toBe('Desarrollador Frontend')
    })

    it('should sort employees by name', async () => {
      wrapper = mount(EmployeesList)
      await nextTick()
      await new Promise(resolve => setTimeout(resolve, 0))
      
      // Sort by firstName descending
      wrapper.vm.sortEmployees('firstName')
      wrapper.vm.sortEmployees('firstName') // Second click for desc
      await nextTick()
      
      const sortedEmployees = wrapper.vm.employees
      expect(sortedEmployees[0].firstName).toBe('María')
      expect(sortedEmployees[1].firstName).toBe('Juan')
    })

    it('should clear all filters', async () => {
      wrapper = mount(EmployeesList)
      await nextTick()
      
      // Set some filters
      wrapper.vm.filters.search = 'test'
      wrapper.vm.filters.position = 'Developer'
      wrapper.vm.filters.salaryMin = 40000
      
      // Clear filters
      wrapper.vm.clearFilters()
      await nextTick()
      
      expect(wrapper.vm.filters.search).toBe('')
      expect(wrapper.vm.filters.position).toBe('')
      expect(wrapper.vm.filters.salaryMin).toBe(null)
    })
  })

  describe('Pagination', () => {
    it('should change page when pagination is used', async () => {
      wrapper = mount(EmployeesList)
      await nextTick()
      
      await wrapper.vm.changePage(2)
      await nextTick()
      
      expect(employeeService.fetchEmployees).toHaveBeenCalledWith(2)
    })

    it('should not change to invalid page numbers', async () => {
      wrapper = mount(EmployeesList)
      await nextTick()
      
      const initialCallCount = employeeService.fetchEmployees.mock.calls.length
      
      await wrapper.vm.changePage(0) // Invalid page
      await wrapper.vm.changePage(-1) // Invalid page
      
      // Should not make additional API calls
      expect(employeeService.fetchEmployees).toHaveBeenCalledTimes(initialCallCount)
    })
  })

  describe('Modal Management', () => {
    it('should close all modals when closeModals is called', async () => {
      wrapper = mount(EmployeesList)
      
      // Open all modals
      wrapper.vm.showCreateModal = true
      wrapper.vm.showEditModal = true
      wrapper.vm.showViewModal = true
      wrapper.vm.selectedEmployee = mockEmployees[0]
      
      // Close modals
      wrapper.vm.closeModals()
      await nextTick()
      
      expect(wrapper.vm.showCreateModal).toBe(false)
      expect(wrapper.vm.showEditModal).toBe(false)
      expect(wrapper.vm.showViewModal).toBe(false)
      expect(wrapper.vm.selectedEmployee).toBe(null)
    })
  })
})