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
    msg.setAttribute("id", "msgAffiliation");
    msg.classList.add("description");
    msg.innerText = "{translate key="plugins.generic.authorDOIScreening.submission.warningAffiliation"}";
    
    formulario.insertBefore(msg, authors);
</script>

{if $roleId == ROLE_ID_AUTHOR}
{capture assign=checkAuthorsUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.authorDOIScreening.controllers.grid.DOIGridHandler" op="checkAuthors" escape=false}{/capture}

<script>
    var formulario = document.getElementById("submitStep3Form");
    var msgAffiliation = document.getElementById('msgAffiliation');
    
    var titulo = document.createElement('label');
    var caixa = document.createElement('div');
    var msgNumber = document.createElement('p');
    var hispano = document.createElement('span');
    var inputNumber = document.createElement('input');

    caixa.setAttribute('id', 'boxNumberAuthors');
    titulo.innerText = "{translate key="plugins.generic.authorDOIScreening.submission.contributors"}";
    msgNumber.classList.add('description');
    msgNumber.innerText = "{translate key="plugins.generic.authorDOIScreening.submission.numberAuthors"}";
    hispano.innerText = "*";
    hispano.classList.add('req');
    inputNumber.setAttribute('id', 'inputNumberAuthors');
    inputNumber.classList.add('required');
    inputNumber.setAttribute('type', 'number');
    inputNumber.setAttribute('required', '1');
    inputNumber.setAttribute('min', '1');
    inputNumber.setAttribute('max', '100');

    caixa.appendChild(msgNumber); msgNumber.appendChild(hispano); caixa.appendChild(inputNumber);
    formulario.insertBefore(titulo, msgAffiliation);
    formulario.insertBefore(caixa, msgAffiliation);
</script>

{if count($dois) == 0}
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
                function (resultado){ldelim}
                    resultado = JSON.parse(resultado);
                    postResponse = resultado;
                {rdelim}
            );

            if(postResponse['statusNumberAuthors'] == 'error'){ldelim}
                alert("{translate key="plugins.generic.authorDOIScreening.required.numberAuthors"}");
                return;
            {rdelim}

            if(postResponse['statusUppercase'] == 'error'){ldelim}
                alert("{translate key="plugins.generic.authorDOIScreening.required.nameUppercase"}");
            {rdelim}

            if(postResponse['statusOrcid'] == 'error'){ldelim}
                alert("{translate key="plugins.generic.authorDOIScreening.required.orcidLeastOne"}");
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