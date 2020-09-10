{if $errorsScreening}
    <link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/statusScreeningStep4.css">

    <div id="statusScreeningStep4">
        <div id="screeningStep4Header">
            <h3>{translate key="plugins.generic.authorDOIScreening.step4.warning"}</h3>
        </div>
        <div class="screeningStep4Body">
            {if $doiNotDone}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.dois"}</span>
                </div>
            {/if}
            {if $authorWithoutAffiliation}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.affiliation"}</span>
                </div>
            {/if}
            {if $metadataNotEnglish}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.metadataNotEnglish"} {$textMetadata}</span>
                </div>
            {/if}
            {if $noPDFs}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.noPDFs"}</span>
                </div>
            {/if}
        </div>
    </div>
{/if}