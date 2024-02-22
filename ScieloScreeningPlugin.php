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

            // Hook::add('Publication::validatePublish', [$this, 'validate']);

            // Hook::add('Settings::Workflow::listScreeningPlugins', [$this, 'listRules']);

            // Hook::add('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', [$this, 'metadataFieldEdit']);
            // Hook::add('Template::Workflow::Publication', [$this, 'addGalleysWarning']);

            // Hook::add('LoadComponentHandler', [$this, 'setupScieloScreeningHandler']);
            // Hook::add('submissionsubmitstep2form::validate', [$this, 'addValidationToStep2']);
            // Hook::add('submissionsubmitstep3form::validate', [$this, 'addValidationToStep3']);
            // Hook::add('submissionsubmitstep4form::display', [$this, 'addToStep4']);
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

    public function addValidationToStep2($hookName, $params)
    {
        $form = & $params[0];
        $submission = $form->submission;

        $checker = new ScreeningChecker();
        $galleys = $submission->getGalleys();
        $galleysFileTypes = array_map(function ($galley) {
            return ($galley->getFileType());
        }, $galleys);

        if (!$checker->checkNumberPdfs($galleysFileTypes)[0]) {
            $form->addErrorField('submitStep2FormNotification');
            $form->addError('submitStep2FormNotification', __("plugins.generic.scieloScreening.required.numberPDFs"));
            return;
        }
    }

    public function addValidationToStep3($hookName, $params)
    {
        $form = & $params[0];
        $form->readUserVars(array('inputNumberAuthors', 'checkCantScreening'));
        $submission = $form->submission;
        if (!$this->userIsAuthor($submission)) {
            return;
        }

        $inputNumberAuthors = $form->getData('inputNumberAuthors');
        $checkCantScreening = $form->getData('checkCantScreening');

        $checker = new ScreeningChecker();
        $authors = $submission->getAuthors();
        if ($inputNumberAuthors != count($authors)) {
            $form->addErrorField('submitStep2FormNotification');
            $form->addError('submitStep2FormNotification', __("plugins.generic.scieloScreening.required.numberAuthors"));
            return;
        };

        $nameAuthors = array_map(function ($author) {
            return $author->getLocalizedGivenName() . $author->getLocalizedFamilyName();
        }, $authors);
        if ($checker->checkHasUppercaseAuthors($nameAuthors)) {
            $form->addErrorField('authorsGridContainer');
            $form->addError('authorsGridContainer', __("plugins.generic.scieloScreening.required.nameUppercase"));
            return;
        }

        $orcidAuthors = array_map(function ($author) {
            return $author->getOrcid();
        }, $authors);
        if (!$checker->checkOrcidAuthors($orcidAuthors)) {
            $form->addErrorField('authorsGridContainer');
            $form->addError('authorsGridContainer', __("plugins.generic.scieloScreening.required.orcidLeastOne"));
            return;
        }

        $doisInformedAtScreening = DAORegistry::getDAO('DOIScreeningDAO')->getBySubmissionId($submission->getId());
        $doiScreeningDone = (count($doisInformedAtScreening) > 0);
        if ($checkCantScreening != "1" && !$doiScreeningDone) {
            $form->addErrorField('errorScreening');
            $form->addError('errorScreening', __("plugins.generic.scieloScreening.required.doiScreening"));
            return;
        }
    }

    public function addToStep4($hookName, $params)
    {
        $submission = $params[0]->submission;
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $scieloScreeningHandler = new ScieloScreeningHandler();
        $dataScreening = $scieloScreeningHandler->getScreeningData($submission);
        $templateMgr->assign($dataScreening);
        $templateMgr->registerFilter("output", array($this, 'statusScreeningFormFilter'));

        return false;
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

    public function metadataFieldEdit($hookName, $params)
    {
        $smarty = & $params[1];
        $output = & $params[2];

        $submissionId = $smarty->smarty->get_template_vars('submissionId');
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($submissionId);

        $dois = DAORegistry::getDAO('DOIScreeningDAO')->getBySubmissionId($submissionId);

        $smarty->assign([
            'userIsAuthor' => $this->userIsAuthor($submission),
            'dois' => $dois
        ]);

        $output .= $smarty->fetch($this->getTemplateResource('editDOISubmission.tpl'));
        return false;
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

    public function addGalleysWarning($hookName, $params)
    {
        $smarty = & $params[1];
        $output = & $params[2];

        $output .= sprintf('%s', $smarty->fetch($this->getTemplateResource('addGalleysWarning.tpl')));
    }

    public function listRules($hookName, $args)
    {
        $rules = & $args[0];
        $pluginRules['hasPublishedBefore'] =
            "<p>" . $this->getDisplayName() . "<br />\n" .
            $this->getDescription() . "</p>\n";
        $rules = array_merge($rules, $pluginRules);
        return $rules;
    }

    public function validate($hookName, $args)
    {
        $errors = & $args[0];
        $submission = $args[2];
        $scieloScreeningHandler = new ScieloScreeningHandler();
        $statusAuthors = $scieloScreeningHandler->getStatusAuthors($submission);
        $okayForPublishing = true;

        if (!$statusAuthors['statusAffiliation']) {
            $errors = array_merge(
                $errors,
                array('affiliationForAll' => __('plugins.generic.scieloScreening.required.affiliationForAll'))
            );
            $okayForPublishing = false;
        }

        if ($this->userIsAuthor($submission) && !$statusAuthors['statusOrcid']) {
            $errors = array_merge(
                $errors,
                array('orcidLeastOne' => __('plugins.generic.scieloScreening.required.orcidLeastOne'))
            );
            $okayForPublishing = false;
        }

        return $okayForPublishing;
    }

    private function userIsAuthor($submission)
    {
        $currentUser = \Application::get()->getRequest()->getUser();
        $currentUserAssignedRoles = array();
        if ($currentUser) {
            $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
            $stageAssignmentsResult = $stageAssignmentDao->getBySubmissionAndUserIdAndStageId($submission->getId(), $currentUser->getId(), $submission->getData('stageId'));
            $userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
            while ($stageAssignment = $stageAssignmentsResult->next()) {
                $userGroup = $userGroupDao->getById($stageAssignment->getUserGroupId(), $submission->getData('contextId'));
                $currentUserAssignedRoles[] = (int) $userGroup->getRoleId();
            }
        }

        return $currentUserAssignedRoles[0] == ROLE_ID_AUTHOR;
    }
}
