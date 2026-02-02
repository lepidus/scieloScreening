Cypress.Commands.add('findSubmission', function(tab, title) {
    cy.get('nav').contains(tab).click();
    cy.contains('table tr', title).within(() => {
        cy.get('button').contains(/Complete submission|View/).click({force: true});
    });
});

Cypress.Commands.add('addSubmissionGalleys', (files) => {
    files.forEach(file => {
        cy.get('a:contains("Add File")').click();
        cy.wait(2000);
        cy.get('[role="dialog"]').then(($modalDiv) => {
            cy.wait(3000);
            $modalDiv.find('div.header:contains("Add File")');
            cy.get('[role="dialog"] input[id^="label-"]').type('PDF', {delay: 0});
            cy.get('[role="dialog"] button:contains("Save")').click();
            cy.wait(2000);
        });
        cy.get('select[id=genreId]').select(file.genre);
        cy.fixture(file.file, 'base64').then(fileContent => {
            cy.get('input[type=file]').attachFile(
                {fileContent, 'filePath': file.fileName, 'mimeType': 'application/pdf', 'encoding': 'base64'}
            );
        });
        cy.get('#continueButton').click();
        cy.wait(2000);
        for (const field in file.metadata) {
            cy.get('input[id^="' + Cypress.$.escapeSelector(field) + '"]:visible,textarea[id^="' + Cypress.$.escapeSelector(field) + '"]').type(file.metadata[field], {delay: 0});
            cy.get('input[id^="language"').click({force: true});
        }
        cy.get('#continueButton').click();
        cy.get('#continueButton').click();
    });
});
