<?php
/**
 * @file plugins/generic/authorDOIScreening/AuthorDOIScreeningPlugin.inc.php
 *
 * @class AuthorDOIScreeningPlugin
 * @ingroup plugins_generic_authorDOIScreening
 *
 * @brief Plugin class for the DefaultScreening plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.authorDOIScreening.classes.DOIScreeningDAO');

class AuthorDOIScreeningPlugin extends GenericPlugin {

	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled($mainContextId)) {
			$doiScreeningDAO = new DOIScreeningDAO();
			DAORegistry::registerDAO('DOIScreeningDAO', $doiScreeningDAO);

			// By default OPS installation will not allow authors to publish. Override the default so that custom publishing rulesets can be used.
			//\HookRegistry::register('Publication::canAuthorPublish', [$this, 'setAuthorCanPublish']);

			// Add a new ruleset for publishing
			\HookRegistry::register('Publication::validatePublish', [$this, 'validate']);

			// Show plugin rules for editors in settings
			\HookRegistry::register('Settings::Workflow::listScreeningPlugins', [$this, 'listRules']);

			// Adds the DOI Form to submission
			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldEdit'));
			HookRegistry::register('Template::Workflow::Publication', array($this, 'addToPublicationForms'));

			HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
            HookRegistry::register('authorform::Constructor', array($this, 'changeAuthorForm'));
            HookRegistry::register('submissionsubmitstep4form::display', array($this, 'addToStep4'));
		}
		return $success;
	}

	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.generic.authorDOIScreening.controllers.grid.DOIGridHandler') {
			import($component);
			DOIGridHandler::setPlugin($this);
			return true;
		}
		return false;
    }
    
    function changeAuthorForm($hookName, $params){
        $path = "../../../plugins/generic/authorDOIScreening/templates/authorForm.tpl";
        
        $params[0]->setTemplate($path);
        $params[1] = $path;
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

        $dataScreening = $this->getScreeningDataSubmission($submission);
        $templateMgr->assign($dataScreening);
        $statusScreening = $templateMgr->fetch($this->getTemplateResource('statusScreeningStep4.tpl'));

        $this->insertTemplateIntoStep4($statusScreening, $output);
        if(!$outputWasEmpty) return true;
    }

    private function getScreeningDataSubmission($submission){
        $dataScreening = array();
        $publication = $submission->getCurrentPublication();
        
        $doiScreeningDAO = new DOIScreeningDAO();
        $dois = $doiScreeningDAO->getBySubmissionId($submission->getId());
        if(count($dois) == 0) {
            $dataScreening['doiNotDone'] = true;
            $dataScreening['errorsScreening'] = true;
        }

        $authors = $submission->getAuthors();
        foreach ($authors as $author) {   
            if($author->getLocalizedAffiliation() == ""){
                $dataScreening['authorWithoutAffiliation'] = true;
                $dataScreening['errorsScreening'] = true;
                break;
            }
        }

        $metadataList = array('title', 'abstract', 'keywords');
        $textMetadata = "";
        foreach ($metadataList as $metadata) {
            if($publication->getData($metadata, 'en_US') == "") {
                $dataScreening['metadataNotEnglish'] = true;
                $dataScreening['errorsScreening'] = true;

                if($textMetadata != "") $textMetadata .= ", ";
                $textMetadata .= __("common." . $metadata);
            }
        }
        $dataScreening['textMetadata'] = $textMetadata;
        
        $numPDFs = 0;
        if(count($submission->getGalleys()) > 0) {
            foreach ($submission->getGalleys() as $galley) {
                if(strtolower($galley->getLabel()) == 'pdf'){
                    $numPDFs++;
                }
            }
        }

        $dataScreening['numPDFs'] = $numPDFs;
        if($numPDFs == 0 || $numPDFs > 1) {
            $dataScreening['errorsScreening'] = true;
        }

        return $dataScreening;
    }

    private function insertTemplateIntoStep4($template, &$step4) {
        $posInsert = strpos($step4, "<p>");
        $newStep4 = substr_replace($step4, $template, $posInsert, 0);

        $step4 = $newStep4;
    }

    public function getDisplayName() {
		return __('plugins.generic.authorDOIScreening.displayName');
	}

	public function getDescription() {
		return __('plugins.generic.authorDOIScreening.description');
	}

	/*function setAuthorCanPublish($hookName, $args) {
		return true;
	}*/

	function metadataFieldEdit($hookName, $params) {
		$smarty =& $params[1];
        $output =& $params[2];

        $submissionId = $smarty->smarty->get_template_vars('submissionId');
        $submission = DAORegistry::getDAO('SubmissionDAO')->getById($submissionId);
        
        $dois = DAORegistry::getDAO('DOIScreeningDAO')->getBySubmissionId($submissionId);
        $authors = $submission->getAuthors();

        $smarty->assign([
            'roleId' => $authors[0]->getUserGroup()->getRoleId(),
            'authors' => $submission->getAuthors(),
            'dois' => $dois
        ]);
        
		$output .= $smarty->fetch($this->getTemplateResource('editDOISubmission.tpl'));
		return false;
	}

	function addToPublicationForms($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
        $submission = $smarty->get_template_vars('submission');
        $publication = $submission->getCurrentPublication();
        $passData = array();

        /* DOI*/
        $doiScreeningDAO = new DOIScreeningDAO();
        $dois = $doiScreeningDAO->getBySubmissionId($submission->getId());

        $passData['flagDOI'] = (count($dois) > 0);
        $passData['dois'] = $dois;

        /* Afiliação e ORCID */
        $authors = $submission->getAuthors();
        $flagAf = true;
        $flagOrcid = false;
        $listAuthors = array();

        foreach ($authors as $author) {   
            if($author->getLocalizedAffiliation() == ""){
                $flagAf = false;
                $listAuthors[] = $author->getLocalizedGivenName() . " " . $author->getLocalizedFamilyName();
            }

            if($author->getOrcid() != ''){
                $flagOrcid = true;
            }
        }

        $passData['flagAf'] = $flagAf;
        $passData['listAuthors'] = $listAuthors;
        $passData['flagOrcid'] = $flagOrcid;
        
        /* Metadados em inglês */
        $metadataList = array('title', 'abstract', 'keywords');
        $flagMetadataEnglish = true;
        $textMetadata = "";
        foreach ($metadataList as $metadata) {
            if($publication->getData($metadata, 'en_US') == "") {
                $flagMetadataEnglish = false;

                if($textMetadata != "") $textMetadata .= ", ";
                $textMetadata .= __("common." . $metadata);
            }
        }
        $passData['flagMetadataEnglish'] = $flagMetadataEnglish;
        $passData['textMetadata'] = $textMetadata;

		$smarty->assign($passData);
		$output .= sprintf(
			'<tab id="screeningInfo" label="%s">%s</tab>',
			__('plugins.generic.authorDOIScreening.info.name'),
			$smarty->fetch($this->getTemplateResource('screeningInfo.tpl'))
		);
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
        $publication = $args[1];
        $submission = $args[2];
        $affAll = true;
        $orcidOne = false;
        $authors = $submission->getAuthors();

        foreach ($authors as $author) {   
            if($author->getLocalizedAffiliation() == ""){
                $errors = array_merge(
                    $errors,
                    array('affiliationForAll' => __('plugins.generic.authorDOIScreening.required.affiliationForAll'))
                );
                $affAll = false;
            }
        }

        if($this->userIsAuthor($submission)){
            foreach ($authors as $author){
                if($author->getOrcid() != ''){
                    $orcidOne = true;
                }
            }
            
            if(!$orcidOne){
                $errors = array_merge(
                    $errors,
                    array('orcidLeastOne' => __('plugins.generic.authorDOIScreening.required.orcidLeastOne'))
                );
            }
            
            return $affAll && $orcidOne;
        }
        else {
            return $affAll;
        }
	}

    /**
	 * @copydoc Plugin::getInstallSchemaFile()
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'schema.xml';
	}

    function userIsAuthor($submission){
        $currentUser = \Application::get()->getRequest()->getUser();
        $currentUserAssignedRoles = array();
        if ($currentUser) {
            $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
            $stageAssignmentsResult = $stageAssignmentDao->getBySubmissionAndUserIdAndStageId($submission->getId(), $currentUser->getId(), $stageId);
            $userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
            while ($stageAssignment = $stageAssignmentsResult->next()) {
                $userGroup = $userGroupDao->getById($stageAssignment->getUserGroupId(), $contextId);
                $currentUserAssignedRoles[] = (int) $userGroup->getRoleId();
            }
        }

        return $currentUserAssignedRoles[0] == ROLE_ID_AUTHOR;
    }
}
