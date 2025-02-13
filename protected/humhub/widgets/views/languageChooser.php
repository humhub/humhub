<?php

use humhub\models\forms\ChooseLanguage;
use humhub\widgets\form\ActiveForm;

/**
 * @var $languages array
 * @var $model ChooseLanguage
 * @var $vertical bool
 */

$flexClasses = $vertical ? 'flex-column' : 'justify-content-center align-items-center';
?>

<div id="language-chooser" class="d-flex <?= $flexClasses ?> w-100 gap-2 text animated fadeIn">
    <?php if (count($languages) > 1) : ?>
        <div id="language-chooser-label">
            <?= Yii::t('base', "Choose language:") ?>
        </div>
        <div id="language-chooser-form">
            <?php $form = ActiveForm::begin(['id' => 'choose-language-form']); ?>
                <?= $form->field($model, 'language')->dropDownList($languages, ['data-action-change' => 'ui.form.submit'])->label(false) ?>
            <?php $form::end(); ?>
        </div>
    <?php endif; ?>
</div>
