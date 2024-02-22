import '../support/commands.js';

describe('SciELO Screening Plugin - WorkFlow features tests', function() {
    let submissionTitle;
    
    before(function() {
        Cypress.config('defaultCommandTimeout', 4000);
        submissionTitle = "The Grand Budapest Hotel";
    });
    
    it("Displays screening info in publication tab", function () {
        cy.login('dphillips', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionTitle);

        cy.contains('button', 'Preprint').click();
        cy.contains('button', 'SciELO Screening Info').click();
        
        cy.contains('See the status of each screening step below');
        cy.contains('All metadata was filled in english');
        cy.contains('All authors had their affiliation filled');
        cy.contains('ORCID status is confirmed');
        cy.contains('Only one PDF document was submitted');

    });
});
