function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

function submissionStep1() {
    cy.get('#sectionId').select('1');
    cy.get('#pkp_submissionChecklist > ul > li > label > input').check();
    cy.get('#privacyConsent').check();
    cy.get('.checkbox_and_radiobutton > li > label:visible').contains('Author').within(() => {
        cy.get('input').check();
    });

    cy.get('#submissionStep1 > .formButtons > .submitFormButton').click();
}

function submissionStep2() {
    cy.get('.pkp_linkaction_addGalley').click();
    cy.wait(2000);
    cy.get('input[name="label"]').type('PDF', { delay: 0 });
    cy.get('#articleGalleyForm > .formButtons > .submitFormButton').click();
    cy.get('#genreId').select('1');
    cy.fixture('dummy.pdf', 'base64').then(fileContent => {
        cy.get('input[type="file"]').upload({ fileContent, 'fileName': 'dummy_document.pdf', 'mimeType': 'application/pdf', 'encoding': 'base64' });
    });
    cy.get('#continueButton').click();
    cy.get('#continueButton').click();
    cy.get('#continueButton').click();
    cy.get('#submitStep2Form > .formButtons > .submitFormButton').click();
}

function addContributor() {
    cy.get('a[id^="component-grid-users-author-authorgrid-addAuthor-button-"]').click();
    cy.wait(250);
    cy.get('input[id^="givenName-en_US-"]').type("Altigran S.", {delay: 0});
    cy.get('input[id^="familyName-en_US-"]').type("da Silva", {delay: 0});
    cy.get('select[id^="country"]').select("Brasil");
    cy.get('input[id^="email"]').type("altigran.silva@lepidus.com.br", {delay: 0});
    cy.get('input[id^="orcid-"]').type("https://orcid.org/0000-0001-2345-6789", {delay: 0});
    cy.get('input[id^="affiliation-en_US-"]').type("UFAM", {delay: 0});
    cy.get('label').contains("Author").click();
    cy.get('#editAuthor > .formButtons > .submitFormButton').click();
}

function performDOIScrening() {
    cy.get('#openDOIModal').click();
    cy.get('#firstDOI').type("10.1016/j.datak.2003.10.003", { delay: 0 });
    cy.get('#firstDOILabel').click();
    cy.wait(5000);
    cy.get('#secondDOI').type("10.34117/bjdv8n2-322", { delay: 0 });
    cy.get('#secondDOILabel').click();
    cy.wait(5000);
    cy.get('#thirdDOI').type("10.4025/actascianimsci.v42i1.44580", { delay: 0 });
    cy.get('#thirdDOILabel').click();
    cy.wait(5000);
    cy.get('#doiSubmit').click();
}

function submissionStep3() {
    cy.get('input[name^="title"]').first().type("Submissions title", { delay: 0 });
    cy.get('label').contains('Title').click();
    cy.get('textarea[id^="abstract-en_US"]').type("Example of abstract");
    cy.get('.section > label:visible').first().click();
    cy.get('#inputNumberAuthors').type("2", { delay: 0 });
    addContributor();
    cy.get('ul[id^="en_US-keywords-"]').then(node => {
        node.tagit('createTag', "Dummy keyword");
    });
    performDOIScrening();
    cy.get('#submitStep3Form > .formButtons > .submitFormButton').click();
}

function submissionStep4() {
    cy.get('#submitStep4Form > .formButtons > .submitFormButton').click();
    cy.get('.pkp_modal_confirmation > .footer > .ok').click();
}

function checkDOIsWithConfirmedAuthorship() {
    cy.get('#screeningInfo-button').click();
    cy.get('#doiBody > ul > li > a').eq(0).contains("10.1016/j.datak.2003.10.003 (authorship confirmed)");
    cy.get('#doiBody > ul > li > a').eq(1).contains("10.34117/bjdv8n2-322 (authorship not confirmed)");
    cy.get('#doiBody > ul > li > a').eq(2).contains("10.4025/actascianimsci.v42i1.44580 (authorship not confirmed)");
}

describe('SciELO Screening Plugin - Show which DOIs have authorship confirmed', function() {
    it("Check if plugin shows which DOIs had authorship confirmed in case one doesn't", function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();

        submissionStep1();
        submissionStep2();
        submissionStep3();
        submissionStep4();
        cy.get('ul.plain > li > a').first().click();
        checkDOIsWithConfirmedAuthorship();
    });
});