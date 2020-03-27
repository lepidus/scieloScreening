{**
 * plugins/generic/authorDOIScreening/templates/editDOISubmission.tpl
 *
 * Form for editing DOIs during a submission
 *}

<script>
    $(function(){ldelim}
        $("#openDOIModal").click(function(){ldelim}
            $("#DOIModal").addClass("is_visible");
        {rdelim});

        $("#closeDOIModal").click(function(){ldelim}
            $("#DOIModal").removeClass("is_visible");
        {rdelim});
    {rdelim});
</script>

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/submissionEdit.css">

{fbvFormSection}
    <div class="pkp_controllers_grid">
        <div class="header">
            <h4>{translate key="plugins.generic.authorDOIScreening.nome"}</h4>
            <ul class="actions">
                <li>
                    <a id="openDOIModal" title="Add DOI" class="pkp_controllers_linkAction">{translate key="plugins.generic.authorDOIScreening.modal"}</a>
                </li>
            </ul>
        </div>
    </div>

    {* Nao esquece da classe is_visible*}
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
{/fbvFormSection}