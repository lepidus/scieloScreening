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
    submissionData.keywords.forEach(keyword => {
        cy.get('#titleAbstract-keywords-control-en').type(keyword, {delay: 0});
        cy.get('#titleAbstract-keywords-control-en').type('{enter}', {delay: 0});
    });
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
    
    cy.get('input[name="affiliation-en"]').should('have.attr', 'required');
    cy.get('input[name="affiliation-en"]').type(contributorData.affiliation, {delay: 0});
    
    cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
    cy.waitJQuery();
}

describe('SciELO Screening Plugin - Submission wizard tests', function() {
    let submissionData;
    let files;
    
    before(function() {
        Cypress.config('defaultCommandTimeout', 4000);
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
                }
            ]
		};
        files = [
            {
                'file': 'dummy.pdf',
                'fileName': 'dummy.pdf',
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
        cy.contains('You have not filled in the affiliation for all contributors').should('not.exist');
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
});