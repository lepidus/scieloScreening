describe('SciELO Screening - Plugin setup', function () {
    it('Enables SciELO Screening plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-scieloscreeningplugin]').check();
		cy.get('input[id^=select-cell-scieloscreeningplugin]').should('be.checked');
    });
});