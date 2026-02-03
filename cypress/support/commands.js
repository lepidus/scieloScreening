Cypress.Commands.add('findSubmission', function(tab, title) {
    cy.get('nav').contains(tab).click();
    cy.contains('table tr', title).within(() => {
        cy.get('button').contains(/Complete submission|View/).click({force: true});
    });
});
