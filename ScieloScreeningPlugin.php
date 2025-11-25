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
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use APP\pages\submission\SubmissionHandler;
use Illuminate\Database\Migrations\Migration;
use APP\plugins\generic\scieloScreening\classes\components\forms\NumberContributorsForm;
use APP\plugins\generic\scieloScreening\classes\ScreeningExecutor;
use APP\plugins\generic\scieloScreening\classes\ScreeningChecker;
use APP\plugins\generic\scieloScreening\classes\DocumentChecker;
use APP\plugins\generic\scieloScreening\classes\OrcidClient;
use APP\plugins\generic\scieloScreening\classes\migration\EncryptLegacyCredentials;
use APP\plugins\generic\scieloScreening\ScieloScreeningSettingsForm;

class ScieloScreeningPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (Application::isUnderMaintenance()) {
            return true;
        }

        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('TemplateManager::display', [$this, 'modifySubmissionSteps']);
            Hook::add('Submission::validateSubmit', [$this, 'validateSubmissionFields']);
            Hook::add('Author::validate', [$this, 'validateAuthorData']);
            Hook::add('Template::SubmissionWizard::Section::Review', [$this, 'modifyReviewSections']);
            Hook::add('Schema::get::publication', [$this, 'addOurFieldsToPublicationSchema']);
            Hook::add('Template::Workflow::Publication', [$this, 'addToWorkFlow']);
            Hook::add('Template::Workflow::Publication', [$this, 'addPdfsWarningToGalleysTab']);

            Hook::add('Publication::validatePublish', [$this, 'validateOnPosting']);
            Hook::add('Settings::Workflow::listScreeningPlugins', [$this, 'listPluginScreeningRules']);

            $this->loadDispatcherClasses();
        }
        return $success;
    }

    private function loadDispatcherClasses(): void
    {
        $dispatcherClasses = [
            'FormsEditDispatcher'
        ];

        foreach ($dispatcherClasses as $dispatcherClass) {
            $dispatcherClass = 'APP\plugins\generic\scieloScreening\classes\dispatchers\\' . $dispatcherClass;
            $dispatcher = new $dispatcherClass($this);
        }
    }

    public function getDisplayName()
    {
        return __('plugins.generic.scieloScreening.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.scieloScreening.description');
    }

    public function getInstallMigration(): Migration
    {
        return new EncryptLegacyCredentials();
    }

    public function getActions($request, $actionArgs)
    {
        $router = $request->getRouter();
        return array_merge(
            array(
                new LinkAction(
                    'settings',
                    new AjaxModal($router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')), $this->getDisplayName()),
                    __('manager.plugins.settings'),
                    null
                ),
            ),
            parent::getActions($request, $actionArgs)
        );
    }

    public function manage($args, $request)
    {
        $context = $request->getContext();
        $contextId = ($context == null) ? 0 : $context->getId();

        switch ($request->getUserVar('verb')) {
            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $templateMgr->registerPlugin('function', 'plugin_url', array($this, 'smartyPluginUrl'));
                $apiOptions = [
                    OrcidClient::ORCID_API_URL_PUBLIC => 'plugins.generic.scieloScreening.settings.orcidAPIPath.public',
                    OrcidClient::ORCID_API_URL_PUBLIC_SANDBOX => 'plugins.generic.scieloScreening.settings.orcidAPIPath.publicSandbox',
                    OrcidClient::ORCID_API_URL_MEMBER => 'plugins.generic.scieloScreening.settings.orcidAPIPath.member',
                    OrcidClient::ORCID_API_URL_MEMBER_SANDBOX => 'plugins.generic.scieloScreening.settings.orcidAPIPath.memberSandbox'
                ];
                $templateMgr->assign('orcidApiUrls', $apiOptions);

                $form = new ScieloScreeningSettingsForm($this, $contextId);
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }
                return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
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

    public function getPluginFullPath(): string
    {
        $request = Application::get()->getRequest();
        return $request->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath();
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

        if ($this->userIsAuthor($submission)) {
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

            $templateMgr->addJavaScript(
                'scieloScreeningEditSubmissionWizard',
                $this->getPluginFullPath() . '/js/EditSubmissionWizard.js',
                [
                    'priority' => TemplateManager::STYLE_SEQUENCE_LAST,
                    'contexts' => ['backend']
                ]
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
        }

        return false;
    }

    public function validateSubmissionFields($hookName, $params)
    {
        $errors = &$params[0];
        $submission = $params[1];
        $context = Application::get()->getRequest()->getContext();
        $publication = $submission->getCurrentPublication();
        $contributorsErrors = $errors['contributors'] ?? [];
        $filesErrors = $errors['files'] ?? [];

        $documentChecker = $this->getDocumentChecker($submission);
        $orcidClient = new OrcidClient($this, $context->getId());
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);
        $screeningChecker = new ScreeningChecker();
        $dataScreening = $screeningExecutor->getScreeningData($submission);

        if (!$dataScreening['statusAffiliation']) {
            $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.affiliation');
        }

        if ($dataScreening['statusCreditRoles'] == 'NotOkay') {
            $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.creditRoles');
        }

        if ($dataScreening['statusUppercaseAuthors']) {
            $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.uppercaseContributors');
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

        $authors = $publication->getData('authors')->toArray();
        if ($this->userIsAuthor($submission)) {
            $numberContributorsInformed = $publication->getData('numberContributors');
            if ($numberContributorsInformed != count($authors)) {
                $contributorsErrors[] = __('plugins.generic.scieloScreening.reviewStep.error.numberContributors');
            }
        }

        if (!empty($contributorsErrors)) {
            $errors['contributors'] = $contributorsErrors;
        }
        if (!empty($filesErrors)) {
            $errors['files'] = $filesErrors;
        }

        return false;
    }

    public function validateAuthorData($hookName, $params)
    {
        $errors = &$params[0];
        $author = $params[1];
        $props = $params[2];

        if (isset($props['creditRoles']) && empty($props['creditRoles'])) {
            $errors['creditRoles'] = [__('plugins.generic.scieloScreening.reviewStep.error.creditRoles')];
        }

        return Hook::CONTINUE;
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

        if ($step == 'files') {
            $documentChecker = $this->getDocumentChecker($submission);
            $orcidClient = new OrcidClient($this, $context->getId());
            $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);
            $dataScreening = $screeningExecutor->getScreeningData($submission);

            $templateMgr->assign($dataScreening);
            $output .= $templateMgr->fetch($this->getTemplateResource('reviewDocumentOrcids.tpl'));
        }
    }

    public function addToWorkFlow($hookName, $params)
    {
        $smarty = &$params[1];
        $output = &$params[2];
        $context = Application::get()->getRequest()->getContext();
        $submission = $smarty->getTemplateVars('submission');

        $documentChecker = $this->getDocumentChecker($submission);
        $orcidClient = new OrcidClient($this, $context->getId());
        $screeningExecutor = new ScreeningExecutor($documentChecker, $orcidClient);
        $dataScreening = $screeningExecutor->getScreeningData($submission);

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
        $screeningExecutor = new ScreeningExecutor(null, null);
        $statusAuthors = $screeningExecutor->getStatusAuthors($submission);
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

    private function getDocumentChecker($submission)
    {
        $galleys = $submission->getGalleys();

        if (count($galleys) > 0 && $galleys[0]->getFile()) {
            $galley = $galleys[0];
            $path = \Config::getVar('files', 'files_dir') . DIRECTORY_SEPARATOR . $galley->getFile()->getData('path');

            return new DocumentChecker($path);
        }

        return null;
    }

    public function userIsAuthor($submission)
    {
        $currentUser = Application::get()->getRequest()->getUser();
        $currentUserAssignedRoles = [];
        if ($currentUser) {
            $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
            $stageAssignmentsResult = $stageAssignmentDao->getBySubmissionAndUserIdAndStageId($submission->getId(), $currentUser->getId(), $submission->getData('stageId'));

            while ($stageAssignment = $stageAssignmentsResult->next()) {
                $userGroup = Repo::userGroup()->get($stageAssignment->getUserGroupId(), $submission->getData('contextId'));
                $currentUserAssignedRoles[] = (int) $userGroup->getRoleId();
            }
        }

        return !empty($currentUserAssignedRoles) and $currentUserAssignedRoles[0] == Role::ROLE_ID_AUTHOR;
    }
}
