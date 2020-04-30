{**
 * plugins/generic/authorDOIScreening/templates/screeningInfo.tpl
 *
 * Template for display info to the moderators
 *}

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/screeningInfo.css">

<div id="screeningInfoArea">
    <div id="screeningInfoHeader">
        <h2>{translate key="plugins.generic.authorDOIScreening.info.name"}</h2>
        <p>{translate key="plugins.generic.authorDOIScreening.info.description"}</p>
    </div>
    <div id="screeningInfoFields">
        <div id="doiInfoField">
            {if $flagDOI == true}
                <div id="doiHeader">
                    <div class="statusOkay"></div>
                    <span id="doiMessage">{$msgDOI}</span>
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
            {else}
                <div id="doiHeader" class="headerWithoutBody">
                    <div class="statusNotOkay"></div>
                    <span id="doiMessage">{$msgDOI}</span>
                </div>
            {/if}
        </div>
        <div id="affiliationInfoField">
            {if $flagAf == true}
                <div id="affiliationHeader" class="headerWithoutBody">
                    <div class="statusOkay"></div>
                    <span id="affiliationMessage">{$msgAf}</span>
                </div>
            {else}
                <div id="affiliationHeader">
                    <div class="statusNotOkay"></div>
                    <span id="affiliationMessage">{$msgAf}</span>
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
    </div>
</div>