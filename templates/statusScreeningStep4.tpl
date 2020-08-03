{if $doiNotDone || $authorWithoutAffiliation}
    <div id="statusScreeningStep4">
        <div>
            <strong>{translate key="common.warning"}:</strong> {translate key="plugins.generic.authorDOIScreening.step4.warning"}
        </div>
        <ul>
            {if $doiNotDone}
                <li>{translate key="plugins.generic.authorDOIScreening.step4.dois"}</li>
            {/if}
            {if $authorWithoutAffiliation}
                <li>{translate key="plugins.generic.authorDOIScreening.step4.affiliation"}</li>
            {/if}
        </ul>
    </div>
{/if}