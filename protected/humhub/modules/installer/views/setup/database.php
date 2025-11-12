<?php

use humhub\helpers\Html;
use humhub\modules\installer\forms\DatabaseForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

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

        <?= $form->field($model, 'hostname')->textInput(['readonly' => $model->isFixed('hostname')]) ?>
        <hr/>
        <?= $form->field($model, 'port')->textInput(['readonly' => $model->isFixed('port')]) ?>
        <hr/>
        <?= $form->field($model, 'username')->textInput(['readonly' => $model->isFixed('username')]) ?>
        <hr/>
        <?= $form->field($model, 'password')->passwordInput(['readonly' => $model->isFixed('password')]) ?>
        <hr/>
        <?= $form->field($model, 'database')->textInput(['readonly' => $model->isFixed('database')]) ?>
        <?= $form->field($model, 'create')->checkbox() ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger">
                <strong><?= Yii::t('InstallerModule.base', 'Ohh, something went wrong!'); ?></strong><br/>
                <?= Html::encode($errorMessage); ?>
            </div>
        <?php endif; ?>
        <hr/>

        <?= Button::primary(Yii::t('InstallerModule.base', 'Next'))
            ->loader(Yii::t('InstallerModule.base', 'Initializing database...'))
            ->submit() ?>

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
