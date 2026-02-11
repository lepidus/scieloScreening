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
        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Users & Roles').click();
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

        cy.contains('button', 'Users').click();
        cy.get('input[type="search"]').type('zwoods');
        cy.waitJQuery();
        
        cy.contains('span', 'Zita Woods').parent().parent().within(() => {
            cy.get('button').click();
            cy.contains('button', 'Edit').click();
        });

        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();

        cy.contains('button', 'Add Another Role').click();
        cy.get('select[name="userGroupId"]').select('SciELO Journal');
        cy.get('input[name="dateStart"]').type(`${yyyy}-${mm}-${dd}`);
        cy.get('select[name="masthead"]').select('Does not appear on the masthead');
        cy.contains('button', 'Save And Continue').click();
        cy.contains('button', 'Invite user to the role').click();
        cy.contains('Invitation Sent');
        cy.logout();
        
        cy.login('zwoods', null, 'publicknowledge');
        cy.visit('localhost:8025');
        cy.get('b:contains("You are invited to new roles")').click();
        cy.wait(500);
        cy.get('iframe#preview-html').its('0.contentDocument.body')
            .then(cy.wrap)
            .find('a:contains("Accept Invitation")')
            .invoke('attr', 'target', '_self')
            .click();

        cy.wait(1000);
        cy.get('iframe#preview-html').its('0.contentDocument.body')
            .then(cy.wrap)
            .find('button:contains("Accept And Continue to OPS")').click();
        cy.logout();
        cy.get('label:contains("SciELO Journal")').within(() => {
            cy.get('input').check();
        });

        cy.get('#userDetailsForm .submitFormButton').click();
        cy.wait(1500);

        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Users & Roles').click();
        cy.contains('button', 'Users').click();
        cy.get('input[type="search"]').type('zwoods');
        cy.waitJQuery();

        cy.contains('span', 'Zita Woods').parent().parent().within(() => {
            cy.contains('SciELO Journal');
        });
    });
});