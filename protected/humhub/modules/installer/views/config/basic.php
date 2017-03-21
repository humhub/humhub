<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.views_config_basic', 'Social Network <strong>Name</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?= Yii::t('InstallerModule.views_config_basic', 'Of course, your new social network needs a name. Please change the default name with one you like. (For example the name of your company, organization or club)'); ?></p>

        <?php $form = CActiveForm::begin(); ?>

        <div class="form-group">
            <?= $form->labelEx($model, 'name'); ?>
            <?= $form->textField($model, 'name', array('class' => 'form-control')); ?>
            <?= $form->error($model, 'name'); ?>
        </div>

        <hr>

        <?= CHtml::submitButton(Yii::t('InstallerModule.views_config_basic', 'Next'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

        <?php CActiveForm::end(); ?>
    </div>
</div>

<script>

$(function () {
    // set cursor to email field
    $('#ConfigBasicForm_name').focus();
})

// Shake panel after wrong validation
<?php if ($model->hasErrors()) { ?>
    $('#name-form').removeClass('fadeIn');
    $('#name-form').addClass('shake');
<?php } ?>

</script>


