<?php

use humhub\libs\Html;
use humhub\modules\installer\forms\DatabaseForm;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var DatabaseForm $model */
/* @var string $errorMessage */
?>

<div id="database-form" class="panel panel-default animated fadeIn">
    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Database</strong> Configuration'); ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Below you have to enter your database connection details. If you’re not sure about these, please contact your system administrator.'); ?></p>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'hostname') ?>
        <hr/>
        <?= $form->field($model, 'port') ?>
        <hr/>
        <?= $form->field($model, 'username') ?>
        <hr/>
        <?= $form->field($model, 'password') ?>
        <hr/>
        <?= $form->field($model, 'database') ?>
        <?= $form->field($model, 'create')->checkbox() ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger">
                <strong><?= Yii::t('InstallerModule.base', 'Ohh, something went wrong!'); ?></strong><br/>
                <?= Html::encode($errorMessage); ?>
            </div>
        <?php endif; ?>
        <hr/>

        <?= Html::submitButton(Yii::t('InstallerModule.base', 'Next'), ['class' => 'btn btn-primary', 'data-loader' => "modal", 'data-message' => Yii::t('InstallerModule.base', 'Initializing database...')]); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script <?= Html::nonce() ?>>

    $(function () {
        // set cursor to email field
        $('#hostname').focus();
    })

    // Shake panel after wrong validation
    <?php if ($model->hasErrors()): ?>
    $('#database-form').removeClass('fadeIn');
    $('#database-form').addClass('shake');
    <?php endif; ?>

</script>
