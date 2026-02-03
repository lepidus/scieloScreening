import '../support/commands.js';

describe('SciELO Screening Plugin - WorkFlow features tests', function() {
    let unscreenedSubmissionData;
    let dummyPdf;

    before(function() {
        Cypress.config('defaultCommandTimeout', 10000);
        unscreenedSubmissionData = {
            title: "Asteroid City",
            abstract: 'A city in the middle of the desert, with an asteroid crater',
            keywords: ['plugin', 'testing'],
            contributors: [
                {
                    'given': 'Jason',
                    'family': 'Schwartzman',
                    'email': 'jason.schwartzman@asteroidcity.com',
                    'country': 'United States'
                },
                {
                    'given': 'Scarlett',
                    'family': 'Johanson',
                    'email': 'scarlett.johanson@asteroidcity.com',
                    'country': 'United States'
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

    it("Hides agencies, prefix and subtitle fields", function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Workflow').click();
        cy.get('#metadata-button').click();
        cy.contains('Enable supporting agencies metadata').parent().within(() => {
            cy.get('input[type="checkbox"]').check();
        });
        cy.get('button:visible:contains("Save")').click();
        cy.get('.pkpFormPage__status:contains("Saved")');
    });

    it("Disables plugin temporarily", function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Website').click({ force: true });

        cy.waitJQuery();
        cy.get('#plugins-button').click();

        cy.get('input[id^=select-cell-scieloscreeningplugin]').uncheck();
        cy.wait(500);
        cy.get('body').then($body => {
            if ($body.find('.pkp_modal_panel button:contains("OK")').length > 0) {
                cy.get('.pkp_modal_panel button:contains("OK")').click();
            } else if ($body.find('div[role="dialog"] button:contains("OK")').length > 0) {
                cy.get('div[role="dialog"] button:contains("OK")').click();
            }
        });
        cy.get('input[id^=select-cell-scieloscreeningplugin]').should('not.be.checked');
    });

    it("Creates submission without going through screening", function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.contains('Start A New Submission').click();

        cy.beginSubmission(unscreenedSubmissionData);
        cy.detailsStep(unscreenedSubmissionData);
        cy.addSubmissionGalleys([dummyPdf, dummyPdf]);
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.addContributor(unscreenedSubmissionData.contributors[0]);
        cy.addContributor(unscreenedSubmissionData.contributors[1]);
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.get('.submissionWizard__footer button').contains('Continue').click();
        cy.wait(1000);

        cy.contains('button', 'Submit').click();
        cy.get('div[role="dialog"]:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });

    it("Re-enable the plugin", function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Website').click({ force: true });

        cy.waitJQuery();
        cy.get('#plugins-button').click();

        cy.get('input[id^=select-cell-scieloscreeningplugin]').check();
        cy.get('input[id^=select-cell-scieloscreeningplugin]').should('be.checked');
    });

    it("Displays screening info in publication tab for unscreened submission", function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Active submissions').click();
        cy.contains('table tr', unscreenedSubmissionData.title).within(() => {
            cy.get('button').click({force: true});
        });
        cy.waitJQuery();

        cy.openWorkflowMenu('SciELO Screening Info');

        cy.contains('See the status of each screening step below');
        cy.contains('The following metadata was not filled in english');
        cy.contains('The authors below have not filled out their affiliations');
        cy.contains('Jason Schwartzman');
        cy.contains('Scarlett Johanson');
        cy.contains('Please send a single PDF file');
        cy.contains('It was not possible to verify the scientific production of the ORCID records, since the PDF document sent does not have ORCIDs listed');
    });

    it("Submission with multiple PDFs shows warning in screening info", function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Active submissions').click();
        cy.contains('table tr', unscreenedSubmissionData.title).within(() => {
            cy.get('button').click({force: true});
        });
        cy.waitJQuery();

        cy.openWorkflowMenu('SciELO Screening Info');
        cy.contains('Please send a single PDF file');
    });

    it("Displays screening rules on submission posting", function () {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Active submissions').click();
        cy.contains('table tr', unscreenedSubmissionData.title).within(() => {
            cy.get('button').click({force: true});
        });
        cy.waitJQuery();

        cy.get('button:contains("Post the preprint")').click();
        cy.contains('button', 'Post').click();
        cy.contains('All submission contributors must have their affiliation filled');
    });
});
