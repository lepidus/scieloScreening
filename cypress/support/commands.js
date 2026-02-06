Cypress.Commands.add('beginSubmission', (submissionData) => {
	cy.get('input[name="locale"][value="en"]').click();
	cy.setTinyMceContent('startSubmission-title-control', submissionData.title);

	cy.get('input[name="submissionRequirements"]').check();
	cy.get('input[name="privacyConsent"]').check();
	cy.contains('button', 'Begin Submission').click();
});

Cypress.Commands.add('detailsStep', (submissionData, options = {}) => {
	const { fillKeywords = true } = options;
	
	cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);

	if (fillKeywords) {
		submissionData.keywords.forEach(keyword => {
			cy.get('#titleAbstract-keywords-control-en').type(keyword, {delay: 0});
			cy.wait(500);
			cy.get('#titleAbstract-keywords-control-en').type('{enter}', {delay: 0});
		});
	}

	cy.contains('button', 'Continue').click();
});

Cypress.Commands.add('addContributor', (contributorData, options = {}) => {
	const { toUpperCase = false, fillAffiliation = true } = options;

	let given = toUpperCase ? contributorData.given.toUpperCase() : contributorData.given;
	let family = toUpperCase ? contributorData.family.toUpperCase() : contributorData.family;

	cy.contains('button', 'Add Contributor').click();
	cy.get('input[name="givenName-en"]').type(given, {delay: 0});
	cy.get('input[name="familyName-en"]').type(family, {delay: 0});
	cy.get('input[name="email"]').type(contributorData.email, {delay: 0});
	cy.get('select[name="country"]').select(contributorData.country);

	if ('orcid' in contributorData) {
		cy.get('input[name="orcid"]').type(contributorData.orcid, {delay: 0});
	}

	if (fillAffiliation) {
		cy.get('input[name="affiliation-en"]').should('have.attr', 'required');
		cy.get('input[name="affiliation-en"]').type(contributorData.affiliation, {delay: 0});
	}

	cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
	cy.waitJQuery();
});

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