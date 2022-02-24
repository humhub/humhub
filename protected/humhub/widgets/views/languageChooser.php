<?php

use humhub\modules\ui\form\widgets\ActiveForm;

/**
 * @var $languages array
 * @var $model \humhub\models\forms\ChooseLanguage
 */

?>

<div class="text text-center animated fadeIn">
    <?php if (count($languages) > 1) : ?>
        <?= Yii::t('base', "Choose language:"); ?> &nbsp;
        <div class="langSwitcher inline-block">
            <?php $form = ActiveForm::begin(['id' => 'choose-language-form']); ?>
            <?= $form->field($model, 'language')->dropDownList($languages, ['data-action-change' => 'ui.form.submit'])->label(false); ?>
            <?php $form::end(); ?>
        </div>
    <?php endif; ?>
</div>
