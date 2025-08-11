import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import employeeService from '@/services/employeeService.js'
import { mockEmployee, mockEmployees, mockPaginatedResponse, createValidEmployeeFormData } from '../../fixtures/employees.js'

// Mock httpClient
vi.mock('@/utils/httpClient', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn()
  }
}))

import httpClient from '@/utils/httpClient'

describe('employeeService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('fetchEmployees', () => {
    test('should fetch employees with pagination', async () => {
      httpClient.get.mockResolvedValue({
        data: mockPaginatedResponse
      })

      const result = await employeeService.fetchEmployees(1)

      expect(httpClient.get).toHaveBeenCalledWith(
        expect.stringContaining('/employees?page=1')
      )

      expect(result.data).toHaveLength(mockEmployees.length)
      expect(result.pagination.totalItems).toBe(25)
      expect(result.pagination.currentPage).toBe(1)
      expect(result.pagination.totalPages).toBe(5)
    })

    test('should fetch employees with filters', async () => {
      httpClient.get.mockResolvedValue({
        data: mockPaginatedResponse
      })

      const filters = { search: 'John', position: 'Developer' }
      await employeeService.fetchEmployees(1, filters)

      expect(httpClient.get).toHaveBeenCalledWith(
        expect.stringContaining('search=John')
      )
      expect(httpClient.get).toHaveBeenCalledWith(
        expect.stringContaining('position=Developer')
      )
    })

    test('should handle network errors', async () => {
      httpClient.get.mockRejectedValue(new Error('Network Error'))

      await expect(employeeService.fetchEmployees()).rejects.toMatchObject({
        type: 'NETWORK_ERROR',
        message: expect.stringContaining('conexión')
      })
    })

    test('should handle server errors', async () => {
      const axiosError = {
        response: {
          status: 500,
          data: {}
        },
        message: 'Request failed with status code 500'
      }
      httpClient.get.mockRejectedValue(axiosError)

      await expect(employeeService.fetchEmployees()).rejects.toMatchObject({
        type: 'SERVER_ERROR',
        status: 500
      })
    })
  })

  describe('getEmployee', () => {
    test('should get single employee by id', async () => {
      httpClient.get.mockResolvedValue({
        data: mockEmployee
      })

      const result = await employeeService.getEmployee(1)

      expect(httpClient.get).toHaveBeenCalledWith('/employees/1')

      expect(result.id).toBe(mockEmployee.id)
      expect(result.fullName).toBe(`${mockEmployee.firstName} ${mockEmployee.lastName}`)
      expect(result.salary.amount).toBe(mockEmployee.salaryAmount)
      expect(result.salary.currency).toBe(mockEmployee.salaryCurrency)
    })

    test('should handle 404 errors', async () => {
      const axiosError = {
        response: {
          status: 404,
          data: {}
        },
        message: 'Request failed with status code 404'
      }
      httpClient.get.mockRejectedValue(axiosError)

      await expect(employeeService.getEmployee(999)).rejects.toMatchObject({
        type: 'NOT_FOUND',
        status: 404,
        message: expect.stringContaining('no fue encontrado')
      })
    })

    test('should require employee ID', async () => {
      await expect(employeeService.getEmployee()).rejects.toMatchObject({
        message: expect.stringContaining('inesperado')
      })
    })
  })

  describe('createEmployee', () => {
    test('should create new employee', async () => {
      const newEmployeeData = createValidEmployeeFormData()
      
      httpClient.post.mockResolvedValue({
        data: { ...mockEmployee, ...newEmployeeData }
      })

      const result = await employeeService.createEmployee(newEmployeeData)

      expect(httpClient.post).toHaveBeenCalledWith('/employees', newEmployeeData)

      expect(result.firstName).toBe(newEmployeeData.firstName)
      expect(result.email).toBe(newEmployeeData.email)
    })

    test('should validate required fields', async () => {
      const invalidData = {
        firstName: '',
        lastName: '',
        email: 'invalid-email',
        position: '',
        salaryAmount: -1000,
        salaryCurrency: '',
        hiredAt: ''
      }

      await expect(employeeService.createEmployee(invalidData)).rejects.toMatchObject({
        type: 'VALIDATION_ERROR',
        status: 422
      })
    })

    test('should handle validation errors from server', async () => {
      const axiosError = {
        response: {
          status: 422,
          data: {
            violations: [
              { propertyPath: 'email', message: 'Email already exists' }
            ]
          }
        },
        message: 'Request failed with status code 422'
      }
      httpClient.post.mockRejectedValue(axiosError)

      const validData = createValidEmployeeFormData()
      
      await expect(employeeService.createEmployee(validData)).rejects.toMatchObject({
        type: 'VALIDATION_ERROR',
        status: 422
      })
    })
  })

  describe('updateEmployee', () => {
    test('should update existing employee', async () => {
      const updateData = createValidEmployeeFormData({ firstName: 'Updated' })
      
      httpClient.put.mockResolvedValue({
        data: { ...mockEmployee, ...updateData }
      })

      const result = await employeeService.updateEmployee(1, updateData)

      expect(httpClient.put).toHaveBeenCalledWith('/employees/1', updateData)

      expect(result.firstName).toBe('Updated')
    })

    test('should require employee ID', async () => {
      const updateData = createValidEmployeeFormData()
      
      await expect(employeeService.updateEmployee(null, updateData)).rejects.toMatchObject({
        message: expect.stringContaining('inesperado')
      })
    })

    test('should validate data before update', async () => {
      const invalidData = { firstName: '', email: 'invalid' }
      
      await expect(employeeService.updateEmployee(1, invalidData)).rejects.toMatchObject({
        type: 'VALIDATION_ERROR',
        status: 422
      })
    })
  })

  describe('deleteEmployee', () => {
    test('should delete employee', async () => {
      httpClient.delete.mockResolvedValue({})

      const result = await employeeService.deleteEmployee(1)

      expect(httpClient.delete).toHaveBeenCalledWith('/employees/1')

      expect(result.success).toBe(true)
      expect(result.id).toBe(1)
    })

    test('should require employee ID', async () => {
      await expect(employeeService.deleteEmployee()).rejects.toMatchObject({
        message: expect.stringContaining('inesperado')
      })
    })

    test('should handle 404 when employee not found', async () => {
      const axiosError = {
        response: {
          status: 404,
          data: {}
        },
        message: 'Request failed with status code 404'
      }
      httpClient.delete.mockRejectedValue(axiosError)

      await expect(employeeService.deleteEmployee(999)).rejects.toMatchObject({
        type: 'NOT_FOUND',
        status: 404
      })
    })
  })

  describe('buildUrl', () => {
    test('should build URLs with parameters correctly', () => {
      const url = employeeService.buildUrl('/employees', { page: 1, search: 'John' })
      
      expect(url).toContain('/employees')
      expect(url).toContain('page=1')
      expect(url).toContain('search=John')
    })

    test('should handle empty parameters', () => {
      const url = employeeService.buildUrl('/employees', {})
      
      expect(url).toBe('http://localhost:8000/api/employees')
    })

    test('should ignore null and undefined parameters', () => {
      const url = employeeService.buildUrl('/employees', { 
        page: 1, 
        search: null, 
        filter: undefined 
      })
      
      expect(url).toContain('page=1')
      expect(url).not.toContain('search=')
      expect(url).not.toContain('filter=')
    })
  })

  describe('transformEmployee', () => {
    test('should transform API responses correctly', () => {
      const apiEmployee = {
        id: 1,
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        position: 'Developer',
        salaryAmount: 50000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-01-15T00:00:00+00:00'
      }

      const result = employeeService.transformEmployee(apiEmployee)

      expect(result.fullName).toBe('John Doe')
      expect(result.salary.amount).toBe(50000)
      expect(result.salary.currency).toBe('EUR')
      expect(result.hiredAt).toBeInstanceOf(Date)
    })

    test('should handle null input', () => {
      const result = employeeService.transformEmployee(null)
      expect(result).toBe(null)
    })
  })

  describe('validateEmployeeData', () => {
    test('should validate correct employee data', () => {
      const validData = createValidEmployeeFormData()
      const result = employeeService.validateEmployeeData(validData)

      expect(result.isValid).toBe(true)
      expect(result.errors).toHaveLength(0)
    })

    test('should detect missing required fields', () => {
      const invalidData = {
        firstName: '',
        lastName: '',
        email: '',
        position: '',
        salaryAmount: 0,
        salaryCurrency: '',
        hiredAt: ''
      }

      const result = employeeService.validateEmployeeData(invalidData)

      expect(result.isValid).toBe(false)
      expect(result.errors.length).toBeGreaterThan(0)
      expect(result.errors.some(error => error.includes('nombre'))).toBe(true)
      expect(result.errors.some(error => error.includes('apellido'))).toBe(true)
      expect(result.errors.some(error => error.includes('email'))).toBe(true)
    })

    test('should validate email format', () => {
      const invalidEmailData = createValidEmployeeFormData({ email: 'invalid-email' })
      const result = employeeService.validateEmployeeData(invalidEmailData)

      expect(result.isValid).toBe(false)
      expect(result.errors.some(error => error.includes('formato'))).toBe(true)
    })

    test('should validate salary amount', () => {
      const invalidSalaryData = createValidEmployeeFormData({ salaryAmount: -1000 })
      const result = employeeService.validateEmployeeData(invalidSalaryData)

      expect(result.isValid).toBe(false)
      expect(result.errors.some(error => error.includes('salario'))).toBe(true)
    })
  })

  describe('handleApiError', () => {
    test('should handle network errors', () => {
      const networkError = new Error('fetch failed')
      const result = employeeService.handleApiError(networkError)

      expect(result.type).toBe('NETWORK_ERROR')
      expect(result.message).toContain('conexión')
    })

    test('should handle 422 validation errors', () => {
      const validationError = {
        status: 422,
        message: 'Validation failed',
        details: { violations: [] }
      }
      const result = employeeService.handleApiError(validationError)

      expect(result.type).toBe('VALIDATION_ERROR')
      expect(result.status).toBe(422)
    })

    test('should handle 500 server errors', () => {
      const serverError = {
        status: 500,
        message: 'Internal Server Error'
      }
      const result = employeeService.handleApiError(serverError)

      expect(result.type).toBe('SERVER_ERROR')
      expect(result.status).toBe(500)
    })

    test('should handle generic errors', () => {
      const genericError = new Error('Something went wrong')
      const result = employeeService.handleApiError(genericError)

      expect(result.type).toBe('SERVER_ERROR')
      expect(result.message).toContain('inesperado')
    })
  })
})