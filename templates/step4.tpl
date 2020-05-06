{**
 * templates/submission/form/step4.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Step 4 of author submission.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the JS form handler.
		$('#submitStep4Form').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="submitStep4Form" method="post" action="{url op="saveStep" path=$submitStep}">
	{csrf}
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="submitStep4FormNotification"}

    {if $doiNotDone || $authorWithoutAffiliation}
        <p><strong>{translate key="common.warning"}:</strong> {translate key="plugins.generic.authorDOIScreening.step4.warning"}</p>
        <ul>
            {if $doiNotDone}
                <li>{translate key="plugins.generic.authorDOIScreening.step4.dois"}</li>
            {/if}
            {if $authorWithoutAffiliation}
                <li>{translate key="plugins.generic.authorDOIScreening.step4.affiliation"}</li>
            {/if}
        </ul>
    {/if}

	<p>{translate key="submission.confirm.message"}</p>

	{fbvFormButtons id="step4Buttons" submitText="submission.submit.finishSubmission" confirmSubmit="submission.confirmSubmit"}
</form>
