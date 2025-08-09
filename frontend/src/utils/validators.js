/**
 * Validation utilities for employee data
 * Extracted from EmployeeForm component for reusability and testing
 */

/**
 * Validate first name
 * @param {string} value - First name value
 * @returns {string} Error message or empty string if valid
 */
export const validateFirstName = (value) => {
  if (!value || value.trim().length === 0) {
    return 'El nombre es obligatorio'
  }
  if (value.trim().length < 2) {
    return 'El nombre debe tener al menos 2 caracteres'
  }
  if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value.trim())) {
    return 'El nombre solo puede contener letras y espacios'
  }
  return ''
}

/**
 * Validate last name
 * @param {string} value - Last name value
 * @returns {string} Error message or empty string if valid
 */
export const validateLastName = (value) => {
  if (!value || value.trim().length === 0) {
    return 'El apellido es obligatorio'
  }
  if (value.trim().length < 2) {
    return 'El apellido debe tener al menos 2 caracteres'
  }
  if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value.trim())) {
    return 'El apellido solo puede contener letras y espacios'
  }
  return ''
}

/**
 * Validate email address
 * @param {string} value - Email value
 * @returns {string} Error message or empty string if valid
 */
export const validateEmail = (value) => {
  if (!value || value.trim().length === 0) {
    return 'El email es obligatorio'
  }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(value.trim())) {
    return 'Ingrese un email válido'
  }
  return ''
}

/**
 * Validate position
 * @param {string} value - Position value
 * @returns {string} Error message or empty string if valid
 */
export const validatePosition = (value) => {
  if (!value || value.trim().length === 0) {
    return 'La posición es obligatoria'
  }
  if (value.trim().length > 100) {
    return 'La posición no puede exceder 100 caracteres'
  }
  return ''
}

/**
 * Validate salary amount
 * @param {number|string} value - Salary amount value
 * @returns {string} Error message or empty string if valid
 */
export const validateSalaryAmount = (value) => {
  if (value === null || value === undefined || value === '') {
    return 'El salario es obligatorio'
  }
  const numValue = Number(value)
  if (isNaN(numValue) || numValue <= 0) {
    return 'El salario debe ser un número positivo'
  }
  // Validar máximo 2 decimales
  if (!/^\d+(\.\d{1,2})?$/.test(value.toString())) {
    return 'El salario puede tener máximo 2 decimales'
  }
  return ''
}

/**
 * Validate salary currency
 * @param {string} value - Currency value
 * @returns {string} Error message or empty string if valid
 */
export const validateSalaryCurrency = (value) => {
  if (!value || value.trim().length === 0) {
    return 'La moneda es obligatoria'
  }
  const validCurrencies = ['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'JPY']
  if (!validCurrencies.includes(value)) {
    return 'Seleccione una moneda válida'
  }
  return ''
}

/**
 * Validate hired date
 * @param {string} value - Date value in YYYY-MM-DD format
 * @returns {string} Error message or empty string if valid
 */
export const validateHiredAt = (value) => {
  if (!value || value.trim().length === 0) {
    return 'La fecha de contratación es obligatoria'
  }
  const selectedDate = new Date(value)
  const today = new Date()
  today.setHours(23, 59, 59, 999) // Final del día actual
  
  if (isNaN(selectedDate.getTime())) {
    return 'Ingrese una fecha válida'
  }
  if (selectedDate > today) {
    return 'La fecha no puede ser futura'
  }
  return ''
}

/**
 * Validate all employee fields
 * @param {Object} data - Employee data object
 * @returns {Object} Validation result with isValid boolean and errors object
 */
export const validateEmployeeData = (data) => {
  const errors = {
    firstName: validateFirstName(data.firstName),
    lastName: validateLastName(data.lastName),
    email: validateEmail(data.email),
    position: validatePosition(data.position),
    salaryAmount: validateSalaryAmount(data.salaryAmount),
    salaryCurrency: validateSalaryCurrency(data.salaryCurrency),
    hiredAt: validateHiredAt(data.hiredAt)
  }

  const isValid = Object.values(errors).every(error => error === '')

  return {
    isValid,
    errors
  }
}

/**
 * Validate a single field
 * @param {string} fieldName - Name of the field to validate
 * @param {any} value - Value to validate
 * @returns {string} Error message or empty string if valid
 */
export const validateField = (fieldName, value) => {
  const validators = {
    firstName: validateFirstName,
    lastName: validateLastName,
    email: validateEmail,
    position: validatePosition,
    salaryAmount: validateSalaryAmount,
    salaryCurrency: validateSalaryCurrency,
    hiredAt: validateHiredAt
  }

  const validator = validators[fieldName]
  if (!validator) {
    console.warn(`No validator found for field: ${fieldName}`)
    return ''
  }

  return validator(value)
}

/**
 * Get list of valid currencies
 * @returns {Array} Array of valid currency codes
 */
export const getValidCurrencies = () => {
  return ['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'JPY']
}

/**
 * Check if a string contains only letters and spaces (including accented characters)
 * @param {string} value - String to check
 * @returns {boolean} True if valid, false otherwise
 */
export const isValidNameFormat = (value) => {
  return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value)
}

/**
 * Check if email format is valid
 * @param {string} email - Email to validate
 * @returns {boolean} True if valid, false otherwise
 */
export const isValidEmailFormat = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

/**
 * Check if date is not in the future
 * @param {string|Date} date - Date to check
 * @returns {boolean} True if not in future, false otherwise
 */
export const isNotFutureDate = (date) => {
  const selectedDate = new Date(date)
  const today = new Date()
  today.setHours(23, 59, 59, 999)
  
  return selectedDate <= today
}

/**
 * Check if salary has valid decimal places (max 2)
 * @param {number|string} salary - Salary to check
 * @returns {boolean} True if valid, false otherwise
 */
export const hasValidDecimalPlaces = (salary) => {
  return /^\d+(\.\d{1,2})?$/.test(salary.toString())
}