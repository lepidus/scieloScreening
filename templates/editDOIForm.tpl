{**
 * plugins/generic/scieloScreening/templates/editDOIs.tpl
 *
 * Form for add/edit DOIs from a submission
 *}

{capture assign=addDOIUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.scieloScreening.controllers.ScieloScreeningHandler" op="addDOIs" escape=false}{/capture}
{capture assign=validateDOIUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.scieloScreening.controllers.ScieloScreeningHandler" op="validateDOI" escape=false}{/capture}
{capture assign=validateDOIsFromScreeningUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.scieloScreening.controllers.ScieloScreeningHandler" op="validateDOIsFromScreening" escape=false}{/capture}


<script>
    var doisOkay = [false, false, false];
    var doisYears = [0,0,0];
    var doisConfirmedAuthorship = [1, 1, 1];

    function sucessoScreening(){ldelim}
        $('#generalMessage').text("{translate key="plugins.generic.scieloScreening.successfulScreening"}");
        $("#generalMessage").removeClass("myError");
        $("#generalMessage").addClass("mySuccessGeneral");
        $('#generalMessage').css('display', 'block');

        $("#boxCantScreening").css("display", "none");

        delayTime = 3 * 1000;
        setTimeout(function() {
            $("#DOIModal").removeClass("is_visible");
        }, delayTime);
    {rdelim}

    async function makeSubmit(e){ldelim}
        var postValidateDOIsResponse;

        await $.post(
            "{$validateDOIsFromScreeningUrl}",
            {ldelim}
                doisOkay: doisOkay,
                doisYears: doisYears,
                dois: [$('#firstDOI').val(), $('#secondDOI').val(), $('#thirdDOI').val()]
            {rdelim},
            function (result){ldelim}
                result = JSON.parse(result);
                postValidateDOIsResponse = result;
            {rdelim}
        );

        if(postValidateDOIsResponse['statusValidateDOIs'] == 0){ldelim}
            $("#generalMessage").text(postValidateDOIsResponse['messageError']);
            $('#generalMessage').css('display', 'block');
            return;
        {rdelim}

        var doisToSave = [
            (doisOkay[0]) ? ([$('#firstDOI').val(), doisConfirmedAuthorship[0]]) : (""),
            (doisOkay[1]) ? ([$('#secondDOI').val(), doisConfirmedAuthorship[1]]) : (""),
            (doisOkay[2]) ? ([$('#thirdDOI').val(), doisConfirmedAuthorship[2]]) : ("")
        ];

        $.post(
            "{$addDOIUrl}",
            {ldelim}
                submissionId: {$submissionId},
                doisToSave: doisToSave
            {rdelim},
            sucessoScreening()
        );
    {rdelim}

    async function validaDOI(doiInput, doiMessage, flag){ldelim}
        var postValidateResponse;
        
        doiMessage.text("{translate key="plugins.generic.scieloScreening.warningValidatingDOI"}");
        doiMessage.removeClass();
        doiMessage.addClass("myWarning");
        doiMessage.css('display', 'block');
        
        await $.post(
            "{$validateDOIUrl}",
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
            doiMessage.text(postValidateResponse['messageError']);
            doiMessage.removeClass();
            doiMessage.addClass("myError");
            doiMessage.css('display', 'block');
            doisOkay[flag] = false;
            return;
        {rdelim}

        doiMessage.text("{translate key="plugins.generic.scieloScreening.DOIValidated"}");
        doiMessage.removeClass();
        doiMessage.addClass("mySuccess");
        doiMessage.css('display', 'block');

        doisOkay[flag] = true;
        doisYears[flag] = postValidateResponse['yearArticle'];
        doisConfirmedAuthorship[flag] = postValidateResponse['doiConfirmedAuthorship'] ? 1 : 0;
    {rdelim}

    $(function(){ldelim}
        $('#firstDOI').focusout(function () {ldelim} validaDOI($('#firstDOI'), $('#firstDOIMessage'), 0) {rdelim});
        $('#secondDOI').focusout(function() {ldelim} validaDOI($('#secondDOI'), $('#secondDOIMessage'), 1) {rdelim});
        $('#thirdDOI').focusout(function() {ldelim} validaDOI($('#thirdDOI'), $('#thirdDOIMessage'), 2) {rdelim});
        $('#doiSubmit').click(makeSubmit);
    {rdelim});
</script>

<link rel="stylesheet" type="text/css" href="/plugins/generic/scieloScreening/styles/submissionForm.css">

<div id="doiForm">
    <div id="doiFormArea">
        <h2>{translate key="plugins.generic.scieloScreening.doiScreeningLabel"}</h2>
        <p>{translate key="plugins.generic.scieloScreening.submission.description"}</p>
        <p>{translate key="plugins.generic.scieloScreening.submission.waitDOIValidation"}</p>
        <span id="generalMessage" class="myError" style="display:none"></span>
        <div id="formFields">
            <div id="firstFormField">
                <span id="firstDOIMessage" style="display:none"></span>
                <label id="firstDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.scieloScreening.submission.first"}</label>
                {if isset($firstDOI)}
                    <input id="firstDOI" type="text" name="firstDOI" placeholder="Ex.: 10.1000/182" value="{$firstDOI->getDOICode()}">
                {else}
                    <input id="firstDOI" type="text" name="firstDOI" placeholder="Ex.: 10.1000/182">
                {/if}
            </div>
            <div id="secondFormField">
                <span id="secondDOIMessage" style="display:none"></span>
                <label id="secondDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.scieloScreening.submission.second"}</label>
                {if isset($secondDOI)}
                    <input id="secondDOI" type="text" name="secondDOI" placeholder="Ex.: 10.1000/182" value="{$secondDOI->getDOICode()}">
                {else}
                    <input id="secondDOI" type="text" name="secondDOI" placeholder="Ex.: 10.1000/182">
                {/if}
            </div>
            <div id="thirdFormField">
                <span id="thirdDOIMessage" style="display:none"></span>
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