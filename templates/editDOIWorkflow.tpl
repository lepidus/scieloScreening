{**
 * plugins/generic/authorDOIScreening/templates/editDOIs.tpl
 *
 * Form for editing DOIs from a submission
 *}

<script>
    function validaDOI(doi){ldelim}
        const padrao = "^10.\\d{ldelim}4,9{rdelim}\/[-._;()/:A-Za-z0-9]+$";
        const regex = RegExp(padrao);

        return regex.test(doi);
    {rdelim}

    $(function(){ldelim}
        $('#doiForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
        $('#firstDOI').focusout(
            function(){ldelim}
                if( $('#firstDOI').val() == "" || !validaDOI($('#firstDOI').val()) ){ldelim}
                    $('#firstDOIError').text("{translate key="plugins.generic.authorDOIScreening.doiValidRequirement"}");
                    $('#firstDOIError').css('display', 'block');
                {rdelim}
                else {ldelim}
                    if($('#firstDOIError').css('display') == 'block')
                        $('#firstDOIError').css('display', 'none');

                {rdelim}
            {rdelim}
        );
        $('#secondDOI').focusout(
            function(){ldelim}
                if($('#secondDOI').val() === ""){ldelim}
                    $('#secondDOIError').text("{translate key="plugins.generic.authorDOIScreening.doiValidRequirement"}");
                    $('#secondDOIError').css('display', 'block');
                {rdelim}
                else {ldelim}
                    if($('#secondDOIError').css('display') === 'block')
                        $('#secondDOIError').css('display', 'none');
                {rdelim}
            {rdelim}
        );
    {rdelim});
</script>

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/submissionWorkflow.css">

{capture assign=actionUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.authorDOIScreening.controllers.grid.DOIGridHandler" op="updateDOIs" submissionId=$submissionId escape=false}{/capture}
<form class="pkpForm" id="doiForm" method="post" action="{$actionUrl}">
    {fbvFormArea id="funderFormArea" class="pkpFormGroup"}
        <h2>{translate key="plugins.generic.authorDOIScreening.nome"}</h2>
        <p>{translate key="plugins.generic.authorDOIScreening.submission.description"}</p>
        <span id="generalError" class="error" style="display:none"></span>
        <div class="pkpFormGroup__fields">
            <div class="pkpFormField">
                <span id="firstDOIError" class="error" style="display:none"></span>
                <div class="pkpFormField__heading">
                    <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.first"}</label>
                </div>
                <input id="firstDOI" type="text" name="firstDOI" class="pkpFormField__input" placeholder="Ex.: 10.1000/182">
            </div>
            <div class="pkpFormField">
                <span id="secondDOIError" class="error" style="display:none"></span>
                <div class="pkpFormField__heading">
                    <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.second"}</label>
                </div>
                <input id="secondDOI" type="text" name="secondDOI" class="pkpFormField__input" placeholder="Ex.: 10.1000/182">
            </div>
        </div>
    {/fbvFormArea}
    {fbvFormSection class="pkpFormPage__footer"}
        <div class="pkpFormPage__buttons">
            <input type="submit" class="pkpButton" value="{translate key="common.save"}">
        </div>
    {/fbvFormSection}
</form>