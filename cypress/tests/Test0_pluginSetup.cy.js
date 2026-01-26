import '../support/commands.js';

describe('SciELO Screening - Plugin setup', function () {
	it('Enables SciELO Screening plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('nav').contains('Settings').click();
		cy.get('nav').contains('Website').click({ force: true });

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-scieloscreeningplugin]').check();
		cy.get('input[id^=select-cell-scieloscreeningplugin]').should('be.checked');
	});
	it('Configures plugin', function() {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.get('nav').contains('Settings').click();
		cy.get('nav').contains('Website').click({ force: true });

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.contains('tr', 'SciELO Screening').within(() => {
			cy.get('a.show_extras').click();
			cy.contains('a', 'Settings').click();
		});

		cy.get('#orcidAPIPath').select('Member Sandbox');
		cy.get('input[name="orcidClientId"]').clear().type(Cypress.env('orcidClientId'), {delay: 0});
		cy.get('input[name="orcidClientSecret"]').clear().type(Cypress.env('orcidClientSecret'), {delay: 0});

		cy.get('#scieloScreeningSettingsForm').contains('button', 'Save').click();
		cy.get('.pkpFormPage__status:contains("Saved")');
		cy.contains('Please configure the ORCID API access for use in pulling ORCID records information').should('not.exist');
	});
});