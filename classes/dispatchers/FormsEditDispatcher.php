<?php

namespace APP\plugins\generic\scieloScreening\classes\dispatchers;

use PKP\plugins\Plugin;
use PKP\plugins\Hook;
use APP\core\Application;
use APP\template\TemplateManager;
use APP\facades\Repo;
use APP\plugins\generic\scieloScreening\classes\ScreeningChecker;

class FormsEditDispatcher
{
    private Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->registerHooks();
    }

    private function registerHooks(): void
    {
        Hook::add('Form::config::after', [$this, 'editFormComponents']);
        Hook::add('preprintgalleyform::display', [$this, 'modifyGalleyForm']);
        Hook::add('preprintgalleyform::validate', [$this, 'modifyGalleyFormValidation']);
    }

    public function editFormComponents($hookName, $params)
    {
        $formConfig = &$params[0];
        $form = $params[1];

        if ($formConfig['id'] == 'contributor') {
            $formConfig = $this->addRequirementForAffiliation($formConfig);
        } elseif ($formConfig['id'] == 'titleAbstract') {
            $formConfig = $this->removeFieldsOfFormComponent($formConfig, ['prefix', 'subtitle']);
        } elseif ($formConfig['id'] == 'metadata') {
            $publication = $form->publication;
            $submission = Repo::submission()->get($publication->getData('submissionId'));
            if ($this->plugin->userIsAuthor($submission)) {
                $formConfig = $this->removeFieldsOfFormComponent($formConfig, ['supportingAgencies']);
            }
        }

        return Hook::CONTINUE;
    }

    private function addRequirementForAffiliation($formConfig)
    {
        foreach ($formConfig['fields'] as &$field) {
            if ($field['name'] == 'affiliation') {
                $field['isRequired'] = true;
                break;
            }
        }
        return $formConfig;
    }

    private function removeFieldsOfFormComponent($formConfig, $fieldsToRemove)
    {
        $filteredFields = array_filter($formConfig['fields'], function ($field) use ($fieldsToRemove) {
            return !in_array($field['name'], $fieldsToRemove);
        });
        $formConfig['fields'] = array_values($filteredFields);
        return $formConfig;
    }

    public function modifyGalleyForm($hookName, $params)
    {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->registerFilter("output", [$this, 'removeFieldsFromGalleyFormFilter']);
    }

    public function removeFieldsFromGalleyFormFilter($output, $templateMgr)
    {
        if (preg_match('/id="preprintGalleyForm"/', $output)) {
            preg_match('/<\/form>/', $output, $matches, PREG_OFFSET_CAPTURE);

            $posMatch = $matches[0][1];
            $removeGalleyFields = $templateMgr->fetch($this->plugin->getTemplateResource('removeGalleyFields.tpl'));
            $output = substr_replace($output, $removeGalleyFields, $posMatch, 0);

            $templateMgr->unregisterFilter('output', [$this, 'removeFieldsFromGalleyFormFilter']);
        }

        return $output;
    }

    public function modifyGalleyFormValidation($hookName, $params)
    {
        $form = &$params[0];
        $submission = $form->_submission;

        if (!empty($submission->getData('submissionProgress')) || !empty($form->_preprintGalley)) {
            return Hook::CONTINUE;
        }

        $checker = new ScreeningChecker();
        $galleys = $submission->getGalleys();
        $galleysFiletypes = array_map(function ($galley) {
            return ($galley->getFileType());
        }, $galleys);

        if ($checker->checkNumberPdfs($galleysFiletypes)[0]) {
            $form->addErrorField('preprintGalleyFormNotification');
            $form->addError('preprintGalleyFormNotification', __("plugins.generic.scieloScreening.screeningRules.numPdfs"));
        }

        return Hook::CONTINUE;
    }
}
