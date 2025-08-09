/**
 * Simple test runner for employeeService
 * Validates core functionality without a testing framework
 */

import employeeService from './employeeService.js';

// Test results tracking
let testsPassed = 0;
let testsFailed = 0;

// Simple assertion function
function assert(condition, message) {
  if (condition) {
    console.log(`✅ PASS: ${message}`);
    testsPassed++;
  } else {
    console.error(`❌ FAIL: ${message}`);
    testsFailed++;
  }
}

// Test helper functions
function testHelperFunctions() {
  console.log('\n=== Testing Helper Functions ===');
  
  // Test buildUrl
  const url1 = employeeService.buildUrl('/employees');
  assert(url1 === 'http://localhost:8000/api/employees', 'buildUrl without params');
  
  const url2 = employeeService.buildUrl('/employees', { page: 1 });
  assert(url2.includes('page=1'), 'buildUrl with params');
  
  // Test validateEmployeeData
  const validData = {
    firstName: 'John',
    lastName: 'Doe',
    email: 'john@example.com',
    position: 'Developer',
    salaryAmount: 50000,
    salaryCurrency: 'EUR',
    hiredAt: '2024-01-15'
  };
  
  const validation = employeeService.validateEmployeeData(validData);
  assert(validation.isValid === true, 'validateEmployeeData with valid data');
  
  const invalidData = { firstName: '', email: 'invalid' };
  const invalidValidation = employeeService.validateEmployeeData(invalidData);
  assert(invalidValidation.isValid === false, 'validateEmployeeData with invalid data');
  
  // Test transformEmployee
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
  assert(transformed.fullName === 'John Doe', 'transformEmployee creates fullName');
  assert(transformed.salary.amount === 50000, 'transformEmployee creates salary object');
  assert(transformed.hiredAt instanceof Date, 'transformEmployee converts date');
  
  // Test handleApiError
  const networkError = new Error('fetch failed');
  const handledError = employeeService.handleApiError(networkError);
  assert(handledError.type === 'NETWORK_ERROR', 'handleApiError handles network errors');
  
  const notFoundError = { status: 404, message: 'Not Found' };
  const handled404 = employeeService.handleApiError(notFoundError);
  assert(handled404.type === 'NOT_FOUND', 'handleApiError handles 404 errors');
}

// Test service structure
function testServiceStructure() {
  console.log('\n=== Testing Service Structure ===');
  
  assert(typeof employeeService.fetchEmployees === 'function', 'fetchEmployees function exists');
  assert(typeof employeeService.getEmployee === 'function', 'getEmployee function exists');
  assert(typeof employeeService.createEmployee === 'function', 'createEmployee function exists');
  assert(typeof employeeService.updateEmployee === 'function', 'updateEmployee function exists');
  assert(typeof employeeService.deleteEmployee === 'function', 'deleteEmployee function exists');
  
  // Test helper functions are exported
  assert(typeof employeeService.buildUrl === 'function', 'buildUrl helper function exists');
  assert(typeof employeeService.handleApiError === 'function', 'handleApiError helper function exists');
  assert(typeof employeeService.transformEmployee === 'function', 'transformEmployee helper function exists');
  assert(typeof employeeService.validateEmployeeData === 'function', 'validateEmployeeData helper function exists');
}

// Test validation edge cases
function testValidationEdgeCases() {
  console.log('\n=== Testing Validation Edge Cases ===');
  
  // Test empty strings
  const emptyData = {
    firstName: '   ',
    lastName: '   ',
    email: '   ',
    position: '   ',
    salaryAmount: 0,
    salaryCurrency: '   ',
    hiredAt: null
  };
  
  const validation = employeeService.validateEmployeeData(emptyData);
  assert(validation.isValid === false, 'Validation fails for empty/whitespace strings');
  assert(validation.errors.length > 0, 'Validation returns error messages');
  
  // Test invalid email formats
  const invalidEmails = ['invalid', '@domain.com', 'user@', 'user@domain'];
  invalidEmails.forEach(email => {
    const result = employeeService.validateEmployeeData({
      firstName: 'Test',
      lastName: 'User',
      email: email,
      position: 'Developer',
      salaryAmount: 50000,
      salaryCurrency: 'EUR',
      hiredAt: '2024-01-15'
    });
    assert(result.isValid === false, `Invalid email format rejected: ${email}`);
  });
  
  // Test negative salary
  const negativeSalary = {
    firstName: 'Test',
    lastName: 'User',
    email: 'test@example.com',
    position: 'Developer',
    salaryAmount: -1000,
    salaryCurrency: 'EUR',
    hiredAt: '2024-01-15'
  };
  
  const negativeValidation = employeeService.validateEmployeeData(negativeSalary);
  assert(negativeValidation.isValid === false, 'Negative salary rejected');
}

// Test error handling scenarios
function testErrorHandling() {
  console.log('\n=== Testing Error Handling ===');
  
  // Test different error types
  const errors = [
    { status: 400, expected: 'SERVER_ERROR' },
    { status: 401, expected: 'SERVER_ERROR' },
    { status: 403, expected: 'SERVER_ERROR' },
    { status: 404, expected: 'NOT_FOUND' },
    { status: 422, expected: 'VALIDATION_ERROR' },
    { status: 500, expected: 'SERVER_ERROR' },
    { status: 503, expected: 'SERVER_ERROR' }
  ];
  
  errors.forEach(({ status, expected }) => {
    const error = { status, message: `Error ${status}` };
    const handled = employeeService.handleApiError(error);
    assert(handled.type === expected, `Status ${status} maps to ${expected}`);
    assert(handled.status === status, `Status ${status} preserved in error`);
  });
  
  // Test generic error
  const genericError = new Error('Something went wrong');
  const handledGeneric = employeeService.handleApiError(genericError);
  assert(handledGeneric.type === 'SERVER_ERROR', 'Generic errors map to SERVER_ERROR');
}

// Run all tests
async function runTests() {
  console.log('🧪 Starting employeeService Tests...\n');
  
  try {
    testServiceStructure();
    testHelperFunctions();
    testValidationEdgeCases();
    testErrorHandling();
    
    console.log('\n=== Test Results ===');
    console.log(`✅ Tests Passed: ${testsPassed}`);
    console.log(`❌ Tests Failed: ${testsFailed}`);
    console.log(`📊 Total Tests: ${testsPassed + testsFailed}`);
    
    if (testsFailed === 0) {
      console.log('\n🎉 All tests passed! employeeService is working correctly.');
    } else {
      console.log('\n⚠️  Some tests failed. Please review the implementation.');
    }
    
  } catch (error) {
    console.error('❌ Test runner failed:', error);
  }
}

// Export for potential use in other contexts
export { runTests };

// Run tests if this file is executed directly
if (import.meta.url === `file://${process.argv[1]}`) {
  runTests();
}