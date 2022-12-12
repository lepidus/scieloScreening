{if $errorsScreening}
    <link rel="stylesheet" type="text/css" href="/plugins/generic/scieloScreening/styles/statusScreeningStep4.css">

    <div id="statusScreeningStep4">
        <div id="screeningStep4Header">
            <h2>{translate key="plugins.generic.scieloScreening.displayName"}</h2>
            <h3>{translate key="plugins.generic.scieloScreening.step4.warning"}</h3>
        </div>
        <div class="screeningStep4Body">
            {if $statusDOI == false && $doisConfirmedAuthorship == true}
                <div class="screeningWarningField">
                    <div class="screeningStatusWarning"></div>
                    <span>{translate key="plugins.generic.scieloScreening.step4.dois"}</span>
                </div>
            {elseif $statusDOI == false && $doisConfirmedAuthorship == false}
                <div id="doiHeader">
                    <div class="screeningStatusWarning"></div>
                    <span id="doiMessage">{translate key="plugins.generic.scieloScreening.step4.doisWithoutConfirmedAuthorship"}</span>
                </div>
                <div id="doiBody">
                    <span>{translate key="plugins.generic.scieloScreening.info.authorNameMetadata" authorName=$authorFromSubmission}</span><br>
                    <span>{translate key="plugins.generic.scieloScreening.info.doisInformed"}</span><br>
                    <ul>
                        {foreach from=$dois key="i" item="doi"}
                            <li>
                                <a href="https://doi.org/{$doi->getDOICode()}" target="_blank" rel="noopener noreferrer">{$doi->getDOICode()}</a><br>
                                <span>{translate key="plugins.generic.scieloScreening.info.namesPresentInDOI" authorsNames=$authorsFromDOIs[$i]}</span>
                            </li>
                        {/foreach}
                    </ul>
                    
                
                </div>
            {/if}
            {if $statusAffiliation == false}
                <div class="screeningWarningField">
                    <div class="screeningStatusNotOkay"></div>
                    <span>{translate key="plugins.generic.scieloScreening.step4.affiliation"}</span>
                </div>
            {/if}
            {if $statusMetadataEnglish == false}
                <div class="screeningWarningField">
                    <div class="screeningStatusNotOkay"></div>
                    <span>{translate key="plugins.generic.scieloScreening.step4.metadataNotEnglish"} {$textMetadataScreening}</span>
                </div>
            {/if}
            {if $numPDFs == 0}
                <div class="screeningWarningField">
                    <div class="screeningStatusNotOkay"></div>
                    <span>{translate key="plugins.generic.scieloScreening.step4.noPDFs"}</span>
                </div>
            {elseif $numPDFs > 1}
                <div id="manyPDFHeader" class="screeningWarningField">
                    <div class="screeningStatusNotOkay"></div>
                    <span>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.header"}</span>
                </div>
                <div id="manyPDFBody">
                    <ul>
                        <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.one"}</li>
                        <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.two"}</li>
                        <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.three"}</li>
                    </ul>
                </div>
            {/if}
        </div>
    </div>
{/if}