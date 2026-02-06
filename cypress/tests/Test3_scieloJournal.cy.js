import '../support/commands.js';

describe('SciELO Screening Plugin - SciELO Journal related features', function() {
    let submissionData;
    let pdfFile;

    before(function() {
        Cypress.config('defaultCommandTimeout', 10000);
        submissionData = {
            title: "Fantastic Mr. Fox",
			abstract: 'An urbane fox cannot resist returning to his farm raiding ways and then must help his community survive the farmers retaliation',
			keywords: ['plugin', 'testing'],
            contributors: [
                {
                    'given': 'George',
                    'family': 'Clooney',
                    'email': 'george.clooney@fantasticfox.com',
                    'country': 'United States',
                    'affiliation': 'Hollywood'
                },
                {
                    'given': 'Meryl',
                    'family': 'Streep',
                    'email': 'meryl.streep@fantasticfox.com',
                    'country': 'United States',
                    'affiliation': 'Hollywood'
                },
                {
                    'given': 'Bill',
                    'family': 'Murray',
                    'email': 'bill.murray@fantasticfox.com',
                    'country': 'United States',
                    'affiliation': 'Hollywood',
                    'orcid': 'https://orcid.org/0000-0002-1825-0097'
                }
            ]
		};
        pdfFile = {
            'file': '../../plugins/generic/scieloScreening/cypress/fixtures/filled_orcid_document.pdf',
            'fileName': 'filled_orcid_document.pdf',
            'mimeType': 'application/pdf',
            'genre': 'Preprint Text'
        };
    });

    it('Creates SciELO Journal role and assigns it to user', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.contains('Users & Roles').click();
        cy.contains('button', 'Roles').click();
        cy.contains('a', 'Create New Role').click();

        cy.get('#roleId').select('Author');
        cy.get('input[name="name[en]"]').type('SciELO Journal');
        cy.contains('label', 'Role Name').click();
        cy.get('input[name="abbrev[en]"]').type('SciELO');
        cy.contains('label', 'Abbreviation').click();

        cy.get('#userGroupForm button:contains("OK")').click();
        cy.waitJQuery();

        cy.contains('span', 'SciELO Journal')
            .parent().parent().parent()
            .within(() => {
                cy.get('input[type="checkbox"]').check();
            });

        cy.get('#users-button').click();
        cy.contains('a', 'Search').click();
        cy.get('input[name="search"]').type('zwoods');
        cy.contains('button', 'Search').click();
        cy.waitJQuery();
        
        cy.get('.show_extras:visible').click();
        cy.contains('a', 'Edit User').click();

        cy.get('label:contains("SciELO Journal")').within(() => {
            cy.get('input').check();
        });

        cy.get('#userDetailsForm .submitFormButton').click();
        cy.wait(1500);
    });
    it ('User with SciELO Journal role creates submission', function () {
        cy.login('zwoods', null, 'publicknowledge');
        cy.get('div#myQueue a:contains("New Submission")').click();

        cy.beginSubmission(submissionData);
        cy.detailsStep(submissionData, { fillKeywords: true });
        cy.addSubmissionGalleys([pdfFile]);
        cy.contains('button', 'Continue').click();

        for (const contributor of submissionData.contributors) {
            cy.addContributor(contributor);
        }
        cy.get('input[name="numberContributors"]').clear().type('4', {delay: 0});
        cy.contains('button', 'Continue').click();
        
        cy.contains('button', 'Continue').click();

        cy.wait(1500);
        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
    it('SciELO Journal user should not be present in contributors list', function() {
        cy.login('zwoods', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Preprint').click();
        cy.contains('button', 'Contributors').click();

        cy.get('.listPanel__itemIdentity').should('have.length', 3);
        cy.get('.listPanel__itemIdentity:contains("Dana Phillips")').should('not.exist');
        cy.get('.listPanel__itemIdentity:contains("George Clooney")');
        cy.get('.listPanel__itemIdentity:contains("Meryl Streep")');
        cy.get('.listPanel__itemIdentity:contains("Bill Murray")');
    });
});