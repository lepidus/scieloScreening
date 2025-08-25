{**
 * plugins/generic/scieloScreening/templates/screeningInfo.tpl
 *
 * Template for display info to the moderators
 *}

<link rel="stylesheet" type="text/css" href="/plugins/generic/scieloScreening/styles/screeningInfo.css">

<div id="screeningInfoArea">
    <div id="screeningInfoHeader">
        <h2>{translate key="plugins.generic.scieloScreening.info.name"}</h2>
        <p>{translate key="plugins.generic.scieloScreening.info.description"}</p>
    </div>
    <div id="screeningInfoFields">
        <div id="metadataEnglishInfoField">
            {if $statusMetadataEnglish == true}
                <div id="metadataEnglishHeader" class="headerWithoutBody">
                    <div class="screeningStatusOkay"></div>
                    <span id="metadataEnglishMessage">{translate key="plugins.generic.scieloScreening.info.metadataEnglishOkay"}</span>
                </div>
            {else}
                <div id="metadataEnglishHeader" class="headerWithoutBody">
                    <div class="screeningStatusNotOkay"></div>
                    <span id="metadataEnglishMessage">{translate key="plugins.generic.scieloScreening.info.metadataEnglishNotOkay" missingMetadata=$missingMetadataEnglish}</span>
                </div>
            {/if}
        </div>
        <div id="affiliationInfoField">
            {if $statusAffiliation == true}
                <div id="affiliationHeader" class="headerWithoutBody">
                    <div class="screeningStatusOkay"></div>
                    <span id="affiliationMessage">{translate key="plugins.generic.scieloScreening.info.affiliationOkay"}</span>
                </div>
            {else}
                <div id="affiliationHeader">
                    <div class="screeningStatusNotOkay"></div>
                    <span id="affiliationMessage">{translate key="plugins.generic.scieloScreening.info.affiliationNotOkay"}</span>
                </div>
                <div id="affiliationBody">
                    <ul>
                        {foreach from=$authorsWithoutAffiliation item="authorName"}
                            <li>
                                {$authorName|escape}
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        </div>
        <div id="orcidInfoField">
            {if $statusOrcid == true}
                <div id="orcidHeader" class="headerWithoutBody">
                    <div class="screeningStatusOkay"></div>
                    <span id="orcidMessage">{translate key="plugins.generic.scieloScreening.info.orcidOkay"}</span>
                </div>
            {else}
                <div id="orcidHeader" class="headerWithoutBody">
                    <div class="screeningStatusNotOkay"></div>
                    <span id="orcidMessage">{translate key="plugins.generic.scieloScreening.info.orcidNotOkay"}</span>
                </div>
            {/if}
        </div>
        <div id="numPDFInfoField">
            {if $numPDFs == 0}
                <div id="pdfsHeader" class="headerWithoutBody">
                    <div class="screeningStatusNotOkay"></div>
                    <span id="pdfsMessage">{translate key="plugins.generic.scieloScreening.info.noPDFs"}</span>
                </div>
            {elseif $numPDFs > 1}
                <div id="pdfsHeader">
                    <div class="screeningStatusNotOkay"></div>
                    <span id="pdfsMessage">{translate key="plugins.generic.scieloScreening.info.manyPDFs.header"}</span>
                </div>
                <div id="pdfsBody">
                    <ul>
                        {translate key="plugins.generic.scieloScreening.info.manyPDFs.body"}
                    </ul>
                </div>
            {else}
                <div id="pdfsHeader" class="headerWithoutBody">
                    <div class="screeningStatusOkay"></div>
                    <span id="pdfsMessage">{translate key="plugins.generic.scieloScreening.info.pdfsOkay"}</span>
                </div>
            {/if}
        </div>
        <div id="documentOrcidsInfoField">
            {if $statusDocumentOrcids == 'Okay'}
                <div id="documentOrcidsHeader" class="headerWithoutBody">
                    <div class="screeningStatusOkay"></div>
                    <span id="documentOrcidsMessage">{translate key="plugins.generic.scieloScreening.info.documentOrcidsOkay"}</span>
                </div>
            {elseif str_contains($statusDocumentOrcids, 'Unable')}
                <div id="documentOrcidsHeader" class="headerWithoutBody">
                    <div class="screeningStatusNotOkay"></div>
                    <span id="documentOrcidsMessage">{translate key="plugins.generic.scieloScreening.info.documentOrcids{$statusDocumentOrcids}"}</span>
                </div>
            {else}
                <div id="documentOrcidsHeader">
                    <div class="screeningStatusWarning"></div>
                    <span id="documentOrcidsMessage">{translate key="plugins.generic.scieloScreening.info.documentOrcidsNotOkay.header"}</span>
                </div>
                <div id="documentOrcidsBody">
                    <ul>
                        {translate key="plugins.generic.scieloScreening.info.documentOrcidsNotOkay.body"}
                    </ul>
                </div>
            {/if}
        </div>
    </div>
</div>