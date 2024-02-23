<?php
/**
 * @file plugins/generic/scieloScreening/ScieloScreeningPlugin.inc.php
 *
 * @class ScieloScreeningPlugin
 * @ingroup plugins_generic_scieloScreening
 *
 * @brief Plugin class for the DefaultScreening plugin.
 */

namespace APP\plugins\generic\scieloScreening;

use PKP\plugins\GenericPlugin;
use APP\core\Application;
use PKP\plugins\Hook;
use PKP\db\DAORegistry;
use APP\facades\Repo;
use PKP\security\Role;
use APP\pages\submission\SubmissionHandler;
use APP\plugins\generic\scieloScreening\classes\components\forms\NumberContributorsForm;
use APP\plugins\generic\scieloScreening\controllers\ScieloScreeningHandler;
use APP\plugins\generic\scieloScreening\classes\ScreeningChecker;

class ScieloScreeningPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (Application::isUnderMaintenance()) {
            return true;
        }

        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('Form::config::after', array($this, 'editContributorForm'));
            Hook::add('TemplateManager::display', [$this, 'modifySubmissionSteps']);
            Hook::add('Submission::validateSubmit', [$this, 'validateSubmissionFields']);
            Hook::add('Template::SubmissionWizard::Section::Review', [$this, 'modifyReviewSections']);
            Hook::add('Schema::get::publication', [$this, 'addOurFieldsToPublicationSchema']);
            Hook::add('Template::Workflow::Publication', [$this, 'addToWorkFlow']);
            Hook::add('Template::Workflow::Publication', [$this, 'addPdfsWarningToGalleysTab']);

            Hook::add('Publication::validatePublish', [$this, 'validateOnPosting']);
            Hook::add('Settings::Workflow::listScreeningPlugins', [$this, 'listPluginScreeningRules']);

            // Hook::add('LoadComponentHandler', [$this, 'setupScieloScreeningHandler']);
        }
        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.scieloScreening.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.scieloScreening.description');
    }

    public function addOurFieldsToPublicationSchema($hookName, $params)
    {
        $schema = &$params[0];

        $schema->properties->{'numberContributors'} = (object) [
            'type' => 'integer',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];

        return false;
    }

    public function setupScieloScreeningHandler($hookName, $params)
    {
        $component = & $params[0];
        if ($component == 'plugins.generic.scieloScreening.controllers.ScieloScreeningHandler') {
            return true;
        }
        return false;
    }

    public function editContributorForm($hookName, $params)
    {
        $formConfig = &$params[0];

        if($formConfig['id'] == 'contributor') {
            foreach ($formConfig['fields'] as &$field) {
                if ($field['name'] == 'affiliation') {
                    $field['isRequired'] = true;
                    break;
                }
            }
        }

        return false;
    }

    public function modifySubmissionSteps($hookName, $params)
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $templateMgr = $params[0];

        if ($request->getRequestedPage() !== 'submission' || $request->getRequestedOp() === 'saved') {
            return false;
        }

        $submission = $request
            ->getRouter()
            ->getHandler()
            ->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        if (!$submission || !$submission->getData('submissionProgress')) {
            return false;
        }

        $publication = $submission->getCurrentPublication();
        $publicationApiUrl = $request->getDispatcher()->url(
            $request,
            Application::ROUTE_API,
            $request->getContext()->getPath(),
            'submissions/' . $submission->getId() . '/publications/' . $publication->getId()
        );
        $numberContributorsForm = new NumberContributorsForm(
            $publicationApiUrl,
            $publication,
        );

        $steps = $templateMgr->getState('steps');
        $steps = array_map(function ($step) use ($numberContributorsForm) {
            if ($step['id'] === 'contributors') {
                $step['sections'][] = [
                    'id' => 'numberContributors',
                    'name' => __('plugins.generic.scieloScreening.section.numberContributors.name'),
                    'description' => __('plugins.generic.scieloScreening.section.numberContributors.description'),
                    'type' => SubmissionHandler::SECTION_TYPE_FORM,
                    'form' => $numberContributorsForm->getConfig(),
                ];
            }
            return $step;
        }, $steps);

        $templateMgr->setState(['steps' => $steps]);

        return false;
    }

    public function validateSubmissionFields($hookName, $params)
    {
        $errors = &$params[0];
        $submission = $params[1];
        $publication = $submission->getCurrentPublication();
        $contributorsErrors = $errors['contributors'] ?? [];
        $filesErrors = $errors['files'] ?? [];

        $screeningHandler = new ScieloScreeningHandler();
        $screeningChecker = new ScreeningChecker();
        $dataScreening = $screeningHandler->getScreeningData($submission);

        if (!$dataScreening['statusAffiliation']) {
            $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.affiliation');
        }

        if (!$dataScreening['statusOrcid']) {
            $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.orcidLeastOne');
        }

        if (!$dataScreening['statusPDFs']) {
            $errorCase = ($dataScreening['numPDFs'] > 1) ? 'manyPdfs' : 'noPdfs';
            $filesErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.' . $errorCase);
        }

        if (!$dataScreening['statusMetadataEnglish']) {
            $errors['metadataEnglish'] = [
                __('plugins.generic.scieloScreening.reviewStep.error.missingMetadataEnglish', ['missingMetadata' => $dataScreening['missingMetadataEnglish']])
            ];
        }

        $numberContributorsInformed = $publication->getData('numberContributors');
        $authors = $publication->getData('authors')->toArray();
        if ($numberContributorsInformed != count($authors)) {
            $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.numberContributors');
        }

        $authorsNames = array_map(function ($author) {
            return $author->getLocalizedGivenName() . $author->getLocalizedFamilyName();
        }, $authors);
        if ($screeningChecker->checkHasUppercaseAuthors($authorsNames)) {
            $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.uppercaseContributors');
        }

        if (!empty($contributorsErrors)) {
            $errors['contributors'] = $contributorsErrors;
        }
        if (!empty($filesErrors)) {
            $errors['files'] = $filesErrors;
        }

        return false;
    }

    public function modifyReviewSections($hookName, $params)
    {
        $step = $params[0]['step'];
        $templateMgr = $params[1];
        $output = &$params[2];
        $context = Application::get()->getRequest()->getContext();
        $submission = $templateMgr->getTemplateVars('submission');

        if ($step == 'details') {
            $output .= $templateMgr->fetch($this->getTemplateResource('reviewMetadataEnglish.tpl'));
        }
    }

    public function statusScreeningFormFilter($output, $templateMgr)
    {
        if (preg_match('/<input[^>]+name="submissionId"[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $match = $matches[0][0];
            $posMatch = $matches[0][1];
            $screeningTemplate = $templateMgr->fetch($this->getTemplateResource('statusScreeningStep4.tpl'));

            $output = substr_replace($output, $screeningTemplate, $posMatch + strlen($match), 0);
            $templateMgr->unregisterFilter('output', array($this, 'statusScreeningFormFilter'));
        }
        return $output;
    }

    public function addToWorkFlow($hookName, $params)
    {
        $smarty = & $params[1];
        $output = & $params[2];
        $submission = $smarty->getTemplateVars('submission');
        $scieloScreeningHandler = new ScieloScreeningHandler();
        $dataScreening = $scieloScreeningHandler->getScreeningData($submission);

        $smarty->assign($dataScreening);
        $output .= sprintf(
            '<tab id="screeningInfo" label="%s">%s</tab>',
            __('plugins.generic.scieloScreening.info.name'),
            $smarty->fetch($this->getTemplateResource('screeningInfo.tpl'))
        );
    }

    public function addPdfsWarningToGalleysTab($hookName, $params)
    {
        $smarty = & $params[1];
        $output = & $params[2];

        $output .= sprintf('%s', $smarty->fetch($this->getTemplateResource('addGalleysWarning.tpl')));
    }

    public function listPluginScreeningRules($hookName, $args)
    {
        $rules = & $args[0];
        $ourRulesSuffix = ['affiliation','orcidLeastOne', 'numberContributors', 'uppercaseContributors', 'numPdfs', 'metadataEnglish'];
        $ourRulesString = "<p>" . $this->getDisplayName() . "<br><br>" . $this->getDescription() .  "<ul>";

        foreach ($ourRulesSuffix as $suffix) {
            $ourRulesString .= '<li>' . __('plugins.generic.scieloScreening.screeningRules.' . $suffix) . '</li>';
        }

        $ourRulesString .= "</ul></p>";
        $rules['scieloScreening'] = $ourRulesString;

        return $rules;
    }

    public function validateOnPosting($hookName, $args)
    {
        $errors = &$args[0];
        $submission = $args[2];
        $scieloScreeningHandler = new ScieloScreeningHandler();
        $statusAuthors = $scieloScreeningHandler->getStatusAuthors($submission);
        $canPostSubmission = true;

        if (!$statusAuthors['statusAffiliation']) {
            $errors = array_merge(
                $errors,
                ['affiliationForAll' => __('plugins.generic.scieloScreening.reviewStep.error.affiliation')]
            );
            $canPostSubmission = false;
        }

        if ($this->userIsAuthor($submission) && !$statusAuthors['statusOrcid']) {
            $errors = array_merge(
                $errors,
                ['orcidLeastOne' => __('plugins.generic.scieloScreening.reviewStep.error.orcidLeastOne')]
            );
            $canPostSubmission = false;
        }

        return $canPostSubmission;
    }

    private function userIsAuthor($submission)
    {
        $currentUser = Application::get()->getRequest()->getUser();
        $currentUserAssignedRoles = array();
        if ($currentUser) {
            $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
            $stageAssignmentsResult = $stageAssignmentDao->getBySubmissionAndUserIdAndStageId($submission->getId(), $currentUser->getId(), $submission->getData('stageId'));

            while ($stageAssignment = $stageAssignmentsResult->next()) {
                $userGroup = Repo::userGroup()->get($stageAssignment->getUserGroupId(), $submission->getData('contextId'));
                $currentUserAssignedRoles[] = (int) $userGroup->getRoleId();
            }
        }

        return $currentUserAssignedRoles[0] == Role::ROLE_ID_AUTHOR;
    }
}
