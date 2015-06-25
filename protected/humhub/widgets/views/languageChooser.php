<?php

use \humhub\compat\CActiveForm;
?>
<div class="text text-center animated fadeIn">
    <?php echo Yii::t('base', "Choose language:"); ?> &nbsp;
    <div class="langSwitcher">
        <?php $form = CActiveForm::begin(['id' => 'choose-language-form']); ?>
        <?php echo $form->dropDownList($model, 'language', $languages, array('onChange' => 'this.form.submit()')); ?>
        <?php CActiveForm::end(); ?>
    </div>
</div>