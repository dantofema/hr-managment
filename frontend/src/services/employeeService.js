/**
 * Employee Service - API abstraction layer for employee operations
 * Handles all CRUD operations with the Symfony API Platform backend
 * 
 * @author HR System
 * @version 1.0.0
 */

// Base configuration
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
const EMPLOYEES_ENDPOINT = '/employees';

// Default headers for API requests
const defaultHeaders = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
};

/**
 * Build URL with endpoint and optional parameters
 * @param {string} endpoint - API endpoint
 * @param {Object} params - Query parameters
 * @returns {string} Complete URL
 */
const buildUrl = (endpoint, params = {}) => {
  const url = new URL(`${API_BASE_URL}${endpoint}`);
  
  Object.keys(params).forEach(key => {
    if (params[key] !== undefined && params[key] !== null) {
      url.searchParams.append(key, params[key]);
    }
  });
  
  return url.toString();
};

/**
 * Handle and standardize API errors
 * @param {Error} error - Original error object
 * @returns {Object} Standardized error object
 */
const handleApiError = (error) => {
  console.error('API Error:', error);
  
  // Network errors
  if (!error.response && error.message.includes('fetch')) {
    return {
      type: 'NETWORK_ERROR',
      message: 'Error de conexión. Verifique su conexión a internet.',
      details: { originalError: error.message },
      status: null
    };
  }
  
  // HTTP errors
  if (error.status) {
    switch (error.status) {
      case 404:
        return {
          type: 'NOT_FOUND',
          message: 'El empleado solicitado no fue encontrado.',
          details: { originalError: error.message },
          status: 404
        };
      case 422:
        return {
          type: 'VALIDATION_ERROR',
          message: 'Los datos proporcionados no son válidos.',
          details: { originalError: error.message, validationErrors: error.details },
          status: 422
        };
      case 500:
        return {
          type: 'SERVER_ERROR',
          message: 'Error interno del servidor. Intente nuevamente más tarde.',
          details: { originalError: error.message },
          status: 500
        };
      default:
        return {
          type: 'SERVER_ERROR',
          message: `Error del servidor: ${error.status}`,
          details: { originalError: error.message },
          status: error.status
        };
    }
  }
  
  // Generic errors
  return {
    type: 'SERVER_ERROR',
    message: 'Ha ocurrido un error inesperado.',
    details: { originalError: error.message },
    status: null
  };
};

/**
 * Transform API employee data to frontend format
 * @param {Object} apiEmployee - Employee data from API
 * @returns {Object} Transformed employee object
 */
const transformEmployee = (apiEmployee) => {
  if (!apiEmployee) return null;
  
  return {
    id: apiEmployee.id,
    fullName: `${apiEmployee.firstName} ${apiEmployee.lastName}`,
    firstName: apiEmployee.firstName,
    lastName: apiEmployee.lastName,
    email: apiEmployee.email,
    position: apiEmployee.position,
    salary: {
      amount: apiEmployee.salaryAmount,
      currency: apiEmployee.salaryCurrency
    },
    salaryAmount: apiEmployee.salaryAmount, // Keep original format for compatibility
    salaryCurrency: apiEmployee.salaryCurrency,
    hiredAt: new Date(apiEmployee.hiredAt)
  };
};

/**
 * Validate employee data before sending to API
 * @param {Object} data - Employee data to validate
 * @returns {Object} Validation result
 */
const validateEmployeeData = (data) => {
  const errors = [];
  
  if (!data.firstName || data.firstName.trim().length === 0) {
    errors.push('El nombre es requerido');
  }
  
  if (!data.lastName || data.lastName.trim().length === 0) {
    errors.push('El apellido es requerido');
  }
  
  if (!data.email || data.email.trim().length === 0) {
    errors.push('El email es requerido');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.push('El formato del email no es válido');
  }
  
  if (!data.position || data.position.trim().length === 0) {
    errors.push('La posición es requerida');
  }
  
  if (!data.salaryAmount || data.salaryAmount <= 0) {
    errors.push('El salario debe ser mayor a 0');
  }
  
  if (!data.salaryCurrency || data.salaryCurrency.trim().length === 0) {
    errors.push('La moneda del salario es requerida');
  }
  
  if (!data.hiredAt) {
    errors.push('La fecha de contratación es requerida');
  }
  
  return {
    isValid: errors.length === 0,
    errors
  };
};

/**
 * Extract page number from API Platform URL
 * @param {string} url - API Platform URL
 * @returns {number} Page number
 */
const extractPageFromUrl = (url) => {
  if (!url) return 1;
  const match = url.match(/page=(\d+)/);
  return match ? parseInt(match[1], 10) : 1;
};

/**
 * Calculate total pages from API response
 * @param {Object} response - API Platform response
 * @returns {number} Total pages
 */
const calculateTotalPages = (response) => {
  const totalItems = response['hydra:totalItems'] || 0;
  const itemsPerPage = 30; // API Platform default
  return Math.ceil(totalItems / itemsPerPage);
};

/**
 * Fetch employees with pagination and optional filters
 * @param {number} page - Page number (default: 1)
 * @param {Object} filters - Optional filters
 * @returns {Promise<Object>} Promise with employees data and pagination info
 */
const fetchEmployees = async (page = 1, filters = {}) => {
  try {
    const params = { page, ...filters };
    const url = buildUrl(EMPLOYEES_ENDPOINT, params);
    
    const response = await fetch(url, {
      method: 'GET',
      headers: defaultHeaders
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw {
        status: response.status,
        message: response.statusText,
        details: errorData
      };
    }
    
    const data = await response.json();
    
    // Transform API Platform response
    const employees = (data['hydra:member'] || []).map(transformEmployee);
    
    return {
      data: employees,
      pagination: {
        currentPage: extractPageFromUrl(data['hydra:view']?.['@id']),
        totalItems: data['hydra:totalItems'] || 0,
        totalPages: calculateTotalPages(data)
      }
    };
    
  } catch (error) {
    throw handleApiError(error);
  }
};

/**
 * Get a single employee by ID
 * @param {number} id - Employee ID
 * @returns {Promise<Object>} Promise with employee data
 */
const getEmployee = async (id) => {
  try {
    if (!id) {
      throw new Error('Employee ID is required');
    }
    
    const url = buildUrl(`${EMPLOYEES_ENDPOINT}/${id}`);
    
    const response = await fetch(url, {
      method: 'GET',
      headers: defaultHeaders
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw {
        status: response.status,
        message: response.statusText,
        details: errorData
      };
    }
    
    const data = await response.json();
    return transformEmployee(data);
    
  } catch (error) {
    throw handleApiError(error);
  }
};

/**
 * Create a new employee
 * @param {Object} employeeData - Employee data
 * @returns {Promise<Object>} Promise with created employee data
 */
const createEmployee = async (employeeData) => {
  try {
    // Validate data before sending
    const validation = validateEmployeeData(employeeData);
    if (!validation.isValid) {
      throw {
        status: 422,
        message: 'Validation failed',
        details: { validationErrors: validation.errors }
      };
    }
    
    const url = buildUrl(EMPLOYEES_ENDPOINT);
    
    const response = await fetch(url, {
      method: 'POST',
      headers: defaultHeaders,
      body: JSON.stringify(employeeData)
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw {
        status: response.status,
        message: response.statusText,
        details: errorData
      };
    }
    
    const data = await response.json();
    return transformEmployee(data);
    
  } catch (error) {
    throw handleApiError(error);
  }
};

/**
 * Update an existing employee
 * @param {number} id - Employee ID
 * @param {Object} employeeData - Updated employee data
 * @returns {Promise<Object>} Promise with updated employee data
 */
const updateEmployee = async (id, employeeData) => {
  try {
    if (!id) {
      throw new Error('Employee ID is required');
    }
    
    // Validate data before sending
    const validation = validateEmployeeData(employeeData);
    if (!validation.isValid) {
      throw {
        status: 422,
        message: 'Validation failed',
        details: { validationErrors: validation.errors }
      };
    }
    
    const url = buildUrl(`${EMPLOYEES_ENDPOINT}/${id}`);
    
    const response = await fetch(url, {
      method: 'PUT',
      headers: defaultHeaders,
      body: JSON.stringify(employeeData)
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw {
        status: response.status,
        message: response.statusText,
        details: errorData
      };
    }
    
    const data = await response.json();
    return transformEmployee(data);
    
  } catch (error) {
    throw handleApiError(error);
  }
};

/**
 * Delete an employee
 * @param {number} id - Employee ID
 * @returns {Promise<Object>} Promise with deletion confirmation
 */
const deleteEmployee = async (id) => {
  try {
    if (!id) {
      throw new Error('Employee ID is required');
    }
    
    const url = buildUrl(`${EMPLOYEES_ENDPOINT}/${id}`);
    
    const response = await fetch(url, {
      method: 'DELETE',
      headers: defaultHeaders
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw {
        status: response.status,
        message: response.statusText,
        details: errorData
      };
    }
    
    return {
      success: true,
      message: 'Empleado eliminado correctamente',
      id: id
    };
    
  } catch (error) {
    throw handleApiError(error);
  }
};

// Export service object
const employeeService = {
  fetchEmployees,
  getEmployee,
  createEmployee,
  updateEmployee,
  deleteEmployee,
  // Export helper functions for testing
  buildUrl,
  handleApiError,
  transformEmployee,
  validateEmployeeData
};

export default employeeService;