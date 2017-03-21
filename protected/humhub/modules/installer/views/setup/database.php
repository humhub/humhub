<?php

use humhub\compat\CActiveForm;
use yii\helpers\Html;

?>

<div id="database-form" class="panel panel-default animated fadeIn">
    <div class="panel-heading">
        <?= Yii::t('InstallerModule.views_setup_database', '<strong>Database</strong> Configuration'); ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.views_setup_database', 'Below you have to enter your database connection details. If you’re not sure about these, please contact your system administrator.'); ?></p>

        <?php $form = CActiveForm::begin(); ?>

        <hr>
        
        <div class="form-group">
            <?= $form->labelEx($model, 'hostname'); ?>
            <?= $form->textField($model, 'hostname', array('class' => 'form-control', 'id' => 'hostname')); ?>
            <p class="help-block"><?= Yii::t('InstallerModule.views_setup_database', 'Hostname of your MySQL Database Server (e.g. localhost if MySQL is running on the same machine)'); ?></p>
            <?= $form->error($model, 'hostname'); ?>
        </div>
        
        <hr>
        
        <div class="form-group">
            <?= $form->labelEx($model, 'username'); ?>
            <?= $form->textField($model, 'username', array('class' => 'form-control')); ?>
            <p class="help-block"><?= Yii::t('InstallerModule.views_setup_database', 'Your MySQL username'); ?></p>
            <?= $form->error($model, 'username'); ?>
        </div>
        
        <hr>
        
        <div class="form-group">
            <?= $form->labelEx($model, 'password'); ?>
            <?= $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
            <p class="help-block"><?= Yii::t('InstallerModule.views_setup_database', 'Your MySQL password.'); ?></p>
            <?= $form->error($model, 'password'); ?>
        </div>
        
        <hr>
        
        <div class="form-group">
            <?= $form->labelEx($model, 'database'); ?>
            <?= $form->textField($model, 'database', array('class' => 'form-control')); ?>
            <p class="help-block"><?= Yii::t('InstallerModule.views_setup_database', 'The name of the database you want to run HumHub in.'); ?></p>
            <?= $form->error($model, 'database'); ?>
        </div>

        <?php if ($errorMessage) { ?>
            <div class="alert alert-danger">
                <strong><?= Yii::t('InstallerModule.views_setup_database', 'Ohh, something went wrong!'); ?></strong><br>
                <?= Html::encode($errorMessage); ?>
            </div>
        <?php } ?>

        <hr>

        <?= Html::submitButton(Yii::t('InstallerModule.views_setup_database', 'Next'), array('class' => 'btn btn-primary', 'data-loader' => "modal", 'data-message' => Yii::t('InstallerModule.views_setup_database', 'Initializing database...'))); ?>

        <?php CActiveForm::end(); ?>
    </div>
</div>

<script>

$(function () {
    // set cursor to email field
    $('#hostname').focus();
})

// Shake panel after wrong validation
<?php if ($model->hasErrors()) { ?>
    $('#database-form').removeClass('fadeIn');
    $('#database-form').addClass('shake');
<?php } ?>

</script>