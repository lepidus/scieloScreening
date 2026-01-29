import '../support/commands.js';

describe('SciELO Screening - Plugin setup', function () {
	it('Enables SciELO Screening plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('nav').contains('Settings').click();
		cy.get('nav').contains('Website').click({ force: true });

		cy.waitJQuery();
		cy.get('button[id="plugins-button"]').click();

		cy.get('input[id^=select-cell-scieloscreeningplugin]').check();
		cy.get('input[id^=select-cell-scieloscreeningplugin]').should('be.checked');
	});

	it('Configures plugin', function() {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.get('nav').contains('Settings').click();
		cy.get('nav').contains('Website').click({ force: true });

		cy.waitJQuery();
		cy.get('button[id="plugins-button"]').click();

		cy.get('a[id^="component-grid-settings-plugins-settingsplugingrid-category-generic-row-scieloscreeningplugin-settings-button-"]', {timeout: 20_000}).as('settings');
		cy.waitJQuery();
		cy.get('@settings').click({force: true});

		cy.get('#orcidAPIPath').select('Member Sandbox');
		cy.get('input[name="orcidClientId"]').clear().type(Cypress.env('orcidClientId'), {delay: 0});
		cy.get('input[name="orcidClientSecret"]').clear().type(Cypress.env('orcidClientSecret'), {delay: 0});

		cy.get('form[id="scieloScreeningSettingsForm"] button[id^="submitFormButton"]').click();
		cy.waitJQuery();
		cy.contains('Please configure the ORCID API access for use in pulling ORCID records information').should('not.exist');
	});
});