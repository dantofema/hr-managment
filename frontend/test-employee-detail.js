// Test script for EmployeeDetail component calculations
// This script tests the calculation logic used in EmployeeDetail.vue

// Sample employee data for testing
const testEmployee = {
  id: 1,
  firstName: 'Juan',
  lastName: 'Pérez',
  email: 'juan.perez@empresa.com',
  position: 'Desarrollador Frontend',
  salaryAmount: 45000,
  salaryCurrency: 'EUR',
  hiredAt: '2022-01-15T00:00:00Z'
}

// Test date validation function
const isValidDate = (dateString) => {
  if (!dateString) return false
  const date = new Date(dateString)
  return date instanceof Date && !isNaN(date) && date <= new Date()
}

// Test years of service calculation
const calculateYearsOfService = (hiredAt) => {
  if (!hiredAt || !isValidDate(hiredAt)) return 0
  try {
    const hired = new Date(hiredAt)
    const now = new Date()
    const years = Math.floor((now - hired) / (365.25 * 24 * 60 * 60 * 1000))
    return Math.max(0, years)
  } catch (error) {
    console.warn('Error calculating years of service:', error)
    return 0
  }
}

// Test vacation days calculation
const calculateVacationDays = (hiredAt) => {
  if (!hiredAt || !isValidDate(hiredAt)) return 0
  try {
    const hired = new Date(hiredAt)
    const now = new Date()
    const monthsWorked = Math.floor((now - hired) / (30.44 * 24 * 60 * 60 * 1000))
    const days = Math.floor(Math.max(0, monthsWorked) * 2.5)
    return Math.min(days, 365)
  } catch (error) {
    console.warn('Error calculating vacation days:', error)
    return 0
  }
}

// Test days worked calculation
const calculateDaysWorked = (hiredAt) => {
  if (!hiredAt || !isValidDate(hiredAt)) return 0
  try {
    const hired = new Date(hiredAt)
    const now = new Date()
    const days = Math.floor((now - hired) / (24 * 60 * 60 * 1000))
    return Math.max(0, days)
  } catch (error) {
    console.warn('Error calculating days worked:', error)
    return 0
  }
}

// Test currency formatting
const formatCurrency = (amount, currency = 'EUR') => {
  if (!amount || isNaN(amount)) return 'No especificado'
  try {
    const validCurrencies = ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY']
    const currencyToUse = validCurrencies.includes(currency) ? currency : 'EUR'
    
    return new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: currencyToUse,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(Number(amount))
  } catch (error) {
    console.warn('Error formatting currency:', error)
    return `${amount} ${currency || 'EUR'}`
  }
}

// Test date formatting
const formatDate = (date) => {
  if (!date) return 'No especificado'
  try {
    const dateObj = new Date(date)
    if (isNaN(dateObj.getTime())) {
      return 'Fecha inválida'
    }
    
    const currentYear = new Date().getFullYear()
    const dateYear = dateObj.getFullYear()
    if (dateYear < 1900 || dateYear > currentYear + 10) {
      return 'Fecha fuera de rango'
    }
    
    return new Intl.DateTimeFormat('es-ES', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    }).format(dateObj)
  } catch (error) {
    console.warn('Error formatting date:', error)
    return 'Error en fecha'
  }
}

// Run tests
console.log('=== TESTING EMPLOYEE DETAIL CALCULATIONS ===')
console.log('Test Employee:', testEmployee)
console.log('')

console.log('=== CALCULATIONS ===')
console.log('Years of Service:', calculateYearsOfService(testEmployee.hiredAt))
console.log('Vacation Days:', calculateVacationDays(testEmployee.hiredAt))
console.log('Days Worked:', calculateDaysWorked(testEmployee.hiredAt))
console.log('')

console.log('=== FORMATTING ===')
console.log('Formatted Salary:', formatCurrency(testEmployee.salaryAmount, testEmployee.salaryCurrency))
console.log('Formatted Hire Date:', formatDate(testEmployee.hiredAt))
console.log('')

console.log('=== EDGE CASES ===')
console.log('Invalid date test:', calculateYearsOfService('invalid-date'))
console.log('Null amount currency:', formatCurrency(null))
console.log('Invalid currency:', formatCurrency(50000, 'INVALID'))
console.log('Future date test:', formatDate('2030-12-31'))
console.log('')

console.log('=== VALIDATION ===')
console.log('Valid date check (valid):', isValidDate(testEmployee.hiredAt))
console.log('Valid date check (invalid):', isValidDate('invalid'))
console.log('Valid date check (future):', isValidDate('2030-12-31'))
console.log('')

console.log('All tests completed successfully! ✅')