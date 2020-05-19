{**
 * plugins/generic/authorDOIScreening/templates/editDOISubmission.tpl
 *
 * Form for editing DOIs during a submission
 *}

 {* Caso o autor seja um moderador, este não precisa fazer a verificação dos DOIs *}

<script>
    var formulario = document.getElementById("submitStep3Form");
    var authors = document.getElementById("authorsGridContainer");

    var msg = document.createElement("P");
    msg.classList.add("description");
    msg.innerText = "{translate key="plugins.generic.authorDOIScreening.submission.warningAffiliation"}";
    
    formulario.insertBefore(msg, authors);
</script>

{if $roleId == ROLE_ID_AUTHOR}

{if count($dois) == 0}
<script>
    var screeningChecked = false;

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
        $(".pkp_button.submitFormButton").click(function(){ldelim}
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

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/submissionEdit.css">

{fbvFormSection}
    <div class="pkp_controllers_grid">
        <div class="header">
            <h4 id="doiTitle">{translate key="plugins.generic.authorDOIScreening.nome"}</h4>
            <span id="asterix" class="req">*</span>
            {if count($dois) > 0}
            <div id="boxScreening">
                <p>{translate key="plugins.generic.authorDOIScreening.doiScreeningDone"}</p>
            </div>
            {else}
            <div>
                <span id="errorScreening" class="myError" style="display:none">{translate key="plugins.generic.authorDOIScreening.screeningRequirement"}</span>
                <div id="boxScreening">
                    <p>{translate key="plugins.generic.authorDOIScreening.instructions"}</p>
                    <a id="openDOIModal" title="Add DOI">{translate key="plugins.generic.authorDOIScreening.modal"}</a>
                </div>
                <div id="boxCantScreening">
                    <h4>{translate key="plugins.generic.authorDOIScreening.caseCantScreening"}</h4>
                    <div id="boxCheck">
                        <input id="checkCantScreening" name="checkCantScreening" type="checkbox">
                        <label id="labelCheck" for="checkCantScreening">{translate key="plugins.generic.authorDOIScreening.declaration"}</label>
                    </div>
                </div>
            </div>
            {/if}
        </div>
    </div>

    {if count($dois) == 0}
    <div id="DOIModal" class="pkp_modal pkpModalWrapper" tabIndex="-1">
        <div class="pkp_modal_panel" role="dialog" aria-label="Add Contributor">
            <div id="titleModal" class="header">{translate key="plugins.generic.authorDOIScreening.modal"}</div>
            <a id="closeDOIModal" class="close pkpModalCloseButton">
                <span class="pkp_screen_reader">{translate key="common.closePanel"}</span>
            </a>
            <div class="content">
                {include file="../../../plugins/generic/authorDOIScreening/templates/editDOIForm.tpl"}
            </div>
        </div>
    </div>
    {/if}
{/fbvFormSection}

{/if}