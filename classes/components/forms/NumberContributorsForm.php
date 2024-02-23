<?php

namespace APP\plugins\generic\scieloScreening\classes\components\forms;

use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldText;
use APP\publication\Publication;

class NumberContributorsForm extends FormComponent
{
    public $id = 'numberContributorsForm';
    public $method = 'PUT';

    public function __construct(string $action, Publication $publication)
    {
        $this->action = $action;

        $this->addField(new FieldText('numberContributors', [
            'label' => __('plugins.generic.scieloScreening.section.numberContributors.name'),
            'isRequired' => true,
            'inpuType' => 'number',
            'value' => $publication->getData('numberContributors'),
        ]));
    }
}
