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

function addContributor(contributorData) {
    cy.contains('button', 'Add Contributor').click();
    cy.get('input[name="givenName-en"]').type(contributorData.given, {delay: 0});
    cy.get('input[name="familyName-en"]').type(contributorData.family, {delay: 0});
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
    
    it("All contributors must have affiliation. Must enter the number of contributors", function() {
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
});