Cypress.Commands.add('findSubmission', function(tab, title) {
	cy.get('#' + tab + '-button').click();
    cy.get('.listPanel__itemSubtitle:visible:contains("' + title + '")').first()
        .parent().parent().within(() => {
            cy.get('.pkpButton:contains("View")').click();
        });
});

Cypress.Commands.add('addSubmissionGalleys', (files) => {
	files.forEach(file => {
		cy.get('a[id^=component-grid-preprintgalleys-preprintgalleygrid-addGalley-button-]').contains("Add File").click();
		cy.wait(2000); // Avoid occasional failure due to form init taking time
		cy.get('div.pkp_modal_panel').then($modalDiv => {
			cy.wait(3000);
			$modalDiv.find('div.header:contains("Add File")');
			cy.get('div.pkp_modal_panel input[id^="label-"]').type('PDF', {delay: 0});
			cy.get('div.pkp_modal_panel button:contains("Save")').click();
			cy.wait(2000); // Avoid occasional failure due to form init taking time
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
			cy.get('input[id^="language"').click({force: true}); // Close multilingual and datepicker pop-overs
		}
		cy.get('#continueButton').click();
		cy.get('#continueButton').click();
	});
});