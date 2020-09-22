<?php

use humhub\compat\CActiveForm;
use humhub\libs\Html;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', 'Social Network <strong>Name</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.base', 'Of course, your new social network needs a name. Please change the default name with one you like. (For example the name of your company, organization or club)'); ?></p>


        <?php $form = CActiveForm::begin(); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'name'); ?>
            <?php echo $form->textField($model, 'name', ['class' => 'form-control']); ?>
            <?php echo $form->error($model, 'name'); ?>
        </div>

        <hr>

        <?php echo Html::submitButton(Yii::t('InstallerModule.base', 'Next'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?php CActiveForm::end(); ?>
    </div>
</div>

<script <?= Html::nonce() ?>>

    $(function () {
        // set cursor to email field
        $('#ConfigBasicForm_name').focus();
    });

    // Shake panel after wrong validation
<?php if ($model->hasErrors()) { ?>
        $('#name-form').removeClass('fadeIn');
        $('#name-form').addClass('shake');
<?php } ?>

</script>


