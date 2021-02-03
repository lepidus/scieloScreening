{**
 * plugins/generic/scieloScreening/templates/editDOISubmission.tpl
 *
 * Form for editing DOIs during a submission
 *}

{* If the author is a moderator, he does not need to check the DOIs *}

<div id="msgAffiliation" class="description">
    {translate key="plugins.generic.scieloScreening.submission.warningAffiliation"}
</div>
<script>
    var form = document.getElementById("submitStep3Form");
    var authors = document.getElementById("authorsGridContainer");
    var msg = document.getElementById("msgAffiliation");
    form.insertBefore(msg, authors);
</script>


{if $roleId == ROLE_ID_AUTHOR}
    <label id="contributorsTitle">
        {translate key="plugins.generic.scieloScreening.submission.contributors"}
    </label>
    <div id="boxNumberAuthors">
        <p class="description">
            {translate key="plugins.generic.scieloScreening.submission.numberAuthors"}
            <span class="req">*</span>
        </p>
        <input id="inputNumberAuthors" class="required" type="number" required="1" min="1" max="100">
    </div>

    <script>
        var form = document.getElementById("submitStep3Form");
        var msgAffiliation = document.getElementById('msgAffiliation');
        var contributorsTitle = document.getElementById('contributorsTitle');
        var boxNumberAuthors = document.getElementById('boxNumberAuthors');

        form.insertBefore(contributorsTitle, msgAffiliation);
        form.insertBefore(boxNumberAuthors, msgAffiliation);
    </script>

    {if count($dois) == 0}
    {capture assign=checkAuthorsUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.scieloScreening.controllers.ScieloScreeningHandler" op="checkAuthors" escape=false}{/capture}
    <script>
        var screeningChecked = false;
        var postResponse;

        $(function(){ldelim}
            $("#openDOIModal").click(function(){ldelim}
                $("#DOIModal").addClass("is_visible");
            {rdelim});

            $("#closeDOIModal").click(function(){ldelim}
                $("#DOIModal").removeClass("is_visible");
            {rdelim});

            $("#checkCantScreening").click(function(){ldelim}
                if($(this).is(":checked")){ldelim}
                    $("#errorScreening").css("display", "none");
                    screeningChecked = true;
                {rdelim}
                else if($(this).is(":not(:checked)")){ldelim}
                    $("#errorScreening").css("display", "block");
                    screeningChecked = false;
                {rdelim}
            {rdelim});

            $(".pkp_button.submitFormButton").removeAttr("type").attr("type", "button");
            $(".pkp_button.submitFormButton").click(async function(){ldelim}
                await $.post(
                    "{$checkAuthorsUrl}",
                    {ldelim}
                        submissionId: {$submissionId},
                        numberAuthors: $('#inputNumberAuthors').val()
                    {rdelim},
                    function (result){ldelim}
                        result = JSON.parse(result);
                        postResponse = result;
                    {rdelim}
                );

                if(postResponse['statusNumberAuthors'] == 'error'){ldelim}
                    alert("{translate key="plugins.generic.scieloScreening.required.numberAuthors"}");
                    return;
                {rdelim}

                if(postResponse['statusUppercase'] == 'error'){ldelim}
                    alert("{translate key="plugins.generic.scieloScreening.required.nameUppercase"}");
                {rdelim}

                if(postResponse['statusOrcid'] == 'error'){ldelim}
                    alert("{translate key="plugins.generic.scieloScreening.required.orcidLeastOne"}");
                    return;
                {rdelim}
                
                if(screeningChecked){ldelim}
                    $("#submitStep3Form").submit();
                {rdelim}
                else{ldelim}
                    $("#errorScreening").css("display", "block");
                {rdelim}
            {rdelim});
        {rdelim});
    </script>
    {/if}

    <link rel="stylesheet" type="text/css" href="/plugins/generic/scieloScreening/styles/submissionEdit.css">

    {fbvFormSection}
        <div class="pkp_controllers_grid">
            <div class="header">
                <h4 id="doiTitle">{translate key="plugins.generic.scieloScreening.nome"}</h4>
                <span id="asterix" class="req">*</span>
                {if count($dois) > 0}
                <div id="boxScreening">
                    <p>{translate key="plugins.generic.scieloScreening.doiScreeningDone"}</p>
                </div>
                {else}
                <div>
                    <span id="errorScreening" class="myError" style="display:none">{translate key="plugins.generic.scieloScreening.screeningRequirement"}</span>
                    <div id="boxScreening">
                        <p>{translate key="plugins.generic.scieloScreening.instructions"}</p>
                        <a id="openDOIModal" title="Add DOI">{translate key="plugins.generic.scieloScreening.modal"}</a>
                    </div>
                    <div id="boxCantScreening">
                        <h4>{translate key="plugins.generic.scieloScreening.caseCantScreening"}</h4>
                        <div id="boxCheck">
                            <input id="checkCantScreening" name="checkCantScreening" type="checkbox">
                            <label id="labelCheck" for="checkCantScreening">{translate key="plugins.generic.scieloScreening.declaration"}</label>
                        </div>
                    </div>
                </div>
                {/if}
            </div>
        </div>

        {if count($dois) == 0}
        <div id="DOIModal" class="pkp_modal pkpModalWrapper" tabIndex="-1">
            <div class="pkp_modal_panel" role="dialog" aria-label="Add Contributor">
                <div id="titleModal" class="header">{translate key="plugins.generic.scieloScreening.modal"}</div>
                <a id="closeDOIModal" class="close pkpModalCloseButton">
                    <span class="pkp_screen_reader">{translate key="common.closePanel"}</span>
                </a>
                <div class="content">
                    {include file="../../../plugins/generic/scieloScreening/templates/editDOIForm.tpl"}
                </div>
            </div>
        </div>
        {/if}
    {/fbvFormSection}

{/if} {* $roleId == ROLE_ID_AUTHOR *}