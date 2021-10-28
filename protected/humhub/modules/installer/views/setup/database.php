<?php

use humhub\libs\Html;
use humhub\modules\installer\forms\DatabaseForm;
use humhub\modules\ui\form\widgets\ActiveForm;

/**
 * @var DatabaseForm $model
 * @var string $errorMessage
 */


?>

<div id="database-form" class="panel panel-default animated fadeIn">
    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Database</strong> Configuration'); ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Below you have to enter your database connection details. If you’re not sure about these, please contact your system administrator.'); ?></p>

        <?php $form = ActiveForm::begin(); ?>
        <hr/>

        <?= $form->field($model, 'hostname')->textInput(); ?>
        <p class="help-block"><?= Yii::t('InstallerModule.base', 'Hostname of your MySQL Database Server (e.g. localhost if MySQL is running on the same machine)'); ?></p>
        <hr/>

        <?= $form->field($model, 'port')->textInput(); ?>
        <p class="help-block"><?= Yii::t('InstallerModule.base', 'Optional: Port of your MySQL Database Server. Leave empty to use default port.'); ?></p>
        <hr/>

        <?= $form->field($model, 'username')->textInput(); ?>
        <p class="help-block"><?= Yii::t('InstallerModule.base', 'Your MySQL username'); ?></p>
        <hr/>

        <?= $form->field($model, 'password')->passwordInput(); ?>
        <p class="help-block"><?= Yii::t('InstallerModule.base', 'Your MySQL password.'); ?></p>
        <hr/>

        <?= $form->field($model, 'database')->textInput(); ?>
        <p class="help-block"><?= Yii::t('InstallerModule.base', 'The name of the database you want to run HumHub in.'); ?></p>
        <hr/>

        <?= $form->field($model, 'create')->checkbox(); ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger">
                <strong><?= Yii::t('InstallerModule.base', 'Ohh, something went wrong!'); ?></strong><br/>
                <?= Html::encode($errorMessage); ?>
            </div>
        <?php endif; ?>
        <hr/>

        <?= Html::submitButton(Yii::t('InstallerModule.base', 'Next'), ['class' => 'btn btn-primary', 'data-loader' => "modal", 'data-message' => Yii::t('InstallerModule.base', 'Initializing database...')]); ?>

        <?php $form::end(); ?>
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
