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
			//HookRegistry::register('Template::Workflow::Publication', array($this, 'addToPublicationForms'));

			HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
            HookRegistry::register('authorform::Constructor', array($this, 'changeAuthorForm'));
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
        $authors = $submission->getAuthors();

        $smarty->assign([
            'authorGroupId' => $authors[0]->getUserGroupId(),
            'authors' => $submission->getAuthors()
        ]);
        
		$output .= $smarty->fetch($this->getTemplateResource('editDOISubmission.tpl'));
		return false;
	}

	/*function addToPublicationForms($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
        $submission = $smarty->get_template_vars('submission');
        
        $passData = [
            'submissionId' => $submission->getId(),
            'authors' => $submission->getAuthors()
        ];
        
        $doiScreeningDAO = new DOIScreeningDAO();
        $dois = $doiScreeningDAO->getBySubmissionId($submission->getId());

        if(count($dois) > 0){
            $passData['firstDOI'] = $dois[0];
            $passData['secondDOI'] = $dois[1];
            
            if(count($dois) == 3)
                $passData['thirdDOI'] = $dois[2];
        }
        
		$smarty->assign($passData);
		$output .= sprintf(
			'<tab id="doiScreeningInWorkflow" label="%s">%s</tab>',
			__('plugins.generic.authorDOIScreening.nome'),
			$smarty->fetch($this->getTemplateResource('editDOIForm.tpl'))
		);
		
		return false;
	}*/

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
        
        $authors = $submission->getAuthors();

        foreach ($authors as $author) {   
            if($author->getLocalizedAffiliation() == ""){
                $errors = array_merge(
                    $errors,
                    array('affiliationForAll' => __('plugins.generic.authorDOIScreening.required.affiliationForAll'))
                );
                return false;
            }
        }
        return true;
	}

    /**
	 * @copydoc Plugin::getInstallSchemaFile()
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'schema.xml';
	}

}
