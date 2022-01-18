<?php

use humhub\libs\Html;
use humhub\modules\installer\forms\MailingForm;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var MailingForm $model */
?>

<div id="mailing-form" class="panel panel-default animated fadeIn">
    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', '<strong>SMTP</strong> Configuration'); ?>
    </div>

    <div class="panel-body">
        <p>
            <?php echo Yii::t('InstallerModule.base', 'Below you have to enter your SMTP connection details. If you’re not sure about these, please contact your system administrator.'); ?>
        </p>

        <?php $form = ActiveForm::begin(); ?>

        <hr/>
        <?= $form->field($model, 'systemEmailAddress') ?>
        <hr/>
        <?= $form->field($model, 'systemEmailName') ?>
        <hr/>
        <?= $form->field($model, 'transportType')->dropDownList(MailingForm::getTransportTypeOtions()) ?>
        <hr>

        <div id="smtpOptions">
            <h4> <?= Yii::t('AdminModule.settings', 'SMTP Options') ?> </h4>

            <?= $form->field($model, 'hostname') ?>
            <hr>
            <?= $form->field($model, 'port') ?>
            <hr>
            <?= $form->field($model, 'username') ?>
            <hr>
            <?= $form->field($model, 'password') ?>
            <hr>
            <?= $form->field($model, 'encryption')->dropDownList(MailingForm::getEncryptionOptions()) ?>

            <hr>
            <div id="encryptionOptions">
                <?= $form->field($model, 'allowSelfSignedCerts')->checkbox(); ?>
                <hr>
            </div>

        </div>

        <?= $form->field($model, 'sendTest')->checkbox() ?>
        <hr>

        <?php if ($errorMessage) { ?>
            <div class="alert alert-danger">
                <strong><?php echo Yii::t('InstallerModule.base', 'Ohh, something went wrong!'); ?></strong><br/>
                <?php echo Html::encode($errorMessage); ?>
            </div>
            <hr>
        <?php } ?>

        <?php echo Html::submitButton(Yii::t('InstallerModule.base', 'Next'), ['class' => 'btn btn-primary', 'data-loader' => "modal", 'data-message' => Yii::t('InstallerModule.base', 'Configuring mailer...')]); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script <?= Html::nonce() ?>>

    $(function () {
        // set cursor to email field
        $('#hostname').focus();
    })

    // Shake panel after wrong validation
    <?php if ($model->hasErrors()) { ?>
    $('#mailing-form').removeClass('fadeIn');
    $('#mailing-form').addClass('shake');
    <?php } ?>
    
    if ($("#mailingform-transporttype option:selected").val() != 'smtp') {
        $("#smtpOptions").hide();
    }

    $('#mailingform-transporttype').on('change', function () {
        if ($("#mailingform-transporttype option:selected").val() != 'smtp') {
            $("#smtpOptions").hide();
        } else {
            $("#smtpOptions").show();
        }
    });

    if ($("#mailingform-encryption option:selected").val() == '') {
        $("#encryptionOptions").hide();
    }

    $('#mailingform-encryption').on('change', function () {
        if ($("#mailingform-encryption option:selected").val() == '') {
            $("#encryptionOptions").hide();
        } else {
            $("#encryptionOptions").show();
        }
    });
    
</script>