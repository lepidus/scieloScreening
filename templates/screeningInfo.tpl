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
        <div id="doiInfoField">
            {if $statusDOI == true}
                <div id="doiHeader">
                    <div class="statusOkay"></div>
                    <span id="doiMessage">{translate key="plugins.generic.scieloScreening.info.doiOkay"}</span>
                </div>
                <div id="doiBody">
                    <ul>
                        {foreach from=$dois item="doi"}
                            <li>
                                <a href="https://doi.org/{$doi->getDOICode()}">{$doi->getDOICode()}</a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {elseif $statusDOI == false && $doisConfirmedAuthorship == false}
                <div id="doiHeader">
                    <div class="statusWarning"></div>
                    <span id="doiMessage">{translate key="plugins.generic.scieloScreening.info.doiWithoutConfirmedAuthorship"}</span>
                </div>
                <div id="doiBody">
                    <ul>
                        {foreach from=$dois item="doi"}
                            <li>
                                <a href="https://doi.org/{$doi->getDOICode()}" target="_blank">{$doi->getDOICode()}</a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {else}
                <div id="doiHeader" class="headerWithoutBody">
                    <div class="statusNotOkay"></div>
                    <span id="doiMessage">{translate key="plugins.generic.scieloScreening.info.doiNotOkay"}</span>
                </div>
            {/if}
        </div>
        <div id="metadataEnglishInfoField">
            {if $statusMetadataEnglish == true}
                <div id="metadataEnglishHeader" class="headerWithoutBody">
                    <div class="statusOkay"></div>
                    <span id="metadataEnglishMessage">{translate key="plugins.generic.scieloScreening.info.metadataEnglishOkay"}</span>
                </div>
            {else}
                <div id="metadataEnglishHeader" class="headerWithoutBody">
                    <div class="statusNotOkay"></div>
                    <span id="metadataEnglishMessage">{translate key="plugins.generic.scieloScreening.info.metadataEnglishNotOkay"} {$textMetadata}</span>
                </div>
            {/if}
        </div>
        <div id="affiliationInfoField">
            {if $statusAffiliation == true}
                <div id="affiliationHeader" class="headerWithoutBody">
                    <div class="statusOkay"></div>
                    <span id="affiliationMessage">{translate key="plugins.generic.scieloScreening.info.affiliationOkay"}</span>
                </div>
            {else}
                <div id="affiliationHeader">
                    <div class="statusNotOkay"></div>
                    <span id="affiliationMessage">{translate key="plugins.generic.scieloScreening.info.affiliationNotOkay"}</span>
                </div>
                <div id="affiliationBody">
                    <ul>
                        {foreach from=$listAuthors item="author"}
                            <li>
                                {$author}
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        </div>
        <div id="orcidInfoField">
            {if $statusOrcid == true}
                <div id="orcidHeader" class="headerWithoutBody">
                    <div class="statusOkay"></div>
                    <span id="orcidMessage">{translate key="plugins.generic.scieloScreening.info.orcidOkay"}</span>
                </div>
            {else}
                <div id="orcidHeader" class="headerWithoutBody">
                    <div class="statusNotOkay"></div>
                    <span id="orcidMessage">{translate key="plugins.generic.scieloScreening.info.orcidNotOkay"}</span>
                </div>
            {/if}
        </div>
        <div id="numPDFInfoField">
            {if $numPDFs == 0}
                <div id="pdfsHeader" class="headerWithoutBody">
                    <div class="statusNotOkay"></div>
                    <span id="pdfsMessage">{translate key="plugins.generic.scieloScreening.step4.noPDFs"}</span>
                </div>
            {elseif $numPDFs > 1}
                <div id="pdfsHeader">
                    <div class="statusNotOkay"></div>
                    <span id="pdfsMessage">{translate key="plugins.generic.scieloScreening.step4.manyPDFs.header"}</span>
                </div>
                <div id="pdfsBody">
                    <ul>
                        <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.one"}</li>
                        <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.two"}</li>
                        <li>{translate key="plugins.generic.scieloScreening.step4.manyPDFs.three"}</li>
                    </ul>
                </div>
            {else}
                <div id="pdfsHeader" class="headerWithoutBody">
                    <div class="statusOkay"></div>
                    <span id="pdfsMessage">{translate key="plugins.generic.scieloScreening.info.pdfsOkay"}</span>
                </div>
            {/if}
        </div>
    </div>
</div>