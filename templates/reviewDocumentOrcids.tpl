<div class="submissionWizard__reviewPanel">
    <div class="submissionWizard__reviewPanel__header">
        <h3 id="documentOrcids">{translate key="plugins.generic.scieloScreening.reviewStep.error.documentOrcidsHeader"}</h3>
        <pkp-button
            aria-describedby="review-plugin-scielo-screening-orcids"
            class="submissionWizard__reviewPanel__edit"
            @click="openStep('{$step.id}')"
        >
            {translate key="common.edit"}
        </pkp-button>
    </div>
    <div class="submissionWizard__reviewPanel__body">
        <div class="submissionWizard__reviewPanel__item" >
            {if $statusDocumentOrcids == 'NotOkay'}
                {translate key="plugins.generic.scieloScreening.info.documentOrcidsNotOkay.header"}
                <ul>
                    {translate key="plugins.generic.scieloScreening.info.documentOrcidsNotOkay.body"}
                </ul>
            {else}
                {translate key="plugins.generic.scieloScreening.info.documentOrcids{$statusDocumentOrcids}"}
            {/if}
        </div>
    </div>
</div>