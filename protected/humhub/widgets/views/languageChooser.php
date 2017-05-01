<?php

use \humhub\compat\CActiveForm;
?>
<div class="text text-center animated fadeIn">
    <?php if (count($languages) > 1) : ?>
        <?= Yii::t('base', "Choose language:"); ?> &nbsp;
        <div class="langSwitcher inline-block">
            <?php $form = CActiveForm::begin(['id' => 'choose-language-form']); ?>
            <?= $form->dropDownList($model, 'language', $languages, ['onChange' => 'this.form.submit()', 'aria-label' => Yii::t('base', "Choose language:")]); ?>
            <?php CActiveForm::end(); ?>
        </div>
    <?php endif; ?>
</div>