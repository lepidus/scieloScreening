Cypress.Commands.add('beginSubmission', function(submissionData) {
    cy.get('label:contains("English")').click();
    cy.setTinyMceContent('startSubmission-title-control', submissionData.title);

    cy.get('input[name="submissionRequirements"]').check();
    cy.get('input[name="privacyConsent"]').check();
    cy.contains('button', 'Begin Submission').click();
});

Cypress.Commands.add('detailsStep', function(submissionData) {
    cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
    cy.get('.submissionWizard__footer button').contains('Continue').click();
});

Cypress.Commands.add('addContributor', function(contributorData, toUpperCase = false) {
    let given = (toUpperCase) ? contributorData.given.toUpperCase() : contributorData.given;
    let family = (toUpperCase) ? contributorData.family.toUpperCase() : contributorData.family;

    cy.get('button').contains('Add Contributor').click();
    cy.wait(1000);
    cy.get('.pkpFormField:contains("Given Name")').find('input[name*="givenName-en"]').type(given, {delay: 0});
    cy.get('.pkpFormField:contains("Family Name")').find('input[name*="familyName-en"]').type(family, {delay: 0});
    cy.get('.pkpFormField:contains("Email")').find('input').type(contributorData.email, {delay: 0});
    cy.get('.pkpFormField:contains("Country")').find('select').select(contributorData.country);

    if ('affiliation' in contributorData) {
        cy.get('.pkpFormField--affiliations .pkpAutosuggest__input')
            .type(contributorData.affiliation, {delay: 0})
            .type('{enter}');
        cy.wait(1000);
        cy.get('.pkpFormField--affiliations .pkpButton').contains('Add').click();
        cy.wait(500);
    }

    cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
    cy.wait(2000);
});

Cypress.Commands.add('openIncompleteSubmission', function(authorName) {
    cy.get('nav').contains('Submissions').click();
    cy.contains('table tr', authorName).within(() => {
        cy.get('button').contains('Complete submission').click({force: true});
    });
});

Cypress.Commands.add('findSubmission', function(dashboardPanel, submissionTitle) {
    cy.get('div[data-pc-section="panel"]').eq(0).within(() => {
        cy.get('a').click();
        cy.wait(500);
        cy.contains('span', dashboardPanel).click();
    })

    cy.contains('span', submissionTitle).parent().parent().within(() => {
        cy.contains('button', 'View').click();
    });
});
