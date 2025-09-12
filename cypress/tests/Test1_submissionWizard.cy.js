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

function addContributor(contributorData, toUpperCase = false) {
    let given = (toUpperCase) ? contributorData.given.toUpperCase() : contributorData.given;
    let family = (toUpperCase) ? contributorData.family.toUpperCase() : contributorData.family; 

    cy.contains('button', 'Add Contributor').click();
    cy.get('input[name="givenName-en"]').type(given, {delay: 0});
    cy.get('input[name="familyName-en"]').type(family, {delay: 0});
    cy.get('input[name="email"]').type(contributorData.email, {delay: 0});
    cy.get('select[name="country"]').select(contributorData.country);

    if ('orcid' in contributorData) {
        cy.get('input[name="orcid"]').type(contributorData.orcid, {delay: 0});
    }

    cy.get('input[name="affiliation-en"]').should('have.attr', 'required');
    cy.get('input[name="affiliation-en"]').type(contributorData.affiliation, {delay: 0});

    cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
    cy.waitJQuery();
}

describe('SciELO Screening Plugin - Submission wizard tests', function() {
    let submissionData;
    let files;
    
    before(function() {
        Cypress.config('defaultCommandTimeout', 10000);
        submissionData = {
            title: "The Grand Budapest Hotel",
			abstract: 'A young lobby boy starts working in a great institution',
			keywords: ['plugin', 'testing'],
            contributors: [
                {
                    'given': 'Tony',
                    'family': 'Revolori',
                    'email': 'tony.revolori@budapest.com',
                    'country': 'United States',
                    'affiliation': 'Hollywood'
                },
                {
                    'given': 'Ralph',
                    'family': 'Fiennes',
                    'email': 'ralph.fiennes@budapest.com',
                    'country': 'United Kingdom',
                    'affiliation': 'Hollywood'
                },
                {
                    'given': 'Saoirse',
                    'family': 'Ronan',
                    'email': 'saoirse.ronan@budapest.com',
                    'country': 'United States',
                    'affiliation': 'Hollywood',
                    'orcid': 'https://orcid.org/0000-0002-1825-0097'
                }
            ]
		};
        files = [
            {
                'file': 'dummy.pdf',
                'fileName': 'dummy.pdf',
                'mimeType': 'application/pdf',
                'genre': 'Preprint Text'
            },
            {
                'file': '../../plugins/generic/scieloScreening/cypress/fixtures/empty_orcid_document.pdf',
                'fileName': 'empty_orcid_document.pdf',
                'mimeType': 'application/pdf',
                'genre': 'Preprint Text'
            },
            {
                'file': '../../plugins/generic/scieloScreening/cypress/fixtures/filled_orcid_document.pdf',
                'fileName': 'filled_orcid_document.pdf',
                'mimeType': 'application/pdf',
                'genre': 'Preprint Text'
            }
        ];
    });
    
    it("All contributors must have affiliation. Must enter the number of contributors", function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        beginSubmission(submissionData);
        detailsStep(submissionData);
        cy.contains('button', 'Continue').click();

        cy.get('.contributorsListPanel button:contains("Delete")').click();
        cy.contains('button', 'Delete Contributor').click();
        cy.waitJQuery();

        cy.contains('h2', 'Number of contributors');
        cy.contains('Please inform the total number of contributors to this publication');
        cy.get('input[name="numberContributors"]').clear().type('5', {delay: 0});

        addContributor(submissionData.contributors[0]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('All submission contributors must have their affiliation filled').should('not.exist');
        cy.contains('The number of contributors entered is not the same as that reported');

        cy.get('.pkpSteps__step button:contains("Contributors")').click();
        cy.get('input[name="numberContributors"]').clear().type('1', {delay: 0});
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('The number of contributors entered is not the same as that reported').should('not.exist');
    });
    it('Contributors names should not be uppercase', function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        
        addContributor(submissionData.contributors[1], true);
        cy.get('input[name="numberContributors"]').clear().type('2', {delay: 0});
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('Some contributors have their name in capital letters. We ask that you correct them.');

        cy.get('.pkpSteps__step button:contains("Contributors")').click();
        cy.get('.listPanel__itemTitle:visible:contains("RALPH FIENNES")')
            .parent().parent().within(() => {
                cy.contains('button', 'Delete').click();
            });
        cy.contains('button', 'Delete Contributor').click();
        cy.waitJQuery();

        addContributor(submissionData.contributors[1]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('Some contributors have their name in capital letters. We ask that you correct them.').should('not.exist');
    });
    it('At least one contributor should have a ORCID assigned', function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('At least one contributor must have their ORCID confirmed. Please, check your e-mail');

        cy.get('.pkpSteps__step button:contains("Contributors")').click();
        addContributor(submissionData.contributors[2]);
        cy.get('input[name="numberContributors"]').clear().type('3', {delay: 0});
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('At least one contributor must have their ORCID confirmed. Please, check your e-mail').should('not.exist');
    });
    it('License field should be hidden for authors', function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.contains('h2', 'License').should('not.exist');
        cy.contains('Please select the license to apply to your preprint when it is posted').should('not.exist');
    });
    it('Submission should have only one PDF file', function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.reload();

        cy.contains('You have not added any PDF documents to this submission');
        cy.contains('It was not possible to verify the scientific production of the ORCID records, since no PDF document was sent');
        cy.get('.pkpSteps__step button:contains("Upload Files")').click();

        cy.addSubmissionGalleys([files[0], files[0]]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);

        cy.contains('Please send a single PDF file');
        cy.contains('If you are sending files associated with the manuscript, please include them in the same PDF as the manuscript');
        cy.contains('If you are sending a new version of the manuscript, make sure to delete the current PDF file before uploading a new one');
        cy.contains('If this is a translated version of the manuscript, a new submission will be necessary, with a different ID');

        cy.get('.pkpSteps__step button:contains("Upload Files")').click();
        cy.get('.show_extras').eq(1).click();
        cy.get('a.pkp_linkaction_deleteGalley').eq(1).click();
        cy.contains('button','OK').click();

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);

        cy.contains('You have not added any PDF documents to this submission').should('not.exist');
        cy.contains('Please send a single PDF file').should('not.exist');
    });
    it('Some submission metadata should be inserted in english', function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('The following metadata must be filled in english: Keywords');

        cy.get('.pkpSteps__step button:contains("Details")').click();
        submissionData.keywords.forEach(keyword => {
            cy.get('#titleAbstract-keywords-control-en').type(keyword, {delay: 0});
            cy.wait(500);
            cy.get('#titleAbstract-keywords-control-en').type('{enter}', {delay: 0});
        });
    
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.wait(1000);
        cy.contains('The following metadata must be filled in english').should('not.exist');
    });
    it('It is desirable that at least one ORCID has publicly listed works', function() {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.reload();

        cy.contains('It was not possible to verify the scientific production of the ORCID records, since the PDF document sent does not have ORCIDs listed');
        cy.contains('button', 'Submit').should('not.be.disabled');
        
        cy.get('.pkpSteps__step button:contains("Upload Files")').click();
        cy.get('.show_extras').first().click();
        cy.get('a.pkp_linkaction_deleteGalley').first().click();
        cy.contains('button','OK').click();
        
        cy.addSubmissionGalleys([files[1]]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.reload();

        cy.contains('None of the ORCID records reported in the manuscript have publicly listed works, making it difficult to moderate it');
        cy.contains('Please make sure that at least one of the ORCID registries you have entered includes the most recent scientific production or ensure that the information is public');
        cy.contains('button', 'Submit').should('not.be.disabled');

        cy.get('.pkpSteps__step button:contains("Upload Files")').click();
        cy.get('.show_extras').first().click();
        cy.get('a.pkp_linkaction_deleteGalley').first().click();
        cy.contains('button','OK').click();

        cy.addSubmissionGalleys([files[2]]);
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.reload();

        cy.contains('The scientific production of the ORCID records has been successfully confirmed');

        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});