import '../support/commands.js';

function beginSubmission(submissionData) {
    cy.get('input[name="locale"][value="en"]').click();
    cy.setTinyMceContent('startSubmission-title-control', submissionData.title);
    
    cy.get('input[name="submissionRequirements"]').check();
    cy.get('input[name="privacyConsent"]').check();
    cy.contains('button', 'Begin Submission').click();
}

function detailsStep(submissionData) {
    cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
    cy.contains('button', 'Continue').click();
}

function addContributor(contributorData) {
    cy.contains('button', 'Add Contributor').click();
    cy.get('input[name="givenName-en"]').type(contributorData.given, {delay: 0});
    cy.get('input[name="familyName-en"]').type(contributorData.family, {delay: 0});
    cy.get('input[name="email"]').type(contributorData.email, {delay: 0});
    cy.get('select[name="country"]').select(contributorData.country);

    cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
    cy.waitJQuery();
}

describe('SciELO Screening Plugin - WorkFlow features tests', function() {
    let screenedSubmissionTitle;
    let unscreenedSubmissionData;
    let dummyPdf;
    
    before(function() {
        Cypress.config('defaultCommandTimeout', 10000);
        screenedSubmissionTitle = "The Grand Budapest Hotel";
        unscreenedSubmissionData = {
            title: "Asteroid City",
			abstract: 'A city in the middle of the desert, with an asteroid crater',
			keywords: ['plugin', 'testing'],
            contributors: [
                {
                    'given': 'Jason',
                    'family': 'Schwartzman',
                    'email': 'jason.schwartzman@asteroidcity.com',
                    'country': 'United States',
                    'affiliation': 'Hollywood'
                },
                {
                    'given': 'Scarlett',
                    'family': 'Johanson',
                    'email': 'scarlett.johanson@asteroidcity.com',
                    'country': 'United States',
                    'affiliation': 'Hollywood'
                }
            ]
		};
        dummyPdf = {
            'file': 'dummy.pdf',
            'fileName': 'dummy.pdf',
            'mimeType': 'application/pdf',
            'genre': 'Preprint Text'
        };
    });
    
    it("Displays screening info in publication tab for screened submission", function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', screenedSubmissionTitle);

        cy.contains('button', 'Preprint').click();
        cy.contains('button', 'SciELO Screening Info').click();
        
        cy.contains('See the status of each screening step below');
        cy.contains('All metadata was filled in english');
        cy.contains('All authors had their affiliation filled');
        cy.contains('ORCID status is confirmed');
        cy.contains('Only one PDF document was submitted');
    });
    it("Disables plugin temporarily", function () {
        cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-scieloscreeningplugin]').uncheck();
        cy.get('.pkp_modal_panel button:contains("OK")').click();
    });
    it("Creates submission without going through screening", function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        beginSubmission(unscreenedSubmissionData);
        detailsStep(unscreenedSubmissionData);
        cy.addSubmissionGalleys([dummyPdf, dummyPdf]);
        cy.contains('button', 'Continue').click();
        addContributor(unscreenedSubmissionData.contributors[0]);
        addContributor(unscreenedSubmissionData.contributors[1]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);

        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
    it("Re-enable the plugin", function () {
        cy.login('dbarnes', null, 'publicknowledge');
		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-scieloscreeningplugin]').check();
        cy.get('input[id^=select-cell-scieloscreeningplugin]').should('be.checked');
    });
    it("Displays screening info in publication tab for unscreened submission", function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', unscreenedSubmissionData.title);

        cy.contains('button', 'Preprint').click();
        cy.contains('button', 'SciELO Screening Info').click();
        
        cy.contains('See the status of each screening step below');
        cy.contains('The following metadata was not filled in english: Keywords');
        cy.contains('The authors below have not filled out their affiliations');
        cy.get('#affiliationBody').within(() => {
            cy.contains('Jason Schwartzman');
            cy.contains('Scarlett Johanson');
        });
        cy.contains('No author had confirmed their ORCID');
        cy.contains('Please send a single PDF file');
    });
    it("Displays screening rules on submission posting", function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.findSubmission('active', unscreenedSubmissionData.title);
        cy.contains('button', 'Preprint').click();
        
        cy.get('.pkpHeader__actions button:contains("Post")').click();
        cy.contains('All submission contributors must have their affiliation filled');
        cy.get('a.pkpModalCloseButton:visible').click();

        cy.contains('button', 'Contributors').click();
        cy.get('.listPanel__itemTitle:visible:contains("Jason Schwartzman")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.get('input[name="affiliation-en"]').type(unscreenedSubmissionData.contributors[0].affiliation, {delay: 0});
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.get('.listPanel__itemTitle:visible:contains("Scarlett Johanson")')
            .parent().parent().within(() => {
                cy.contains('button', 'Edit').click();
            });
        cy.get('input[name="affiliation-en"]').type(unscreenedSubmissionData.contributors[0].affiliation, {delay: 0});
        cy.get('.modal__panel:contains("Edit")').find('button').contains('Save').click();
        cy.waitJQuery();

        cy.get('.pkpHeader__actions button:contains("Post")').click();
        cy.contains('All requirements have been met. Are you sure you want to post this?');
    });
});
