/**
 * Unit tests for useEmployees.js composable
 * Tests CRUD operations, state management, error handling, and computed properties
 */

import { nextTick } from 'vue';
import { useEmployees } from '../useEmployees.js';
import employeeService from '../../services/employeeService.js';

// Mock the employeeService
jest.mock('../../services/employeeService.js');

describe('useEmployees', () => {
  let composable;

  beforeEach(() => {
    // Reset all mocks
    jest.clearAllMocks();
    
    // Create a fresh instance of the composable for each test
    composable = useEmployees();
  });

  describe('Initial State', () => {
    test('should initialize with correct default values', () => {
      expect(composable.employees.value).toEqual([]);
      expect(composable.currentEmployee.value).toBeNull();
      expect(composable.loading.value).toBe(false);
      expect(composable.error.value).toBeNull();
      expect(composable.pagination.value).toEqual({
        currentPage: 1,
        totalPages: 1,
        totalItems: 0
      });
      expect(composable.loadingStates).toEqual({
        fetching: false,
        creating: false,
        updating: false,
        deleting: false,
        fetchingOne: false
      });
    });

    test('should have correct computed properties initial values', () => {
      expect(composable.hasEmployees.value).toBe(false);
      expect(composable.isLoading.value).toBe(false);
      expect(composable.hasError.value).toBe(false);
      expect(composable.canLoadMore.value).toBe(false);
    });
  });

  describe('fetchEmployees', () => {
    test('should fetch employees successfully', async () => {
      const mockResponse = {
        employees: [
          { id: 1, firstName: 'John', lastName: 'Doe', email: 'john@example.com' },
          { id: 2, firstName: 'Jane', lastName: 'Smith', email: 'jane@example.com' }
        ],
        totalItems: 2,
        currentPage: 1
      };

      employeeService.getEmployees.mockResolvedValue(mockResponse);

      const result = await composable.fetchEmployees(1);

      expect(composable.loadingStates.fetching).toBe(false);
      expect(composable.loading.value).toBe(false);
      expect(composable.employees.value).toEqual(mockResponse.employees);
      expect(composable.pagination.value.totalItems).toBe(2);
      expect(composable.pagination.value.currentPage).toBe(1);
      expect(composable.error.value).toBeNull();
      expect(result).toEqual(mockResponse);
    });

    test('should handle fetch employees error', async () => {
      const errorMessage = 'Failed to fetch employees';
      employeeService.getEmployees.mockRejectedValue(new Error(errorMessage));

      await expect(composable.fetchEmployees()).rejects.toThrow(errorMessage);
      
      expect(composable.loadingStates.fetching).toBe(false);
      expect(composable.loading.value).toBe(false);
      expect(composable.error.value).toBe(errorMessage);
      expect(composable.employees.value).toEqual([]);
    });

    test('should set loading states correctly during fetch', async () => {
      let resolvePromise;
      const promise = new Promise((resolve) => {
        resolvePromise = resolve;
      });
      
      employeeService.getEmployees.mockReturnValue(promise);

      const fetchPromise = composable.fetchEmployees();
      
      // Check loading states are set
      expect(composable.loadingStates.fetching).toBe(true);
      expect(composable.loading.value).toBe(true);
      expect(composable.isLoading.value).toBe(true);

      // Resolve the promise
      resolvePromise({ employees: [], totalItems: 0, currentPage: 1 });
      await fetchPromise;

      // Check loading states are cleared
      expect(composable.loadingStates.fetching).toBe(false);
      expect(composable.loading.value).toBe(false);
      expect(composable.isLoading.value).toBe(false);
    });
  });

  describe('fetchEmployee', () => {
    test('should fetch single employee successfully', async () => {
      const mockEmployee = {
        id: 1,
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
      };

      employeeService.getEmployee.mockResolvedValue(mockEmployee);

      const result = await composable.fetchEmployee(1);

      expect(composable.loadingStates.fetchingOne).toBe(false);
      expect(composable.currentEmployee.value).toEqual(mockEmployee);
      expect(composable.error.value).toBeNull();
      expect(result).toEqual(mockEmployee);
    });

    test('should handle fetch employee error', async () => {
      const errorMessage = 'Employee not found';
      employeeService.getEmployee.mockRejectedValue(new Error(errorMessage));

      await expect(composable.fetchEmployee(999)).rejects.toThrow(errorMessage);
      
      expect(composable.loadingStates.fetchingOne).toBe(false);
      expect(composable.error.value).toBe(errorMessage);
      expect(composable.currentEmployee.value).toBeNull();
    });
  });

  describe('createEmployee', () => {
    test('should create employee successfully', async () => {
      const newEmployeeData = {
        firstName: 'Jane',
        lastName: 'Smith',
        email: 'jane@example.com',
        position: 'Designer'
      };

      const createdEmployee = { id: 3, ...newEmployeeData };
      employeeService.createEmployee.mockResolvedValue(createdEmployee);

      // Set initial state to simulate first page
      composable.pagination.value.currentPage = 1;

      const result = await composable.createEmployee(newEmployeeData);

      expect(composable.loadingStates.creating).toBe(false);
      expect(composable.employees.value).toContain(createdEmployee);
      expect(composable.pagination.value.totalItems).toBe(1);
      expect(composable.error.value).toBeNull();
      expect(result).toEqual(createdEmployee);
    });

    test('should handle create employee error', async () => {
      const errorMessage = 'Validation failed';
      employeeService.createEmployee.mockRejectedValue(new Error(errorMessage));

      const newEmployeeData = { firstName: 'Jane' };

      await expect(composable.createEmployee(newEmployeeData)).rejects.toThrow(errorMessage);
      
      expect(composable.loadingStates.creating).toBe(false);
      expect(composable.error.value).toBe(errorMessage);
      expect(composable.employees.value).toEqual([]);
    });

    test('should not add to list if not on first page', async () => {
      const newEmployeeData = { firstName: 'Jane', lastName: 'Smith' };
      const createdEmployee = { id: 3, ...newEmployeeData };
      
      employeeService.createEmployee.mockResolvedValue(createdEmployee);
      
      // Set to page 2
      composable.pagination.value.currentPage = 2;

      await composable.createEmployee(newEmployeeData);

      expect(composable.employees.value).not.toContain(createdEmployee);
      expect(composable.pagination.value.totalItems).toBe(1);
    });
  });

  describe('updateEmployee', () => {
    test('should update employee successfully', async () => {
      const originalEmployee = {
        id: 1,
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
      };

      const updatedData = {
        firstName: 'John',
        lastName: 'Doe Updated',
        email: 'john.updated@example.com'
      };

      const updatedEmployee = { ...originalEmployee, ...updatedData };

      // Set initial employees list
      composable.employees.value = [originalEmployee];
      composable.currentEmployee.value = originalEmployee;

      employeeService.updateEmployee.mockResolvedValue(updatedEmployee);

      const result = await composable.updateEmployee(1, updatedData);

      expect(composable.loadingStates.updating).toBe(false);
      expect(composable.employees.value[0]).toEqual(updatedEmployee);
      expect(composable.currentEmployee.value).toEqual(updatedEmployee);
      expect(composable.error.value).toBeNull();
      expect(result).toEqual(updatedEmployee);
    });

    test('should handle update employee error', async () => {
      const errorMessage = 'Employee not found';
      employeeService.updateEmployee.mockRejectedValue(new Error(errorMessage));

      await expect(composable.updateEmployee(999, {})).rejects.toThrow(errorMessage);
      
      expect(composable.loadingStates.updating).toBe(false);
      expect(composable.error.value).toBe(errorMessage);
    });
  });

  describe('deleteEmployee', () => {
    test('should delete employee successfully', async () => {
      const employeeToDelete = {
        id: 1,
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
      };

      // Set initial state
      composable.employees.value = [employeeToDelete];
      composable.currentEmployee.value = employeeToDelete;
      composable.pagination.value.totalItems = 1;

      employeeService.deleteEmployee.mockResolvedValue(true);

      const result = await composable.deleteEmployee(1);

      expect(composable.loadingStates.deleting).toBe(false);
      expect(composable.employees.value).toEqual([]);
      expect(composable.currentEmployee.value).toBeNull();
      expect(composable.pagination.value.totalItems).toBe(0);
      expect(composable.error.value).toBeNull();
      expect(result).toBe(true);
    });

    test('should handle delete employee error', async () => {
      const errorMessage = 'Employee not found';
      employeeService.deleteEmployee.mockRejectedValue(new Error(errorMessage));

      await expect(composable.deleteEmployee(999)).rejects.toThrow(errorMessage);
      
      expect(composable.loadingStates.deleting).toBe(false);
      expect(composable.error.value).toBe(errorMessage);
    });
  });

  describe('Utility Functions', () => {
    test('clearError should clear error state', () => {
      composable.error.value = 'Some error';
      
      composable.clearError();
      
      expect(composable.error.value).toBeNull();
    });

    test('resetPagination should reset pagination to defaults', () => {
      composable.pagination.value = {
        currentPage: 5,
        totalPages: 10,
        totalItems: 100
      };
      
      composable.resetPagination();
      
      expect(composable.pagination.value).toEqual({
        currentPage: 1,
        totalPages: 1,
        totalItems: 0
      });
    });

    test('setCurrentEmployee should set current employee', () => {
      const employee = { id: 1, firstName: 'John', lastName: 'Doe' };
      
      composable.setCurrentEmployee(employee);
      
      expect(composable.currentEmployee.value).toEqual(employee);
    });

    test('setCurrentEmployee should handle invalid input', () => {
      const consoleSpy = jest.spyOn(console, 'warn').mockImplementation();
      
      composable.setCurrentEmployee(null);
      composable.setCurrentEmployee('invalid');
      
      expect(consoleSpy).toHaveBeenCalledTimes(2);
      expect(composable.currentEmployee.value).toBeNull();
      
      consoleSpy.mockRestore();
    });

    test('clearCurrentEmployee should clear current employee', () => {
      composable.currentEmployee.value = { id: 1, firstName: 'John' };
      
      composable.clearCurrentEmployee();
      
      expect(composable.currentEmployee.value).toBeNull();
    });

    test('refreshEmployees should call fetchEmployees with current page', async () => {
      const mockResponse = { employees: [], totalItems: 0, currentPage: 2 };
      employeeService.getEmployees.mockResolvedValue(mockResponse);
      
      composable.pagination.value.currentPage = 2;
      
      await composable.refreshEmployees({ filter: 'test' });
      
      expect(employeeService.getEmployees).toHaveBeenCalledWith(2, { filter: 'test' });
    });
  });

  describe('Computed Properties', () => {
    test('hasEmployees should return true when employees exist', async () => {
      expect(composable.hasEmployees.value).toBe(false);
      
      composable.employees.value = [{ id: 1, firstName: 'John' }];
      await nextTick();
      
      expect(composable.hasEmployees.value).toBe(true);
    });

    test('isLoading should return true when any loading state is active', async () => {
      expect(composable.isLoading.value).toBe(false);
      
      composable.loadingStates.fetching = true;
      await nextTick();
      
      expect(composable.isLoading.value).toBe(true);
      
      composable.loadingStates.fetching = false;
      composable.loadingStates.creating = true;
      await nextTick();
      
      expect(composable.isLoading.value).toBe(true);
    });

    test('hasError should return true when error exists', async () => {
      expect(composable.hasError.value).toBe(false);
      
      composable.error.value = 'Some error';
      await nextTick();
      
      expect(composable.hasError.value).toBe(true);
    });

    test('canLoadMore should return true when more pages available', async () => {
      expect(composable.canLoadMore.value).toBe(false);
      
      composable.pagination.value = {
        currentPage: 1,
        totalPages: 3,
        totalItems: 30
      };
      await nextTick();
      
      expect(composable.canLoadMore.value).toBe(true);
      
      composable.pagination.value.currentPage = 3;
      await nextTick();
      
      expect(composable.canLoadMore.value).toBe(false);
    });
  });

  describe('Error Auto-Clear', () => {
    test('should auto-clear error after timeout', async () => {
      jest.useFakeTimers();
      
      composable.error.value = 'Test error';
      
      // Fast-forward time by 10 seconds
      jest.advanceTimersByTime(10000);
      await nextTick();
      
      expect(composable.error.value).toBeNull();
      
      jest.useRealTimers();
    });
  });

  describe('State Immutability', () => {
    test('returned states should be readonly', () => {
      expect(() => {
        composable.employees.value = [];
      }).toThrow();
      
      expect(() => {
        composable.loading.value = true;
      }).toThrow();
      
      expect(() => {
        composable.error.value = 'error';
      }).toThrow();
    });
  });
});