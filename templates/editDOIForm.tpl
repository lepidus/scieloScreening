{**
 * plugins/generic/scieloScreening/templates/editDOIs.tpl
 *
 * Form for add/edit DOIs from a submission
 *}

{capture assign=addDoiUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.scieloScreening.controllers.ScieloScreeningHandler" op="addDOIs" escape=false}{/capture}
{capture assign=validateDoiUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.scieloScreening.controllers.ScieloScreeningHandler" op="validateDOI" escape=false}{/capture}
{capture assign=validateDoisFromScreeningUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.scieloScreening.controllers.ScieloScreeningHandler" op="validateDoisFromScreening" escape=false}{/capture}


<script>
    var doisOkay = [false, false, false];
    var doisYears = [0,0,0];

    function sucessoScreening(){ldelim}
        $('#generalMessage').text("{translate key="plugins.generic.scieloScreening.successfulScreening"}");
        $("#generalMessage").removeClass("myError");
        $("#generalMessage").addClass("mySuccess");
        $('#generalMessage').css('display', 'block');

        screeningChecked = true;
        $("#boxCantScreening").css("display", "none");
    {rdelim}

    async function makeSubmit(e){ldelim}
        var postValidateDoisResponse;

        await $.post(
            "{$validateDoisFromScreeningUrl}",
            {ldelim}
                doisOkay: doisOkay,
                doisYears: doisYears,
                dois: [$('#firstDOI').val(), $('#secondDOI').val(), $('#thirdDOI').val()]
            {rdelim},
            function (result){ldelim}
                result = JSON.parse(result);
                postValidateDoisResponse = result;
            {rdelim}
        );

        if(postValidateDoisResponse['statusValidateDois'] == 0){ldelim}
            $("#generalMessage").text(postValidateDoisResponse['messageError']);
            $('#generalMessage').css('display', 'block');
            return;
        {rdelim}

        var doisToSave = [
            (doisOkay[0]) ? ($('#firstDOI').val()) : (""),
            (doisOkay[1]) ? ($('#secondDOI').val()) : (""),
            (doisOkay[2]) ? ($('#thirdDOI').val()) : ("")
        ];

        $.post(
            "{$addDoiUrl}",
            {ldelim}
                submissionId: {$submissionId},
                doisToSave: doisToSave
            {rdelim},
            sucessoScreening()
        );
    {rdelim}

    async function validaDOI(doiInput, doiError, flag){ldelim}
        var postValidateResponse;
        
        await $.post(
            "{$validateDoiUrl}",
            {ldelim}
                doiString: doiInput.val(),
                submissionId: {$submissionId}
            {rdelim},
            function (result){ldelim}
                result = JSON.parse(result);
                postValidateResponse = result;
            {rdelim}
        );
        
        if(postValidateResponse['statusValidate'] == 0){ldelim}
            doiError.text(postValidateResponse['messageError']);
            doiError.css('display', 'block');
            doisOkay[flag] = false;
            return;
        {rdelim}

        if(doiError.css('display') == 'block')
            doiError.css('display', 'none');

        doisOkay[flag] = true;
        doisYears[flag] = postValidateResponse['yearArticle'];
    {rdelim}

    $(function(){ldelim}
        $('#firstDOI').focusout(function () {ldelim} validaDOI($('#firstDOI'), $('#firstDOIError'), 0) {rdelim});
        $('#secondDOI').focusout(function() {ldelim} validaDOI($('#secondDOI'), $('#secondDOIError'), 1) {rdelim});
        $('#thirdDOI').focusout(function() {ldelim} validaDOI($('#thirdDOI'), $('#thirdDOIError'), 2) {rdelim});
        $('#doiSubmit').click(makeSubmit);
    {rdelim});
</script>

<link rel="stylesheet" type="text/css" href="/plugins/generic/scieloScreening/styles/submissionForm.css">

<div id="doiForm">
    <div id="doiFormArea">
        <h2>{translate key="plugins.generic.scieloScreening.nome"}</h2>
        <p>{translate key="plugins.generic.scieloScreening.submission.description"}</p>
        <span id="generalMessage" class="myError" style="display:none"></span>
        <div id="formFields">
            <div id="firstFormField">
                <span id="firstDOIError" class="myError" style="display:none"></span>
                <label id="firstDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.scieloScreening.submission.first"}</label>
                {if isset($firstDOI)}
                    <input id="firstDOI" type="text" name="firstDOI" placeholder="Ex.: 10.1000/182" value="{$firstDOI->getDOICode()}">
                {else}
                    <input id="firstDOI" type="text" name="firstDOI" placeholder="Ex.: 10.1000/182">
                {/if}
            </div>
            <div id="secondFormField">
                <span id="secondDOIError" class="myError" style="display:none"></span>
                <label id="secondDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.scieloScreening.submission.second"}</label>
                {if isset($secondDOI)}
                    <input id="secondDOI" type="text" name="secondDOI" placeholder="Ex.: 10.1000/182" value="{$secondDOI->getDOICode()}">
                {else}
                    <input id="secondDOI" type="text" name="secondDOI" placeholder="Ex.: 10.1000/182">
                {/if}
            </div>
            <div id="thirdFormField">
                <span id="thirdDOIError" class="myError" style="display:none"></span>
                <label id="thirdDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.scieloScreening.submission.third"}</label>
                {if isset($thirdDOI)}
                    <input id="thirdDOI" type="text" name="thirdDOI" placeholder="Ex.: 10.1000/182" value="{$thirdDOI->getDOICode()}">
                {else}
                    <input id="thirdDOI" type="text" name="thirdDOI" placeholder="Ex.: 10.1000/182">
                {/if}
            </div>
        </div>
    </div>
    <div id="doiFormFooter">
        <div id="formButtons">
            <button id="doiSubmit" type="button" class="pkpButton">{translate key="common.save"}</button>
        </div>
    </div>
</div>