describe('Homepage', () => {
  it('should load the homepage successfully', () => {
    cy.visit('/')
    
    // Check that the page loads without errors
    cy.get('body').should('be.visible')
    
    // Check for common elements that might be present
    cy.get('html').should('exist')
    
    // Verify the page title
    cy.title().should('not.be.empty')
  })

  it('should have proper page structure', () => {
    cy.visit('/')
    
    // Check that basic HTML structure exists
    cy.get('head').should('exist')
    cy.get('body').should('exist')
    
    // Check that the page is interactive (not just a static error page)
    cy.get('body').should('not.contain.text', '404')
    cy.get('body').should('not.contain.text', 'Error')
  })
})