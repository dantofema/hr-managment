import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { useEmployees } from '@/composables/useEmployees.js'
import employeeService from '@/services/employeeService.js'
import { mockEmployee, mockEmployees, createValidEmployeeFormData } from '../../fixtures/employees.js'
import { flushPromises } from '../../utils/test-utils.js'

// Mock del servicio
vi.mock('@/services/employeeService.js', () => ({
  default: {
    getEmployees: vi.fn(),
    getEmployee: vi.fn(),
    createEmployee: vi.fn(),
    updateEmployee: vi.fn(),
    deleteEmployee: vi.fn()
  }
}))

describe('useEmployees composable', () => {
  let composable

  beforeEach(() => {
    vi.spyOn(console, 'error').mockImplementation(() => {})
    vi.spyOn(console, 'warn').mockImplementation(() => {})
    
    // Setup default mock responses
    employeeService.getEmployees.mockResolvedValue({
      employees: mockEmployees,
      totalItems: mockEmployees.length,
      currentPage: 1
    })
    
    employeeService.getEmployee.mockResolvedValue(mockEmployee)
    employeeService.createEmployee.mockResolvedValue(mockEmployee)
    employeeService.updateEmployee.mockResolvedValue(mockEmployee)
    employeeService.deleteEmployee.mockResolvedValue()
    
    composable = useEmployees()
  })

  afterEach(() => {
    vi.clearAllMocks()
    vi.restoreAllMocks()
  })

  describe('initial state', () => {
    test('should have correct initial state', () => {
      expect(composable.employees.value).toEqual([])
      expect(composable.currentEmployee.value).toBe(null)
      expect(composable.loading.value).toBe(false)
      expect(composable.error.value).toBe(null)
      expect(composable.pagination.value).toEqual({
        currentPage: 1,
        totalPages: 1,
        totalItems: 0
      })
    })

    test('should have correct initial computed properties', () => {
      expect(composable.hasEmployees.value).toBe(false)
      expect(composable.isLoading.value).toBe(false)
      expect(composable.hasError.value).toBe(false)
      expect(composable.canLoadMore.value).toBe(false)
    })

    test('should have correct initial loading states', () => {
      expect(composable.loadingStates.value.fetching).toBe(false)
      expect(composable.loadingStates.value.creating).toBe(false)
      expect(composable.loadingStates.value.updating).toBe(false)
      expect(composable.loadingStates.value.deleting).toBe(false)
      expect(composable.loadingStates.value.fetchingOne).toBe(false)
    })
  })

  describe('fetchEmployees', () => {
    test('should fetch employees successfully', async () => {
      const result = await composable.fetchEmployees()

      expect(employeeService.getEmployees).toHaveBeenCalledWith(1, {})
      expect(composable.employees.value).toEqual(mockEmployees)
      expect(composable.pagination.value.totalItems).toBe(mockEmployees.length)
      expect(composable.hasEmployees.value).toBe(true)
      expect(result.employees).toEqual(mockEmployees)
    })

    test('should fetch employees with pagination and filters', async () => {
      const filters = { search: 'John', position: 'Developer' }
      await composable.fetchEmployees(2, filters)

      expect(employeeService.getEmployees).toHaveBeenCalledWith(2, filters)
    })

    test('should handle fetch employees error', async () => {
      const errorMessage = 'Network error'
      employeeService.getEmployees.mockRejectedValue(new Error(errorMessage))

      await expect(composable.fetchEmployees()).rejects.toThrow(errorMessage)
      
      expect(composable.error.value).toBe(errorMessage)
      expect(composable.hasError.value).toBe(true)
      expect(composable.employees.value).toEqual([])
    })

    test('should manage loading states correctly', async () => {
      let loadingDuringFetch = false
      
      employeeService.getEmployees.mockImplementation(async () => {
        loadingDuringFetch = composable.loadingStates.value.fetching
        return {
          employees: mockEmployees,
          totalItems: mockEmployees.length,
          currentPage: 1
        }
      })

      await composable.fetchEmployees()

      expect(loadingDuringFetch).toBe(true)
      expect(composable.loadingStates.value.fetching).toBe(false)
      expect(composable.loading.value).toBe(false)
    })
  })

  describe('fetchEmployee', () => {
    test('should fetch single employee successfully', async () => {
      const result = await composable.fetchEmployee(1)

      expect(employeeService.getEmployee).toHaveBeenCalledWith(1)
      expect(composable.currentEmployee.value).toEqual(mockEmployee)
      expect(result).toEqual(mockEmployee)
    })

    test('should handle fetch employee error', async () => {
      const errorMessage = 'Employee not found'
      employeeService.getEmployee.mockRejectedValue(new Error(errorMessage))

      await expect(composable.fetchEmployee(999)).rejects.toThrow(errorMessage)
      
      expect(composable.error.value).toBe(errorMessage)
      expect(composable.currentEmployee.value).toBe(null)
    })

    test('should manage loading state for single employee', async () => {
      let loadingDuringFetch = false
      
      employeeService.getEmployee.mockImplementation(async () => {
        loadingDuringFetch = composable.loadingStates.value.fetchingOne
        return mockEmployee
      })

      await composable.fetchEmployee(1)

      expect(loadingDuringFetch).toBe(true)
      expect(composable.loadingStates.value.fetchingOne).toBe(false)
    })
  })

  describe('createEmployee', () => {
    test('should create employee successfully', async () => {
      const newEmployeeData = createValidEmployeeFormData()
      const createdEmployee = { ...mockEmployee, ...newEmployeeData }
      employeeService.createEmployee.mockResolvedValue(createdEmployee)

      // Set pagination to first page
      composable.pagination.value.currentPage = 1

      const result = await composable.createEmployee(newEmployeeData)

      expect(employeeService.createEmployee).toHaveBeenCalledWith(newEmployeeData)
      expect(result).toEqual(createdEmployee)
      expect(composable.employees.value[0]).toEqual(createdEmployee)
      expect(composable.pagination.value.totalItems).toBe(1)
    })

    test('should handle create employee error', async () => {
      const errorMessage = 'Validation failed'
      employeeService.createEmployee.mockRejectedValue(new Error(errorMessage))

      const newEmployeeData = createValidEmployeeFormData()
      
      await expect(composable.createEmployee(newEmployeeData)).rejects.toThrow(errorMessage)
      
      expect(composable.error.value).toBe(errorMessage)
    })

    test('should manage loading state during creation', async () => {
      let loadingDuringCreate = false
      
      employeeService.createEmployee.mockImplementation(async () => {
        loadingDuringCreate = composable.loadingStates.value.creating
        return mockEmployee
      })

      await composable.createEmployee(createValidEmployeeFormData())

      expect(loadingDuringCreate).toBe(true)
      expect(composable.loadingStates.value.creating).toBe(false)
    })

    test('should not add to list if not on first page', async () => {
      const newEmployeeData = createValidEmployeeFormData()
      const createdEmployee = { ...mockEmployee, ...newEmployeeData }
      employeeService.createEmployee.mockResolvedValue(createdEmployee)

      // Set pagination to second page
      composable.pagination.value.currentPage = 2

      await composable.createEmployee(newEmployeeData)

      expect(composable.employees.value).toEqual([])
      expect(composable.pagination.value.totalItems).toBe(1)
    })
  })

  describe('updateEmployee', () => {
    test('should update employee successfully', async () => {
      const updateData = createValidEmployeeFormData({ firstName: 'Updated' })
      const updatedEmployee = { ...mockEmployee, ...updateData }
      employeeService.updateEmployee.mockResolvedValue(updatedEmployee)

      // Add employee to list first
      composable.employees.value = [mockEmployee]

      const result = await composable.updateEmployee(mockEmployee.id, updateData)

      expect(employeeService.updateEmployee).toHaveBeenCalledWith(mockEmployee.id, updateData)
      expect(result).toEqual(updatedEmployee)
      expect(composable.employees.value[0]).toEqual(updatedEmployee)
    })

    test('should update current employee if same ID', async () => {
      const updateData = createValidEmployeeFormData({ firstName: 'Updated' })
      const updatedEmployee = { ...mockEmployee, ...updateData }
      employeeService.updateEmployee.mockResolvedValue(updatedEmployee)

      // Set current employee
      composable.setCurrentEmployee(mockEmployee)

      await composable.updateEmployee(mockEmployee.id, updateData)

      expect(composable.currentEmployee.value).toEqual(updatedEmployee)
    })

    test('should handle update employee error', async () => {
      const errorMessage = 'Update failed'
      employeeService.updateEmployee.mockRejectedValue(new Error(errorMessage))

      await expect(composable.updateEmployee(1, {})).rejects.toThrow(errorMessage)
      
      expect(composable.error.value).toBe(errorMessage)
    })

    test('should manage loading state during update', async () => {
      let loadingDuringUpdate = false
      
      employeeService.updateEmployee.mockImplementation(async () => {
        loadingDuringUpdate = composable.loadingStates.value.updating
        return mockEmployee
      })

      await composable.updateEmployee(1, createValidEmployeeFormData())

      expect(loadingDuringUpdate).toBe(true)
      expect(composable.loadingStates.value.updating).toBe(false)
    })
  })

  describe('deleteEmployee', () => {
    test('should delete employee successfully', async () => {
      // Add employee to list first
      composable.employees.value = [mockEmployee]
      composable.pagination.value.totalItems = 1

      const result = await composable.deleteEmployee(mockEmployee.id)

      expect(employeeService.deleteEmployee).toHaveBeenCalledWith(mockEmployee.id)
      expect(result).toBe(true)
      expect(composable.employees.value).toEqual([])
      expect(composable.pagination.value.totalItems).toBe(0)
    })

    test('should clear current employee if same ID', async () => {
      composable.setCurrentEmployee(mockEmployee)
      composable.employees.value = [mockEmployee]

      await composable.deleteEmployee(mockEmployee.id)

      expect(composable.currentEmployee.value).toBe(null)
    })

    test('should handle delete employee error', async () => {
      const errorMessage = 'Delete failed'
      employeeService.deleteEmployee.mockRejectedValue(new Error(errorMessage))

      await expect(composable.deleteEmployee(1)).rejects.toThrow(errorMessage)
      
      expect(composable.error.value).toBe(errorMessage)
    })

    test('should manage loading state during deletion', async () => {
      let loadingDuringDelete = false
      
      employeeService.deleteEmployee.mockImplementation(async () => {
        loadingDuringDelete = composable.loadingStates.value.deleting
      })

      await composable.deleteEmployee(1)

      expect(loadingDuringDelete).toBe(true)
      expect(composable.loadingStates.value.deleting).toBe(false)
    })
  })

  describe('utility functions', () => {
    test('should clear errors', () => {
      composable.error.value = 'Some error'
      
      composable.clearError()
      
      expect(composable.error.value).toBe(null)
      expect(composable.hasError.value).toBe(false)
    })

    test('should reset pagination', () => {
      composable.pagination.value = {
        currentPage: 5,
        totalPages: 10,
        totalItems: 100
      }
      
      composable.resetPagination()
      
      expect(composable.pagination.value).toEqual({
        currentPage: 1,
        totalPages: 1,
        totalItems: 0
      })
    })

    test('should set current employee', () => {
      composable.setCurrentEmployee(mockEmployee)
      
      expect(composable.currentEmployee.value).toEqual(mockEmployee)
    })

    test('should handle invalid employee in setCurrentEmployee', () => {
      composable.setCurrentEmployee(null)
      composable.setCurrentEmployee('invalid')
      
      expect(console.warn).toHaveBeenCalledWith(
        'setCurrentEmployee: Invalid employee object provided'
      )
    })

    test('should clear current employee', () => {
      composable.setCurrentEmployee(mockEmployee)
      
      composable.clearCurrentEmployee()
      
      expect(composable.currentEmployee.value).toBe(null)
    })

    test('should refresh employees', async () => {
      const filters = { search: 'test' }
      composable.pagination.value.currentPage = 2
      
      await composable.refreshEmployees(filters)
      
      expect(employeeService.getEmployees).toHaveBeenCalledWith(2, filters)
    })
  })

  describe('computed properties', () => {
    test('should calculate hasEmployees correctly', () => {
      expect(composable.hasEmployees.value).toBe(false)
      
      composable.employees.value = [mockEmployee]
      
      expect(composable.hasEmployees.value).toBe(true)
    })

    test('should calculate isLoading correctly', () => {
      expect(composable.isLoading.value).toBe(false)
      
      composable.loadingStates.value.fetching = true
      
      expect(composable.isLoading.value).toBe(true)
      
      composable.loadingStates.value.fetching = false
      composable.loadingStates.value.creating = true
      
      expect(composable.isLoading.value).toBe(true)
    })

    test('should calculate canLoadMore correctly', () => {
      composable.pagination.value = {
        currentPage: 1,
        totalPages: 3,
        totalItems: 30
      }
      
      expect(composable.canLoadMore.value).toBe(true)
      
      composable.pagination.value.currentPage = 3
      
      expect(composable.canLoadMore.value).toBe(false)
    })
  })

  describe('pagination management', () => {
    test('should update pagination correctly', async () => {
      employeeService.getEmployees.mockResolvedValue({
        employees: mockEmployees,
        totalItems: 50,
        currentPage: 2
      })

      await composable.fetchEmployees(2)

      expect(composable.pagination.value).toEqual({
        currentPage: 2,
        totalItems: 50,
        totalPages: 3 // 50 items / 20 per page = 2.5 -> 3 pages
      })
    })
  })

  describe('error handling', () => {
    test('should auto-clear errors after timeout', async () => {
      vi.useFakeTimers()
      
      composable.error.value = 'Test error'
      
      // Fast-forward time by 10 seconds
      vi.advanceTimersByTime(10000)
      await flushPromises()
      
      expect(composable.error.value).toBe(null)
      
      vi.useRealTimers()
    })

    test('should not clear different error after timeout', async () => {
      vi.useFakeTimers()
      
      composable.error.value = 'First error'
      
      // Change error before timeout
      setTimeout(() => {
        composable.error.value = 'Second error'
      }, 5000)
      
      vi.advanceTimersByTime(5000)
      await flushPromises()
      
      // Fast-forward remaining time
      vi.advanceTimersByTime(5000)
      await flushPromises()
      
      // Should still have the second error
      expect(composable.error.value).toBe('Second error')
      
      vi.useRealTimers()
    })
  })

  describe('readonly state', () => {
    test('should provide readonly access to state', () => {
      // These should not throw errors but also shouldn't modify the original
      expect(() => {
        composable.employees.value = []
      }).toThrow()
      
      expect(() => {
        composable.currentEmployee.value = null
      }).toThrow()
      
      expect(() => {
        composable.error.value = null
      }).toThrow()
    })
  })

  describe('optimistic updates', () => {
    test('should handle optimistic update failure gracefully', async () => {
      // Add employee to list
      composable.employees.value = [mockEmployee]
      
      // Mock update to fail
      employeeService.updateEmployee.mockRejectedValue(new Error('Update failed'))
      
      const originalEmployee = { ...mockEmployee }
      
      try {
        await composable.updateEmployee(mockEmployee.id, { firstName: 'Updated' })
      } catch (error) {
        // The employee in the list should remain unchanged after error
        expect(composable.employees.value[0]).toEqual(originalEmployee)
      }
    })
  })
})