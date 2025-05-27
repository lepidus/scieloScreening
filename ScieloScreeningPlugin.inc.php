<?php

/**
 * @file plugins/generic/scieloScreening/ScieloScreeningPlugin.inc.php
 *
 * @class ScieloScreeningPlugin
 * @ingroup plugins_generic_scieloScreening
 *
 * @brief Plugin class for the DefaultScreening plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.scieloScreening.classes.DOIScreeningDAO');
import('plugins.generic.scieloScreening.controllers.ScieloScreeningHandler');
import('plugins.generic.scieloScreening.classes.ScreeningChecker');

class ScieloScreeningPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }
        if ($success && $this->getEnabled($mainContextId)) {
            $doiScreeningDAO = new DOIScreeningDAO();
            DAORegistry::registerDAO('DOIScreeningDAO', $doiScreeningDAO);

            // Add a new ruleset for publishing
            \HookRegistry::register('Publication::validatePublish', [$this, 'validate']);

            // Show plugin rules for editors in settings
            \HookRegistry::register('Settings::Workflow::listScreeningPlugins', [$this, 'listRules']);

            // Adds the DOI Form to submission
            HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldEdit'));
            HookRegistry::register('Template::Workflow::Publication', array($this, 'addToPublicationForms'));
            HookRegistry::register('Template::Workflow::Publication', array($this, 'addGalleysWarning'));
            HookRegistry::register('Form::config::after', array($this, 'hidePrefixAndSubtitleFields'));

            HookRegistry::register('Schema::get::submission', [$this, 'addOurFieldsToSubmissionSchema']);
            HookRegistry::register('LoadComponentHandler', array($this, 'setupScieloScreeningHandler'));
            HookRegistry::register('authorform::Constructor', array($this, 'changeAuthorForm'));
            HookRegistry::register('submissionsubmitstep2form::validate', array($this, 'addValidationToStep2'));
            HookRegistry::register('submissionsubmitstep3form::display', array($this, 'addToStep3'));
            HookRegistry::register('submissionsubmitstep3form::validate', array($this, 'addValidationToStep3'));
            HookRegistry::register('submissionsubmitstep4form::display', array($this, 'addToStep4'));
        }
        return $success;
    }

    public function addOurFieldsToSubmissionSchema($hookName, $params)
    {
        $schema = &$params[0];

        $schema->properties->{'inputNumberAuthors'} = (object) [
            'type' => 'integer',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];
        $schema->properties->{'checkCantScreening'} = (object) [
            'type' => 'integer',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];

        return false;
    }

    public function setupScieloScreeningHandler($hookName, $params)
    {
        $component = &$params[0];
        if ($component == 'plugins.generic.scieloScreening.controllers.ScieloScreeningHandler') {
            return true;
        }
        return false;
    }

    public function changeAuthorForm($hookName, $params)
    {
        $path = "../../../plugins/generic/scieloScreening/templates/authorForm.tpl";

        $params[0]->setTemplate($path);
        $params[1] = $path;
    }

    public function addValidationToStep2($hookName, $params)
    {
        $form = &$params[0];
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

    public function addToStep3($hookName, $params)
    {
        $submission = $params[0]->submission;
        $request = PKPApplication::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign([
            'inputNumberAuthors' => $submission->getData('inputNumberAuthors'),
            'checkCantScreening' => $submission->getData('checkCantScreening')
        ]);

        return false;
    }

    public function addValidationToStep3($hookName, $params)
    {
        $form = &$params[0];
        $form->readUserVars(['inputNumberAuthors', 'checkCantScreening']);
        $submission = $form->submission;
        if (!$this->userIsAuthor($submission)) {
            return;
        }

        $inputNumberAuthors = (int) $form->getData('inputNumberAuthors');
        $checkCantScreening = (int) $form->getData('checkCantScreening');

        Services::get('submission')->edit(
            $submission,
            [
                'inputNumberAuthors' => $inputNumberAuthors,
                'checkCantScreening' => $checkCantScreening
            ],
            Application::get()->getRequest()
        );

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
        if ($checkCantScreening != 1 && !$doiScreeningDone) {
            $form->addErrorField('errorScreening');
            $form->addError('errorScreening', __("plugins.generic.scieloScreening.required.doiScreening"));
            return;
        }
    }

    public function addToStep4($hookName, $params)
    {
        $submission = $params[0]->submission;
        $request = PKPApplication::get()->getRequest();
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

    public function getDisplayName()
    {
        return __('plugins.generic.scieloScreening.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.scieloScreening.description');
    }

    public function metadataFieldEdit($hookName, $params)
    {
        $smarty = &$params[1];
        $output = &$params[2];

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

    public function addToPublicationForms($hookName, $params)
    {
        $smarty = &$params[1];
        $output = &$params[2];
        $submission = $smarty->get_template_vars('submission');
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
        $smarty = &$params[1];
        $output = &$params[2];

        $output .= sprintf('%s', $smarty->fetch($this->getTemplateResource('addGalleysWarning.tpl')));
    }

    public function hidePrefixAndSubtitleFields($hookName, $params)
    {
        $formConfig = &$params[0];
        $form = $params[1];

        if ($form->id !== 'titleAbstract' || !empty($form->errors)) {
            return;
        }

        $filteredFields = array_filter($formConfig['fields'], function ($field) {
            return $field['name'] != 'prefix' && $field['name'] != 'subtitle';
        });
        $formConfig['fields'] = array_values($filteredFields);
    }

    public function listRules($hookName, $args)
    {
        $rules = &$args[0];
        $pluginRules['hasPublishedBefore'] =
            "<p>" . $this->getDisplayName() . "<br />\n" .
            $this->getDescription() . "</p>\n";
        $rules = array_merge($rules, $pluginRules);
        return $rules;
    }

    public function validate($hookName, $args)
    {
        $errors = &$args[0];
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

    public function getInstallMigration()
    {
        $this->import('classes.migration.DOIScreeningMigration');
        return new DOIScreeningMigration();
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
