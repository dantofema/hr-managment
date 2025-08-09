import { http, HttpResponse } from 'msw'
import { mockEmployees, mockPaginatedResponse, mockEmployee, mockEmptyResponse } from '../fixtures/employees.js'

let employeesData = [...mockEmployees]
let nextId = Math.max(...employeesData.map(e => e.id)) + 1

export const handlers = [
  // GET /api/employees - Lista paginada de empleados
  http.get('/api/employees', ({ request }) => {
    const url = new URL(request.url)
    const page = parseInt(url.searchParams.get('page') || '1')
    const limit = parseInt(url.searchParams.get('limit') || '10')
    const search = url.searchParams.get('search')
    const position = url.searchParams.get('position')
    const sortBy = url.searchParams.get('sortBy')
    const sortOrder = url.searchParams.get('sortOrder') || 'asc'

    let filteredEmployees = [...employeesData]

    // Aplicar filtros
    if (search) {
      filteredEmployees = filteredEmployees.filter(emp => 
        emp.firstName.toLowerCase().includes(search.toLowerCase()) ||
        emp.lastName.toLowerCase().includes(search.toLowerCase()) ||
        emp.email.toLowerCase().includes(search.toLowerCase())
      )
    }

    if (position) {
      filteredEmployees = filteredEmployees.filter(emp => 
        emp.position.toLowerCase() === position.toLowerCase()
      )
    }

    // Aplicar ordenamiento
    if (sortBy) {
      filteredEmployees.sort((a, b) => {
        let aValue = a[sortBy]
        let bValue = b[sortBy]
        
        if (typeof aValue === 'string') {
          aValue = aValue.toLowerCase()
          bValue = bValue.toLowerCase()
        }
        
        if (sortOrder === 'desc') {
          return aValue < bValue ? 1 : -1
        }
        return aValue > bValue ? 1 : -1
      })
    }

    // Aplicar paginación
    const startIndex = (page - 1) * limit
    const endIndex = startIndex + limit
    const paginatedEmployees = filteredEmployees.slice(startIndex, endIndex)

    const totalItems = filteredEmployees.length
    const totalPages = Math.ceil(totalItems / limit)

    const response = {
      'hydra:member': paginatedEmployees,
      'hydra:totalItems': totalItems,
      'hydra:view': {
        '@id': `/api/employees?page=${page}`,
        'hydra:first': '/api/employees?page=1',
        'hydra:last': `/api/employees?page=${totalPages}`,
        ...(page < totalPages && { 'hydra:next': `/api/employees?page=${page + 1}` }),
        ...(page > 1 && { 'hydra:previous': `/api/employees?page=${page - 1}` })
      }
    }

    return HttpResponse.json(response)
  }),

  // GET /api/employees/:id - Obtener empleado por ID
  http.get('/api/employees/:id', ({ params }) => {
    const { id } = params
    const employee = employeesData.find(emp => emp.id === parseInt(id))
    
    if (!employee) {
      return new HttpResponse(null, { status: 404 })
    }
    
    return HttpResponse.json(employee)
  }),

  // POST /api/employees - Crear nuevo empleado
  http.post('/api/employees', async ({ request }) => {
    const newEmployee = await request.json()
    
    // Validación básica
    if (!newEmployee.firstName || !newEmployee.lastName || !newEmployee.email) {
      return HttpResponse.json(
        { 
          error: 'Validation failed',
          violations: [
            { propertyPath: 'firstName', message: 'First name is required' },
            { propertyPath: 'lastName', message: 'Last name is required' },
            { propertyPath: 'email', message: 'Email is required' }
          ]
        },
        { status: 400 }
      )
    }

    // Verificar email único
    if (employeesData.some(emp => emp.email === newEmployee.email)) {
      return HttpResponse.json(
        { 
          error: 'Validation failed',
          violations: [
            { propertyPath: 'email', message: 'Email already exists' }
          ]
        },
        { status: 400 }
      )
    }

    const createdEmployee = {
      id: nextId++,
      ...newEmployee,
      hiredAt: newEmployee.hiredAt || new Date().toISOString()
    }
    
    employeesData.push(createdEmployee)
    
    return HttpResponse.json(createdEmployee, { status: 201 })
  }),

  // PUT /api/employees/:id - Actualizar empleado
  http.put('/api/employees/:id', async ({ params, request }) => {
    const { id } = params
    const updatedData = await request.json()
    const employeeIndex = employeesData.findIndex(emp => emp.id === parseInt(id))
    
    if (employeeIndex === -1) {
      return new HttpResponse(null, { status: 404 })
    }

    // Validación básica
    if (!updatedData.firstName || !updatedData.lastName || !updatedData.email) {
      return HttpResponse.json(
        { 
          error: 'Validation failed',
          violations: [
            { propertyPath: 'firstName', message: 'First name is required' },
            { propertyPath: 'lastName', message: 'Last name is required' },
            { propertyPath: 'email', message: 'Email is required' }
          ]
        },
        { status: 400 }
      )
    }

    // Verificar email único (excluyendo el empleado actual)
    if (employeesData.some(emp => emp.email === updatedData.email && emp.id !== parseInt(id))) {
      return HttpResponse.json(
        { 
          error: 'Validation failed',
          violations: [
            { propertyPath: 'email', message: 'Email already exists' }
          ]
        },
        { status: 400 }
      )
    }
    
    employeesData[employeeIndex] = {
      ...employeesData[employeeIndex],
      ...updatedData
    }
    
    return HttpResponse.json(employeesData[employeeIndex])
  }),

  // DELETE /api/employees/:id - Eliminar empleado
  http.delete('/api/employees/:id', ({ params }) => {
    const { id } = params
    const employeeIndex = employeesData.findIndex(emp => emp.id === parseInt(id))
    
    if (employeeIndex === -1) {
      return new HttpResponse(null, { status: 404 })
    }
    
    employeesData.splice(employeeIndex, 1)
    
    return new HttpResponse(null, { status: 204 })
  }),

  // Simulación de errores de red
  http.get('/api/employees/network-error', () => {
    return HttpResponse.error()
  }),

  // Simulación de error 500
  http.get('/api/employees/server-error', () => {
    return new HttpResponse(null, { status: 500 })
  })
]

// Utilidades para tests
export const resetEmployeesData = () => {
  employeesData = [...mockEmployees]
  nextId = Math.max(...employeesData.map(e => e.id)) + 1
}

export const setEmployeesData = (data) => {
  employeesData = [...data]
  nextId = Math.max(...employeesData.map(e => e.id)) + 1
}

export const getEmployeesData = () => [...employeesData]