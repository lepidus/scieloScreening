describe('SciELO Screening - Plugin setup', function () {
    it('Enables SciELO Screening plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-scieloscreeningplugin]').check();
		cy.get('input[id^=select-cell-scieloscreeningplugin]').should('be.checked');
    });
	it('Configures plugin', function() {
		const pluginRowId = 'component-grid-settings-plugins-settingsplugingrid-category-generic-row-scieloscreeningplugin';

		cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();
		cy.get('tr#' + pluginRowId + ' a.show_extras').click();
		cy.get('a[id^=' + pluginRowId + '-settings-button]').click();

		cy.get('#orcidAPIPath').select('Member Sandbox');
		cy.get('input[name="orcidClientId"]').clear().type(Cypress.env('orcidClientId'), {delay: 0});
		cy.get('input[name="orcidClientSecret"]').clear().type(Cypress.env('orcidClientSecret'), {delay: 0});

		cy.get('#scieloScreeningSettingsForm button:contains("OK")').click();
		cy.contains('Please configure the ORCID API access for use in pulling ORCID records information').should('not.exist');
	});
});