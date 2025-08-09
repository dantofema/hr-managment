export const mockEmployee = {
  id: 1,
  firstName: 'John',
  lastName: 'Doe',
  email: 'john.doe@example.com',
  position: 'Developer',
  salaryAmount: 50000,
  salaryCurrency: 'EUR',
  hiredAt: '2023-01-15T00:00:00+00:00'
}

export const mockEmployees = [
  mockEmployee,
  {
    id: 2,
    firstName: 'Jane',
    lastName: 'Smith',
    email: 'jane.smith@example.com',
    position: 'Designer',
    salaryAmount: 45000,
    salaryCurrency: 'EUR',
    hiredAt: '2023-02-01T00:00:00+00:00'
  },
  {
    id: 3,
    firstName: 'Bob',
    lastName: 'Johnson',
    email: 'bob.johnson@example.com',
    position: 'Manager',
    salaryAmount: 65000,
    salaryCurrency: 'EUR',
    hiredAt: '2022-06-15T00:00:00+00:00'
  },
  {
    id: 4,
    firstName: 'Alice',
    lastName: 'Williams',
    email: 'alice.williams@example.com',
    position: 'QA Engineer',
    salaryAmount: 42000,
    salaryCurrency: 'EUR',
    hiredAt: '2023-03-10T00:00:00+00:00'
  },
  {
    id: 5,
    firstName: 'Charlie',
    lastName: 'Brown',
    email: 'charlie.brown@example.com',
    position: 'DevOps',
    salaryAmount: 55000,
    salaryCurrency: 'EUR',
    hiredAt: '2022-11-20T00:00:00+00:00'
  }
]

export const mockPaginatedResponse = {
  'hydra:member': mockEmployees,
  'hydra:totalItems': 25,
  'hydra:view': {
    '@id': '/api/employees?page=1',
    'hydra:first': '/api/employees?page=1',
    'hydra:last': '/api/employees?page=5',
    'hydra:next': '/api/employees?page=2'
  }
}

export const mockEmptyResponse = {
  'hydra:member': [],
  'hydra:totalItems': 0,
  'hydra:view': {
    '@id': '/api/employees?page=1',
    'hydra:first': '/api/employees?page=1',
    'hydra:last': '/api/employees?page=1'
  }
}

export const createMockEmployee = (overrides = {}) => ({
  ...mockEmployee,
  ...overrides
})

export const createMockEmployees = (count = 5) => {
  return Array.from({ length: count }, (_, index) => ({
    id: index + 1,
    firstName: `Employee${index + 1}`,
    lastName: `LastName${index + 1}`,
    email: `employee${index + 1}@example.com`,
    position: ['Developer', 'Designer', 'Manager', 'QA Engineer', 'DevOps'][index % 5],
    salaryAmount: 40000 + (index * 5000),
    salaryCurrency: 'EUR',
    hiredAt: `2023-0${(index % 9) + 1}-15T00:00:00+00:00`
  }))
}