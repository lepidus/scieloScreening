<?php

use APP\plugins\generic\scieloScreening\ScieloScreeningPlugin;
use APP\plugins\generic\scieloScreening\ScieloScreeningSettingsForm;
use PHPUnit\Framework\TestCase;

class SettingsFormTest extends TestCase
{
    public function testSavingGloballyConfiguredOrcidDoesNotOverwriteLocalSettings(): void
    {
        $form = $this->getMockBuilder(ScieloScreeningSettingsForm::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['orcidIsGloballyConfigured'])
            ->getMock();
        $form->method('orcidIsGloballyConfigured')->willReturn(true);
        $plugin = $this->createMock(ScieloScreeningPlugin::class);
        $plugin->expects($this->never())->method('updateSetting');
        $form->plugin = $plugin;
        $form->execute();
    }
}
