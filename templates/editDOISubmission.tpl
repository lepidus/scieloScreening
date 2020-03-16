{**
 * plugins/generic/authorDOIScreening/templates/editDOISubmission.tpl
 *
 * Form for editing DOIs during a submission
 *}

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/submissionEdit.css">

{fbvFormSection}
    <label>
        {translate key="plugins.generic.authorDOIScreening.nome"}
        <span class="req">*</span>
    </label>
    <p class="description">{translate key="plugins.generic.authorDOIScreening.submission.description"}</p>
    {* Algum campo hidden para indicar erro *}
    <div class="pkpFormGroup__fields">
        <div class="pkpFormField">
            <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.first"}</label>
            <input type="text" class="pkpFormField__input" placeholder="Ex.: 10.1000/182">
        </div>
        <div class="pkpFormField">
            <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.second"}</label>
            <input type="text" class="pkpFormField__input" placeholder="Ex.: 10.1000/182">
        </div>
    </div>
{/fbvFormSection}