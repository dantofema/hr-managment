import EmployeeForm from '../../src/components/employees/EmployeeForm.vue'

describe('EmployeeForm Component', () => {
  
  describe('Form Validation Tests', () => {
    
    it('should show validation errors for empty required fields', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        }
      })

      // Try to submit empty form
      cy.get('button[type="submit"]').should('be.disabled')
      
      // Check firstName validation
      cy.get('#firstName').focus().blur()
      cy.contains('El nombre es obligatorio').should('be.visible')
      
      // Check lastName validation
      cy.get('#lastName').focus().blur()
      cy.contains('El apellido es obligatorio').should('be.visible')
      
      // Check email validation
      cy.get('#email').focus().blur()
      cy.contains('El email es obligatorio').should('be.visible')
      
      // Check position validation
      cy.get('#position').focus().blur()
      cy.contains('La posición es obligatoria').should('be.visible')
      
      // Check salary validation
      cy.get('#salaryAmount').focus().blur()
      cy.contains('El salario es obligatorio').should('be.visible')
      
      // Check currency validation
      cy.get('#salaryCurrency').focus().blur()
      cy.contains('La moneda es obligatoria').should('be.visible')
      
      // Check date validation
      cy.get('#hiredAt').focus().blur()
      cy.contains('La fecha de contratación es obligatoria').should('be.visible')
    })

    it('should validate firstName field correctly', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        }
      })

      // Test minimum length validation
      cy.get('#firstName').type('A').blur()
      cy.contains('El nombre debe tener al menos 2 caracteres').should('be.visible')
      
      // Test invalid characters
      cy.get('#firstName').clear().type('John123').blur()
      cy.contains('El nombre solo puede contener letras y espacios').should('be.visible')
      
      // Test valid input
      cy.get('#firstName').clear().type('Juan Carlos').blur()
      cy.get('#firstName-error').should('not.exist')
    })

    it('should validate email field correctly', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        }
      })

      // Test invalid email format
      cy.get('#email').type('invalid-email').blur()
      cy.contains('Ingrese un email válido').should('be.visible')
      
      // Test valid email
      cy.get('#email').clear().type('juan@empresa.com').blur()
      cy.get('#email-error').should('not.exist')
    })

    it('should validate position field length', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        }
      })

      // Test maximum length validation
      const longPosition = 'A'.repeat(101)
      cy.get('#position').type(longPosition).blur()
      cy.contains('La posición no puede exceder 100 caracteres').should('be.visible')
      
      // Test valid position
      cy.get('#position').clear().type('Desarrollador Frontend').blur()
      cy.get('#position-error').should('not.exist')
    })

    it('should validate salary amount correctly', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        }
      })

      // Test negative value
      cy.get('#salaryAmount').type('-100').blur()
      cy.contains('El salario debe ser un número positivo').should('be.visible')
      
      // Test zero value
      cy.get('#salaryAmount').clear().type('0').blur()
      cy.contains('El salario debe ser un número positivo').should('be.visible')
      
      // Test valid salary
      cy.get('#salaryAmount').clear().type('50000.50').blur()
      cy.get('#salaryAmount-error').should('not.exist')
    })

    it('should validate hired date correctly', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        }
      })

      // Test future date
      const futureDate = new Date()
      futureDate.setDate(futureDate.getDate() + 1)
      const futureDateString = futureDate.toISOString().split('T')[0]
      
      cy.get('#hiredAt').type(futureDateString).blur()
      cy.contains('La fecha no puede ser futura').should('be.visible')
      
      // Test valid date (today)
      const today = new Date().toISOString().split('T')[0]
      cy.get('#hiredAt').clear().type(today).blur()
      cy.get('#hiredAt-error').should('not.exist')
    })

  })

  describe('Form Submit Tests', () => {
    
    it('should emit submit event with valid form data in create mode', () => {
      const onSubmit = cy.stub()
      
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        },
        listeners: {
          submit: onSubmit
        }
      })

      // Fill valid form data
      cy.get('#firstName').type('Juan')
      cy.get('#lastName').type('Pérez')
      cy.get('#email').type('juan.perez@empresa.com')
      cy.get('#position').type('Desarrollador Frontend')
      cy.get('#salaryAmount').type('45000')
      cy.get('#salaryCurrency').select('EUR')
      cy.get('#hiredAt').type('2024-01-15')

      // Submit form
      cy.get('button[type="submit"]').should('not.be.disabled').click()

      // Verify submit event was called with correct data
      cy.then(() => {
        expect(onSubmit).to.have.been.calledWith({
          firstName: 'Juan',
          lastName: 'Pérez',
          email: 'juan.perez@empresa.com',
          position: 'Desarrollador Frontend',
          salaryAmount: 45000,
          salaryCurrency: 'EUR',
          hiredAt: '2024-01-15'
        })
      })
    })

    it('should populate form with employee data in edit mode', () => {
      const employeeData = {
        firstName: 'María',
        lastName: 'González',
        email: 'maria@empresa.com',
        position: 'Diseñadora UX',
        salaryAmount: 42000,
        salaryCurrency: 'USD',
        hiredAt: '2023-06-15T00:00:00Z'
      }

      cy.mount(EmployeeForm, {
        props: {
          mode: 'edit',
          employee: employeeData
        }
      })

      // Verify form is populated with employee data
      cy.get('#firstName').should('have.value', 'María')
      cy.get('#lastName').should('have.value', 'González')
      cy.get('#email').should('have.value', 'maria@empresa.com')
      cy.get('#position').should('have.value', 'Diseñadora UX')
      cy.get('#salaryAmount').should('have.value', '42000')
      cy.get('#salaryCurrency').should('have.value', 'USD')
      cy.get('#hiredAt').should('have.value', '2023-06-15')

      // Verify submit button shows correct text
      cy.get('button[type="submit"]').should('contain', 'Actualizar Empleado')
    })

    it('should emit cancel event when cancel button is clicked', () => {
      const onCancel = cy.stub()
      
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        },
        listeners: {
          cancel: onCancel
        }
      })

      cy.get('button[type="button"]').contains('Cancelar').click()

      cy.then(() => {
        expect(onCancel).to.have.been.called
      })
    })

    it('should show confirmation dialog when canceling with unsaved changes', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        }
      })

      // Make some changes to the form
      cy.get('#firstName').type('Juan')
      
      // Stub window.confirm to return false (cancel)
      cy.window().then((win) => {
        cy.stub(win, 'confirm').returns(false)
      })

      cy.get('button[type="button"]').contains('Cancelar').click()

      // Verify confirm was called
      cy.window().its('confirm').should('have.been.called')
    })

    it('should disable form when loading prop is true', () => {
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create',
          loading: true
        }
      })

      // Verify all inputs are disabled
      cy.get('#firstName').should('be.disabled')
      cy.get('#lastName').should('be.disabled')
      cy.get('#email').should('be.disabled')
      cy.get('#position').should('be.disabled')
      cy.get('#salaryAmount').should('be.disabled')
      cy.get('#salaryCurrency').should('be.disabled')
      cy.get('#hiredAt').should('be.disabled')

      // Verify buttons are disabled
      cy.get('button[type="submit"]').should('be.disabled')
      cy.get('button[type="button"]').should('be.disabled')

      // Verify loading text is shown
      cy.get('button[type="submit"]').should('contain', 'Procesando...')
    })

  })

  describe('Form State Management', () => {
    
    it('should track form dirty state correctly', () => {
      const employeeData = {
        firstName: 'Carlos',
        lastName: 'Ruiz',
        email: 'carlos@empresa.com',
        position: 'Backend Developer',
        salaryAmount: 48000,
        salaryCurrency: 'EUR',
        hiredAt: '2023-03-10T00:00:00Z'
      }

      cy.mount(EmployeeForm, {
        props: {
          mode: 'edit',
          employee: employeeData
        }
      })

      // Initially form should not be dirty
      // Make a change
      cy.get('#firstName').clear().type('Carlos Modified')
      
      // Try to cancel - should show confirmation
      cy.window().then((win) => {
        cy.stub(win, 'confirm').returns(true)
      })

      cy.get('button[type="button"]').contains('Cancelar').click()
      
      cy.window().its('confirm').should('have.been.called')
    })

    it('should emit input events when fields change', () => {
      const onInput = cy.stub()
      
      cy.mount(EmployeeForm, {
        props: {
          mode: 'create'
        },
        listeners: {
          input: onInput
        }
      })

      cy.get('#firstName').type('Test')

      cy.then(() => {
        expect(onInput).to.have.been.called
      })
    })

  })

})