{**
 * templates/settingsFormEmptySecret.tpl
 *
 * Copyright (c) 2025 SciELO
 * Copyright (c) 2025 Lepidus Tecnologia
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * SciELO Screening plugin settings when there is no api_key_secret configured.
 *
*}



{if $missingDocumentScreeningRequirements}
    <p role="alert">{translate key="plugins.generic.scieloScreening.settings.missingDocumentRequirements" requirements=$missingDocumentScreeningRequirements|escape}</p>
{/if}
{translate key="plugins.generic.scieloScreening.settings.emptyApiSecretKey"}