<?php

namespace humhub\widgets\modal;

use humhub\widgets\form\ActiveForm;

class FormModal extends Modal
{
    public static function beginFormDialog($config = []): ActiveForm
    {
        $form = ActiveForm::begin($config['form'] ?? []);

        parent::beginDialog($config);
        return $form;
    }

    public static function endFormDialog()
    {
        ActiveForm::end();
        parent::endDialog();
    }
}
