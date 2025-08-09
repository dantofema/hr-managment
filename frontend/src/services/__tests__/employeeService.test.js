/**
 * Unit tests for employeeService.js
 * Tests CRUD operations, error handling, and helper functions
 */

import employeeService from '../employeeService.js';

// Mock fetch globally
global.fetch = jest.fn();

describe('employeeService', () => {
  beforeEach(() => {
    fetch.mockClear();
  });

  describe('Helper Functions', () => {
    describe('buildUrl', () => {
      test('should build URL without parameters', () => {
        const url = employeeService.buildUrl('/employees');
        expect(url).toBe('http://localhost:8000/api/employees');
      });

      test('should build URL with parameters', () => {
        const url = employeeService.buildUrl('/employees', { page: 1, filter: 'test' });
        expect(url).toContain('page=1');
        expect(url).toContain('filter=test');
      });

      test('should ignore null/undefined parameters', () => {
        const url = employeeService.buildUrl('/employees', { page: 1, filter: null, search: undefined });
        expect(url).toContain('page=1');
        expect(url).not.toContain('filter=');
        expect(url).not.toContain('search=');
      });
    });

    describe('validateEmployeeData', () => {
      test('should validate correct employee data', () => {
        const validData = {
          firstName: 'John',
          lastName: 'Doe',
          email: 'john@example.com',
          position: 'Developer',
          salaryAmount: 50000,
          salaryCurrency: 'EUR',
          hiredAt: '2024-01-15'
        };

        const result = employeeService.validateEmployeeData(validData);
        expect(result.isValid).toBe(true);
        expect(result.errors).toHaveLength(0);
      });

      test('should return errors for invalid data', () => {
        const invalidData = {
          firstName: '',
          lastName: '',
          email: 'invalid-email',
          position: '',
          salaryAmount: -1000,
          salaryCurrency: '',
          hiredAt: null
        };

        const result = employeeService.validateEmployeeData(invalidData);
        expect(result.isValid).toBe(false);
        expect(result.errors.length).toBeGreaterThan(0);
        expect(result.errors).toContain('El nombre es requerido');
        expect(result.errors).toContain('El formato del email no es válido');
      });
    });

    describe('transformEmployee', () => {
      test('should transform API employee data correctly', () => {
        const apiEmployee = {
          id: 1,
          firstName: 'John',
          lastName: 'Doe',
          email: 'john@example.com',
          position: 'Developer',
          salaryAmount: 50000,
          salaryCurrency: 'EUR',
          hiredAt: '2023-01-15T00:00:00+00:00'
        };

        const transformed = employeeService.transformEmployee(apiEmployee);
        
        expect(transformed.id).toBe(1);
        expect(transformed.fullName).toBe('John Doe');
        expect(transformed.salary.amount).toBe(50000);
        expect(transformed.salary.currency).toBe('EUR');
        expect(transformed.hiredAt).toBeInstanceOf(Date);
      });

      test('should return null for null input', () => {
        const result = employeeService.transformEmployee(null);
        expect(result).toBeNull();
      });
    });

    describe('handleApiError', () => {
      test('should handle network errors', () => {
        const networkError = new Error('fetch failed');
        const result = employeeService.handleApiError(networkError);
        
        expect(result.type).toBe('NETWORK_ERROR');
        expect(result.message).toContain('Error de conexión');
        expect(result.status).toBeNull();
      });

      test('should handle 404 errors', () => {
        const notFoundError = { status: 404, message: 'Not Found' };
        const result = employeeService.handleApiError(notFoundError);
        
        expect(result.type).toBe('NOT_FOUND');
        expect(result.status).toBe(404);
      });

      test('should handle validation errors', () => {
        const validationError = { status: 422, message: 'Validation failed', details: ['error1'] };
        const result = employeeService.handleApiError(validationError);
        
        expect(result.type).toBe('VALIDATION_ERROR');
        expect(result.status).toBe(422);
      });
    });
  });

  describe('CRUD Operations', () => {
    describe('fetchEmployees', () => {
      test('should fetch employees successfully', async () => {
        const mockResponse = {
          'hydra:member': [
            {
              id: 1,
              firstName: 'John',
              lastName: 'Doe',
              email: 'john@example.com',
              position: 'Developer',
              salaryAmount: 50000,
              salaryCurrency: 'EUR',
              hiredAt: '2023-01-15T00:00:00+00:00'
            }
          ],
          'hydra:totalItems': 1,
          'hydra:view': {
            '@id': '/api/employees?page=1'
          }
        };

        fetch.mockResolvedValueOnce({
          ok: true,
          json: async () => mockResponse
        });

        const result = await employeeService.fetchEmployees(1);
        
        expect(result.data).toHaveLength(1);
        expect(result.data[0].fullName).toBe('John Doe');
        expect(result.pagination.totalItems).toBe(1);
        expect(result.pagination.currentPage).toBe(1);
      });

      test('should handle fetch employees error', async () => {
        fetch.mockResolvedValueOnce({
          ok: false,
          status: 500,
          statusText: 'Internal Server Error',
          json: async () => ({})
        });

        await expect(employeeService.fetchEmployees()).rejects.toMatchObject({
          type: 'SERVER_ERROR',
          status: 500
        });
      });
    });

    describe('getEmployee', () => {
      test('should get single employee successfully', async () => {
        const mockEmployee = {
          id: 1,
          firstName: 'John',
          lastName: 'Doe',
          email: 'john@example.com',
          position: 'Developer',
          salaryAmount: 50000,
          salaryCurrency: 'EUR',
          hiredAt: '2023-01-15T00:00:00+00:00'
        };

        fetch.mockResolvedValueOnce({
          ok: true,
          json: async () => mockEmployee
        });

        const result = await employeeService.getEmployee(1);
        
        expect(result.id).toBe(1);
        expect(result.fullName).toBe('John Doe');
      });

      test('should handle employee not found', async () => {
        fetch.mockResolvedValueOnce({
          ok: false,
          status: 404,
          statusText: 'Not Found',
          json: async () => ({})
        });

        await expect(employeeService.getEmployee(999)).rejects.toMatchObject({
          type: 'NOT_FOUND',
          status: 404
        });
      });

      test('should throw error for missing ID', async () => {
        await expect(employeeService.getEmployee()).rejects.toMatchObject({
          type: 'SERVER_ERROR'
        });
      });
    });

    describe('createEmployee', () => {
      test('should create employee successfully', async () => {
        const newEmployeeData = {
          firstName: 'Jane',
          lastName: 'Smith',
          email: 'jane@example.com',
          position: 'Designer',
          salaryAmount: 45000,
          salaryCurrency: 'EUR',
          hiredAt: '2024-01-15'
        };

        const mockCreatedEmployee = {
          id: 2,
          ...newEmployeeData,
          hiredAt: '2024-01-15T00:00:00+00:00'
        };

        fetch.mockResolvedValueOnce({
          ok: true,
          json: async () => mockCreatedEmployee
        });

        const result = await employeeService.createEmployee(newEmployeeData);
        
        expect(result.id).toBe(2);
        expect(result.fullName).toBe('Jane Smith');
        expect(fetch).toHaveBeenCalledWith(
          expect.stringContaining('/employees'),
          expect.objectContaining({
            method: 'POST',
            headers: expect.objectContaining({
              'Content-Type': 'application/json'
            }),
            body: JSON.stringify(newEmployeeData)
          })
        );
      });

      test('should handle validation errors on create', async () => {
        const invalidData = {
          firstName: '',
          lastName: 'Smith',
          email: 'invalid-email',
          position: 'Designer',
          salaryAmount: -1000,
          salaryCurrency: 'EUR',
          hiredAt: '2024-01-15'
        };

        await expect(employeeService.createEmployee(invalidData)).rejects.toMatchObject({
          type: 'VALIDATION_ERROR',
          status: 422
        });

        // Should not make API call if validation fails
        expect(fetch).not.toHaveBeenCalled();
      });

      test('should handle server errors on create', async () => {
        const validData = {
          firstName: 'Jane',
          lastName: 'Smith',
          email: 'jane@example.com',
          position: 'Designer',
          salaryAmount: 45000,
          salaryCurrency: 'EUR',
          hiredAt: '2024-01-15'
        };

        fetch.mockResolvedValueOnce({
          ok: false,
          status: 500,
          statusText: 'Internal Server Error',
          json: async () => ({})
        });

        await expect(employeeService.createEmployee(validData)).rejects.toMatchObject({
          type: 'SERVER_ERROR',
          status: 500
        });
      });
    });

    describe('updateEmployee', () => {
      test('should update employee successfully', async () => {
        const updateData = {
          firstName: 'John',
          lastName: 'Doe Updated',
          email: 'john.updated@example.com',
          position: 'Senior Developer',
          salaryAmount: 60000,
          salaryCurrency: 'EUR',
          hiredAt: '2023-01-15'
        };

        const mockUpdatedEmployee = {
          id: 1,
          ...updateData,
          hiredAt: '2023-01-15T00:00:00+00:00'
        };

        fetch.mockResolvedValueOnce({
          ok: true,
          json: async () => mockUpdatedEmployee
        });

        const result = await employeeService.updateEmployee(1, updateData);
        
        expect(result.id).toBe(1);
        expect(result.fullName).toBe('John Doe Updated');
        expect(result.position).toBe('Senior Developer');
      });

      test('should handle update validation errors', async () => {
        const invalidData = {
          firstName: '',
          lastName: 'Doe',
          email: 'john@example.com',
          position: 'Developer',
          salaryAmount: 50000,
          salaryCurrency: 'EUR',
          hiredAt: '2023-01-15'
        };

        await expect(employeeService.updateEmployee(1, invalidData)).rejects.toMatchObject({
          type: 'VALIDATION_ERROR',
          status: 422
        });
      });
    });

    describe('deleteEmployee', () => {
      test('should delete employee successfully', async () => {
        fetch.mockResolvedValueOnce({
          ok: true,
          json: async () => ({})
        });

        const result = await employeeService.deleteEmployee(1);
        
        expect(result.success).toBe(true);
        expect(result.id).toBe(1);
        expect(fetch).toHaveBeenCalledWith(
          expect.stringContaining('/employees/1'),
          expect.objectContaining({
            method: 'DELETE'
          })
        );
      });

      test('should handle delete errors', async () => {
        fetch.mockResolvedValueOnce({
          ok: false,
          status: 404,
          statusText: 'Not Found',
          json: async () => ({})
        });

        await expect(employeeService.deleteEmployee(999)).rejects.toMatchObject({
          type: 'NOT_FOUND',
          status: 404
        });
      });

      test('should throw error for missing ID on delete', async () => {
        await expect(employeeService.deleteEmployee()).rejects.toMatchObject({
          type: 'SERVER_ERROR'
        });
      });
    });
  });

  describe('Error Handling', () => {
    test('should handle network timeout errors', async () => {
      fetch.mockRejectedValueOnce(new Error('fetch timeout'));

      await expect(employeeService.fetchEmployees()).rejects.toMatchObject({
        type: 'NETWORK_ERROR'
      });
    });

    test('should handle JSON parsing errors', async () => {
      fetch.mockResolvedValueOnce({
        ok: true,
        json: async () => {
          throw new Error('Invalid JSON');
        }
      });

      await expect(employeeService.fetchEmployees()).rejects.toMatchObject({
        type: 'SERVER_ERROR'
      });
    });

    test('should handle various HTTP status codes', async () => {
      // Test 422 Validation Error
      fetch.mockResolvedValueOnce({
        ok: false,
        status: 422,
        statusText: 'Unprocessable Entity',
        json: async () => ({ errors: ['validation error'] })
      });

      await expect(employeeService.fetchEmployees()).rejects.toMatchObject({
        type: 'VALIDATION_ERROR',
        status: 422
      });
    });
  });
});