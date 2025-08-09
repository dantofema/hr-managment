import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import {
  validateFirstName,
  validateLastName,
  validateEmail,
  validatePosition,
  validateSalaryAmount,
  validateSalaryCurrency,
  validateHiredAt,
  validateEmployeeData,
  validateField,
  getValidCurrencies,
  isValidNameFormat,
  isValidEmailFormat,
  isNotFutureDate,
  hasValidDecimalPlaces
} from '@/utils/validators.js'

describe('validators', () => {
  beforeEach(() => {
    vi.spyOn(console, 'warn').mockImplementation(() => {})
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('validateFirstName', () => {
    test('should return error for empty first name', () => {
      expect(validateFirstName('')).toBe('El nombre es obligatorio')
      expect(validateFirstName(null)).toBe('El nombre es obligatorio')
      expect(validateFirstName(undefined)).toBe('El nombre es obligatorio')
      expect(validateFirstName('   ')).toBe('El nombre es obligatorio')
    })

    test('should return error for first name too short', () => {
      expect(validateFirstName('J')).toBe('El nombre debe tener al menos 2 caracteres')
    })

    test('should return error for invalid characters', () => {
      expect(validateFirstName('John123')).toBe('El nombre solo puede contener letras y espacios')
      expect(validateFirstName('John@')).toBe('El nombre solo puede contener letras y espacios')
      expect(validateFirstName('John-Doe')).toBe('El nombre solo puede contener letras y espacios')
    })

    test('should accept valid first names', () => {
      expect(validateFirstName('John')).toBe('')
      expect(validateFirstName('José')).toBe('')
      expect(validateFirstName('María José')).toBe('')
      expect(validateFirstName('Jean-Pierre')).toBe('El nombre solo puede contener letras y espacios') // Should fail with hyphen
      expect(validateFirstName('Jean Pierre')).toBe('') // Should pass with space
    })

    test('should handle accented characters', () => {
      expect(validateFirstName('José')).toBe('')
      expect(validateFirstName('María')).toBe('')
      expect(validateFirstName('Ángel')).toBe('')
      expect(validateFirstName('Iñaki')).toBe('')
    })
  })

  describe('validateLastName', () => {
    test('should return error for empty last name', () => {
      expect(validateLastName('')).toBe('El apellido es obligatorio')
      expect(validateLastName(null)).toBe('El apellido es obligatorio')
      expect(validateLastName(undefined)).toBe('El apellido es obligatorio')
    })

    test('should return error for last name too short', () => {
      expect(validateLastName('D')).toBe('El apellido debe tener al menos 2 caracteres')
    })

    test('should return error for invalid characters', () => {
      expect(validateLastName('Doe123')).toBe('El apellido solo puede contener letras y espacios')
    })

    test('should accept valid last names', () => {
      expect(validateLastName('Doe')).toBe('')
      expect(validateLastName('García López')).toBe('')
      expect(validateLastName('Ñuñez')).toBe('')
    })
  })

  describe('validateEmail', () => {
    test('should return error for empty email', () => {
      expect(validateEmail('')).toBe('El email es obligatorio')
      expect(validateEmail(null)).toBe('El email es obligatorio')
      expect(validateEmail(undefined)).toBe('El email es obligatorio')
    })

    test('should return error for invalid email format', () => {
      expect(validateEmail('invalid-email')).toBe('Ingrese un email válido')
      expect(validateEmail('test@')).toBe('Ingrese un email válido')
      expect(validateEmail('@example.com')).toBe('Ingrese un email válido')
      expect(validateEmail('test@example')).toBe('Ingrese un email válido')
      expect(validateEmail('test.example.com')).toBe('Ingrese un email válido')
    })

    test('should accept valid email formats', () => {
      expect(validateEmail('test@example.com')).toBe('')
      expect(validateEmail('user.name@domain.co.uk')).toBe('')
      expect(validateEmail('test+tag@example.org')).toBe('')
      expect(validateEmail('123@example.com')).toBe('')
    })
  })

  describe('validatePosition', () => {
    test('should return error for empty position', () => {
      expect(validatePosition('')).toBe('La posición es obligatoria')
      expect(validatePosition(null)).toBe('La posición es obligatoria')
      expect(validatePosition(undefined)).toBe('La posición es obligatoria')
    })

    test('should return error for position too long', () => {
      const longPosition = 'A'.repeat(101)
      expect(validatePosition(longPosition)).toBe('La posición no puede exceder 100 caracteres')
    })

    test('should accept valid positions', () => {
      expect(validatePosition('Developer')).toBe('')
      expect(validatePosition('Senior Frontend Developer')).toBe('')
      expect(validatePosition('A'.repeat(100))).toBe('') // Exactly 100 characters
    })
  })

  describe('validateSalaryAmount', () => {
    test('should return error for empty salary', () => {
      expect(validateSalaryAmount('')).toBe('El salario es obligatorio')
      expect(validateSalaryAmount(null)).toBe('El salario es obligatorio')
      expect(validateSalaryAmount(undefined)).toBe('El salario es obligatorio')
    })

    test('should return error for non-positive salary', () => {
      expect(validateSalaryAmount(0)).toBe('El salario debe ser un número positivo')
      expect(validateSalaryAmount(-1000)).toBe('El salario debe ser un número positivo')
      expect(validateSalaryAmount('0')).toBe('El salario debe ser un número positivo')
    })

    test('should return error for invalid number format', () => {
      expect(validateSalaryAmount('abc')).toBe('El salario debe ser un número positivo')
      expect(validateSalaryAmount('12.34.56')).toBe('El salario debe ser un número positivo')
    })

    test('should return error for too many decimal places', () => {
      expect(validateSalaryAmount('50000.123')).toBe('El salario puede tener máximo 2 decimales')
      expect(validateSalaryAmount(50000.123)).toBe('El salario puede tener máximo 2 decimales')
    })

    test('should accept valid salary amounts', () => {
      expect(validateSalaryAmount(50000)).toBe('')
      expect(validateSalaryAmount('50000')).toBe('')
      expect(validateSalaryAmount('50000.50')).toBe('')
      expect(validateSalaryAmount('50000.5')).toBe('')
      expect(validateSalaryAmount(50000.50)).toBe('')
    })
  })

  describe('validateSalaryCurrency', () => {
    test('should return error for empty currency', () => {
      expect(validateSalaryCurrency('')).toBe('La moneda es obligatoria')
      expect(validateSalaryCurrency(null)).toBe('La moneda es obligatoria')
      expect(validateSalaryCurrency(undefined)).toBe('La moneda es obligatoria')
    })

    test('should return error for invalid currency', () => {
      expect(validateSalaryCurrency('INVALID')).toBe('Seleccione una moneda válida')
      expect(validateSalaryCurrency('MXN')).toBe('Seleccione una moneda válida')
    })

    test('should accept valid currencies', () => {
      expect(validateSalaryCurrency('EUR')).toBe('')
      expect(validateSalaryCurrency('USD')).toBe('')
      expect(validateSalaryCurrency('GBP')).toBe('')
      expect(validateSalaryCurrency('CAD')).toBe('')
      expect(validateSalaryCurrency('AUD')).toBe('')
      expect(validateSalaryCurrency('JPY')).toBe('')
    })
  })

  describe('validateHiredAt', () => {
    test('should return error for empty date', () => {
      expect(validateHiredAt('')).toBe('La fecha de contratación es obligatoria')
      expect(validateHiredAt(null)).toBe('La fecha de contratación es obligatoria')
      expect(validateHiredAt(undefined)).toBe('La fecha de contratación es obligatoria')
    })

    test('should return error for invalid date format', () => {
      expect(validateHiredAt('invalid-date')).toBe('Ingrese una fecha válida')
      expect(validateHiredAt('2023-13-01')).toBe('Ingrese una fecha válida')
      expect(validateHiredAt('2023-02-30')).toBe('Ingrese una fecha válida')
    })

    test('should return error for future date', () => {
      const futureDate = new Date()
      futureDate.setDate(futureDate.getDate() + 1)
      const futureDateString = futureDate.toISOString().split('T')[0]
      
      expect(validateHiredAt(futureDateString)).toBe('La fecha no puede ser futura')
    })

    test('should accept valid dates', () => {
      const today = new Date().toISOString().split('T')[0]
      const pastDate = '2023-01-15'
      
      expect(validateHiredAt(today)).toBe('')
      expect(validateHiredAt(pastDate)).toBe('')
    })
  })

  describe('validateEmployeeData', () => {
    const validEmployeeData = {
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      position: 'Developer',
      salaryAmount: 50000,
      salaryCurrency: 'EUR',
      hiredAt: '2023-01-15'
    }

    test('should return valid for correct employee data', () => {
      const result = validateEmployeeData(validEmployeeData)
      
      expect(result.isValid).toBe(true)
      expect(Object.values(result.errors).every(error => error === '')).toBe(true)
    })

    test('should return invalid for incorrect employee data', () => {
      const invalidData = {
        firstName: '',
        lastName: 'D',
        email: 'invalid-email',
        position: '',
        salaryAmount: -1000,
        salaryCurrency: 'INVALID',
        hiredAt: '2025-12-31'
      }

      const result = validateEmployeeData(invalidData)
      
      expect(result.isValid).toBe(false)
      expect(result.errors.firstName).toBeTruthy()
      expect(result.errors.lastName).toBeTruthy()
      expect(result.errors.email).toBeTruthy()
      expect(result.errors.position).toBeTruthy()
      expect(result.errors.salaryAmount).toBeTruthy()
      expect(result.errors.salaryCurrency).toBeTruthy()
      expect(result.errors.hiredAt).toBeTruthy()
    })

    test('should validate individual fields correctly', () => {
      const partiallyInvalidData = {
        ...validEmployeeData,
        firstName: '',
        email: 'invalid-email'
      }

      const result = validateEmployeeData(partiallyInvalidData)
      
      expect(result.isValid).toBe(false)
      expect(result.errors.firstName).toBeTruthy()
      expect(result.errors.email).toBeTruthy()
      expect(result.errors.lastName).toBe('')
      expect(result.errors.position).toBe('')
    })
  })

  describe('validateField', () => {
    test('should validate individual fields correctly', () => {
      expect(validateField('firstName', 'John')).toBe('')
      expect(validateField('firstName', '')).toBe('El nombre es obligatorio')
      expect(validateField('email', 'test@example.com')).toBe('')
      expect(validateField('email', 'invalid')).toBe('Ingrese un email válido')
    })

    test('should handle unknown field names', () => {
      expect(validateField('unknownField', 'value')).toBe('')
      expect(console.warn).toHaveBeenCalledWith('No validator found for field: unknownField')
    })
  })

  describe('getValidCurrencies', () => {
    test('should return array of valid currencies', () => {
      const currencies = getValidCurrencies()
      
      expect(Array.isArray(currencies)).toBe(true)
      expect(currencies).toContain('EUR')
      expect(currencies).toContain('USD')
      expect(currencies).toContain('GBP')
      expect(currencies).toContain('CAD')
      expect(currencies).toContain('AUD')
      expect(currencies).toContain('JPY')
      expect(currencies).toHaveLength(6)
    })
  })

  describe('isValidNameFormat', () => {
    test('should return true for valid name formats', () => {
      expect(isValidNameFormat('John')).toBe(true)
      expect(isValidNameFormat('José María')).toBe(true)
      expect(isValidNameFormat('Ángel')).toBe(true)
      expect(isValidNameFormat('Iñaki')).toBe(true)
    })

    test('should return false for invalid name formats', () => {
      expect(isValidNameFormat('John123')).toBe(false)
      expect(isValidNameFormat('John@')).toBe(false)
      expect(isValidNameFormat('John-Doe')).toBe(false)
      expect(isValidNameFormat('')).toBe(false)
    })
  })

  describe('isValidEmailFormat', () => {
    test('should return true for valid email formats', () => {
      expect(isValidEmailFormat('test@example.com')).toBe(true)
      expect(isValidEmailFormat('user.name@domain.co.uk')).toBe(true)
      expect(isValidEmailFormat('test+tag@example.org')).toBe(true)
    })

    test('should return false for invalid email formats', () => {
      expect(isValidEmailFormat('invalid-email')).toBe(false)
      expect(isValidEmailFormat('test@')).toBe(false)
      expect(isValidEmailFormat('@example.com')).toBe(false)
      expect(isValidEmailFormat('')).toBe(false)
    })
  })

  describe('isNotFutureDate', () => {
    test('should return true for past and present dates', () => {
      const today = new Date()
      const yesterday = new Date()
      yesterday.setDate(yesterday.getDate() - 1)
      
      expect(isNotFutureDate(today)).toBe(true)
      expect(isNotFutureDate(yesterday)).toBe(true)
      expect(isNotFutureDate('2023-01-15')).toBe(true)
    })

    test('should return false for future dates', () => {
      const tomorrow = new Date()
      tomorrow.setDate(tomorrow.getDate() + 1)
      
      expect(isNotFutureDate(tomorrow)).toBe(false)
      
      const futureDate = new Date()
      futureDate.setFullYear(futureDate.getFullYear() + 1)
      expect(isNotFutureDate(futureDate)).toBe(false)
    })

    test('should handle string dates', () => {
      const today = new Date().toISOString().split('T')[0]
      const futureDate = new Date()
      futureDate.setDate(futureDate.getDate() + 1)
      const futureDateString = futureDate.toISOString().split('T')[0]
      
      expect(isNotFutureDate(today)).toBe(true)
      expect(isNotFutureDate(futureDateString)).toBe(false)
    })
  })

  describe('hasValidDecimalPlaces', () => {
    test('should return true for valid decimal places', () => {
      expect(hasValidDecimalPlaces(50000)).toBe(true)
      expect(hasValidDecimalPlaces('50000')).toBe(true)
      expect(hasValidDecimalPlaces('50000.5')).toBe(true)
      expect(hasValidDecimalPlaces('50000.50')).toBe(true)
      expect(hasValidDecimalPlaces(50000.50)).toBe(true)
    })

    test('should return false for invalid decimal places', () => {
      expect(hasValidDecimalPlaces('50000.123')).toBe(false)
      expect(hasValidDecimalPlaces(50000.123)).toBe(false)
      expect(hasValidDecimalPlaces('50000.1234')).toBe(false)
    })

    test('should handle edge cases', () => {
      expect(hasValidDecimalPlaces('0')).toBe(true)
      expect(hasValidDecimalPlaces('0.1')).toBe(true)
      expect(hasValidDecimalPlaces('0.12')).toBe(true)
      expect(hasValidDecimalPlaces('0.123')).toBe(false)
    })
  })

  describe('edge cases and boundary conditions', () => {
    test('should handle whitespace in names', () => {
      expect(validateFirstName('  John  ')).toBe('')
      expect(validateLastName('  Doe  ')).toBe('')
    })

    test('should handle whitespace in email', () => {
      expect(validateEmail('  test@example.com  ')).toBe('')
    })

    test('should handle whitespace in position', () => {
      expect(validatePosition('  Developer  ')).toBe('')
    })

    test('should handle boundary values for position length', () => {
      expect(validatePosition('A'.repeat(99))).toBe('')
      expect(validatePosition('A'.repeat(100))).toBe('')
      expect(validatePosition('A'.repeat(101))).toBe('La posición no puede exceder 100 caracteres')
    })

    test('should handle boundary values for name length', () => {
      expect(validateFirstName('A')).toBe('El nombre debe tener al menos 2 caracteres')
      expect(validateFirstName('AB')).toBe('')
      expect(validateLastName('A')).toBe('El apellido debe tener al menos 2 caracteres')
      expect(validateLastName('AB')).toBe('')
    })

    test('should handle various salary formats', () => {
      expect(validateSalaryAmount(1)).toBe('')
      expect(validateSalaryAmount('1')).toBe('')
      expect(validateSalaryAmount(1.0)).toBe('')
      expect(validateSalaryAmount('1.0')).toBe('')
      expect(validateSalaryAmount(1.00)).toBe('')
      expect(validateSalaryAmount('1.00')).toBe('')
    })
  })
})