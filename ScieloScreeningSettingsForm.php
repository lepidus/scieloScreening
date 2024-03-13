<?php

/**
 * @file ScieloScreeningSettingsForm.inc.php
 *
 * Copyright (c) 2024 SciELO
 * Copyright (c) 2024 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ScieloScreeningSettingsForm
 * @ingroup plugins_generic_scieloScreening
 *
 * @brief Form for site admins to modify SciELO Screening plugin settings
 */

namespace APP\plugins\generic\scieloScreening;

use PKP\form\Form;
use APP\template\TemplateManager;
use APP\core\Application;
use PKP\config\Config;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorPost;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorCustom;

class ScieloScreeningSettingsForm extends Form
{
    public const CONFIG_VARS = array(
        'orcidAPIPath' => 'string',
        'orcidClientId' => 'string',
        'orcidClientSecret' => 'string',
    );

    public $contextId;
    public $plugin;

    public function __construct($plugin, $contextId)
    {
        $this->contextId = $contextId;
        $this->plugin = $plugin;
        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));

        if (!$this->orcidIsGloballyConfigured()) {
            $this->addCheck(new FormValidator($this, 'orcidAPIPath', 'required', 'plugins.generic.scieloScreening.settings.orcidAPIPathRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'orcidClientId', 'required', 'plugins.generic.scieloScreening.settings.orcidClientIdError', function ($clientId) {
                return $this->validateClientId($clientId);
            }));
            $this->addCheck(new FormValidatorCustom($this, 'orcidClientSecret', 'required', 'plugins.generic.scieloScreening.settings.orcidClientSecretError', function ($clientSecret) {
                return $this->validateClientSecret($clientSecret);
            }));
        }
    }

    public function initData()
    {
        $contextId = $this->contextId;
        $plugin = &$this->plugin;
        $this->_data = array();
        foreach (self::CONFIG_VARS as $configVar => $type) {
            $this->_data[$configVar] = $plugin->getSetting($contextId, $configVar);
        }
    }

    public function readInputData()
    {
        $this->readUserVars(array_keys(self::CONFIG_VARS));
    }

    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('globallyConfigured', $this->orcidIsGloballyConfigured());
        $templateMgr->assign('pluginName', $this->plugin->getName());
        $templateMgr->assign('applicationName', Application::get()->getName());
        return parent::fetch($request, $template, $display);
    }

    public function execute(...$functionArgs)
    {
        $plugin = &$this->plugin;
        $contextId = $this->contextId;
        foreach (self::CONFIG_VARS as $configVar => $type) {
            if ($configVar === 'orcidAPIPath') {
                $plugin->updateSetting($contextId, $configVar, trim($this->getData($configVar), "\"\';"), $type);
            } else {
                $plugin->updateSetting($contextId, $configVar, $this->getData($configVar), $type);
            }
        }

        parent::execute(...$functionArgs);
    }

    public function _checkPrerequisites()
    {
        $messages = array();

        $clientId = $this->getData('orcidClientId');
        if (!$this->validateClientId($clientId)) {
            $messages[] = __('plugins.generic.scieloScreening.settings.orcidClientIdError');
        }
        $clientSecret = $this->getData('orcidClientSecret');
        if (!$this->validateClientSecret($clientSecret)) {
            $messages[] = __('plugins.generic.scieloScreening.settings.orcidClientSecretError');
        }
        if (strlen($clientId) == 0 or strlen($clientSecret) == 0) {
            $this->plugin->setEnabled(false);
        }
        return $messages;
    }

    public function orcidIsGloballyConfigured(): bool
    {
        $apiUrl = Config::getVar('orcid', 'api_url');
        $clientId = Config::getVar('orcid', 'client_id');
        $clientSecret = Config::getVar('orcid', 'client_secret');
        return isset($apiUrl) && trim($apiUrl) && isset($clientId) && trim($clientId) &&
            isset($clientSecret) && trim($clientSecret);
    }

    private function validateClientId($str): bool
    {
        $valid = false;
        if (preg_match('/^APP-[\da-zA-Z]{16}|(\d{4}-){3,}\d{3}[\dX]/', $str) == 1) {
            $valid = true;
        }
        return $valid;
    }

    private function validateClientSecret($str): bool
    {
        $valid = false;
        if (preg_match('/^(\d|-|[a-f]){36,64}/', $str) == 1) {
            $valid = true;
        }
        return $valid;
    }
}
