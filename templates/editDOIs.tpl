{**
 * plugins/generic/authorDOIScreening/templates/editDOIs.tpl
 *
 * Form for editing DOIs from a submission
 *}

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/submissionEdit.css">

<form class="pkpForm" id="funderForm" method="post" action="{$actionUrl}">
    {fbvFormArea id="funderFormArea" class="pkpFormGroup"}
        <h2>{translate key="plugins.generic.authorDOIScreening.nome"}</h2>
        <p>{translate key="plugins.generic.authorDOIScreening.submission.description"}</p>
        {* Algum campo hidden para indicar erro *}
        <div class="pkpFormGroup__fields">
            <div class="pkpFormField">
                <div class="pkpFormField__heading">
                    <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.first"}</label>
                </div>
                <input type="text" class="pkpFormField__input">
            </div>
            <div class="pkpFormField">
                <div class="pkpFormField__heading">
                    <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.second"}</label>
                </div>
                <input type="text" class="pkpFormField__input">
            </div>
        </div>
    {/fbvFormArea}
    {fbvFormSection class="pkpFormPage__footer"}
        <div class="pkpFormPage__buttons">
            <button type="submit" class="pkpButton">{translate key="common.save"}</button>
        </div>
    {/fbvFormSection}
</form>