{if $errorsScreening}
    <link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/statusScreeningStep4.css">

    <div id="statusScreeningStep4">
        <div id="screeningStep4Header">
            <h3>{translate key="plugins.generic.authorDOIScreening.step4.warning"}</h3>
        </div>
        <div class="screeningStep4Body">
            {if $statusDOI == false}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.dois"}</span>
                </div>
            {/if}
            {if $statusAffiliation == false}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.affiliation"}</span>
                </div>
            {/if}
            {if $statusMetadataEnglish == false}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.metadataNotEnglish"} {$textMetadata}</span>
                </div>
            {/if}
            {if $numPDFs == 0}
                <div class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.noPDFs"}</span>
                </div>
            {elseif $numPDFs > 1}
                <div id="manyPDFHeader" class="warningField">
                    <div class="statusNotOkay"></div>
                    <span>{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.header"}</span>
                </div>
                <div id="manyPDFBody">
                    <ul>
                        <li>{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.one"}</li>
                        <li>{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.two"}</li>
                        <li>{translate key="plugins.generic.authorDOIScreening.step4.manyPDFs.three"}</li>
                    </ul>
                </div>
            {/if}
        </div>
    </div>
{/if}