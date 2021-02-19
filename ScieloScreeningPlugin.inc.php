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

class ScieloScreeningPlugin extends GenericPlugin {

	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
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

			HookRegistry::register('LoadComponentHandler', array($this, 'setupScieloScreeningHandler'));
            HookRegistry::register('authorform::Constructor', array($this, 'changeAuthorForm'));
            HookRegistry::register('submissionsubmitstep4form::display', array($this, 'addToStep4'));
            HookRegistry::register('submissionsubmitstep2form::display', array($this, 'addToStep2'));
		}
		return $success;
	}

	function setupScieloScreeningHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.generic.scieloScreening.controllers.ScieloScreeningHandler') {
			return true;
		}
		return false;
    }
    
    function changeAuthorForm($hookName, $params){
        $path = "../../../plugins/generic/scieloScreening/templates/authorForm.tpl";
        
        $params[0]->setTemplate($path);
        $params[1] = $path;
    }

    function addToStep2($hookName, $params) {
        $output =& $params[1];
        $templateMgr = TemplateManager::getManager(null);

        if($output == "") {
            $output = $templateMgr->fetch($params[0]->getTemplate());
        }

        $checkNumberPDFs = $templateMgr->fetch($this->getTemplateResource('checkPDFStep2.tpl'));

        $this->insertTemplateIntoStep2($checkNumberPDFs, $output);
        return true;
    }

    function insertTemplateIntoStep2($template, &$step2) {
        $posInsert = strpos($step2, "<div id=\"formatsGridContainer");
        $newStep2 = substr_replace($step2, $template, $posInsert, 0);

        $step2 = $newStep2;
    }

    function addToStep4($hookName, $params){
        $output =& $params[1];
        $submission = $params[0]->submission;
        $templateMgr = TemplateManager::getManager(null);
        $outputWasEmpty = false;

        if($output == "") {
            $outputWasEmpty = true;
            $output = $templateMgr->fetch($params[0]->getTemplate());
        }

        $scieloScreeningHandler = new ScieloScreeningHandler();
        $dataScreening = $scieloScreeningHandler->getScreeningData($submission);
        $templateMgr->assign($dataScreening);
        $statusScreening = $templateMgr->fetch($this->getTemplateResource('statusScreeningStep4.tpl'));

        $this->insertTemplateIntoStep4($statusScreening, $output);
        if(!$outputWasEmpty) return true;
    }

    private function insertTemplateIntoStep4($template, &$step4) {
        $posInsert = strpos($step4, "<p>");
        $newStep4 = substr_replace($step4, $template, $posInsert, 0);

        $step4 = $newStep4;
    }

    public function getDisplayName() {
		return __('plugins.generic.scieloScreening.displayName');
	}

	public function getDescription() {
		return __('plugins.generic.scieloScreening.description');
	}

	function metadataFieldEdit($hookName, $params) {
		$smarty =& $params[1];
        $output =& $params[2];

        $submissionId = $smarty->smarty->get_template_vars('submissionId');
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($submissionId);
        
        $dois = DAORegistry::getDAO('DOIScreeningDAO')->getBySubmissionId($submissionId);
        $authors = $submission->getAuthors();

        $smarty->assign([
            'roleId' => $authors[0]->getUserGroup()->getRoleId(),
            'dois' => $dois
        ]);
        
		$output .= $smarty->fetch($this->getTemplateResource('editDOISubmission.tpl'));
		return false;
	}

	function addToPublicationForms($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
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
    
    function addGalleysWarning($hookName, $params) {
        $smarty =& $params[1];
		$output =& $params[2];
        
        $output .= sprintf('%s', $smarty->fetch($this->getTemplateResource('addGalleysWarning.tpl')));
    }

	function listRules($hookName, $args) {
		$rules =& $args[0];
		$pluginRules['hasPublishedBefore'] = 
			"<p>" . $this->getDisplayName() . "<br />\n" . 
			$this->getDescription() . "</p>\n";
		$rules = array_merge($rules, $pluginRules);
		return $rules;
	}

	function validate($hookName, $args) {
		$errors =& $args[0];
        $submission = $args[2];
        $scieloScreeningHandler = new ScieloScreeningHandler();
        $statusAuthors = $scieloScreeningHandler->getStatusAuthors($submission);
        $okayForPublishing = true;
        
        if(!$statusAuthors['statusAffiliation']) {
            $errors = array_merge(
                $errors,
                array('affiliationForAll' => __('plugins.generic.scieloScreening.required.affiliationForAll'))
            );
            $okayForPublishing = false;
        }

        if($this->userIsAuthor($submission) && !$statusAuthors['statusOrcid']){
            $errors = array_merge(
                $errors,
                array('orcidLeastOne' => __('plugins.generic.scieloScreening.required.orcidLeastOne'))
            );
            $okayForPublishing = false;
        }
        
        return $okayForPublishing;
	}

    function getInstallMigration() {
        $this->import('classes.migration.DOIScreeningMigration');
        return new DOIScreeningMigration();
    }

    function userIsAuthor($submission){
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
