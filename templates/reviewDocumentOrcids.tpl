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
            {if $statusDocumentOrcids == 'Unable'}
                {translate key="plugins.generic.scieloScreening.info.documentOrcidsUnable"}
            {elseif $statusDocumentOrcids == 'NotOkay'}
                {translate key="plugins.generic.scieloScreening.info.documentOrcidsNotOkay.header"}
            {/if}
            {if $statusDocumentOrcids == 'NotOkay'}
                <ul>
                    {translate key="plugins.generic.scieloScreening.info.documentOrcidsNotOkay.body"}
                </ul>
            {/if}
        </div>
    </div>
</div>