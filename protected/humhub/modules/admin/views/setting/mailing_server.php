<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
?>

<?php $this->beginContent('@admin/views/setting/_emailLayout.php'); ?>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <?= $form->labelEx($model, 'systemEmailAddress'); ?>
    <?= $form->textField($model, 'systemEmailAddress', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.systemEmailAddress'))); ?>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'systemEmailName'); ?>
    <?= $form->textField($model, 'systemEmailName', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.systemEmailName'))); ?>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'transportType'); ?>
    <?= $form->dropDownList($model, 'transportType', $transportTypes, array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.transportType'))); ?>
</div>

<div id="smtpOptions">
    <hr>
    <h4> <?= Yii::t('AdminModule.views_setting_mailing_server', 'SMTP Options'); ?> </h4>

    <div class="form-group">
        <?= $form->labelEx($model, 'hostname'); ?>
        <?= $form->textField($model, 'hostname', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.hostname'))); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($model, 'username'); ?>
        <?= $form->textField($model, 'username', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.username'))); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($model, 'password'); ?>
        <?= $form->passwordField($model, 'password', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.password'))); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($model, 'port'); ?>
        <?= $form->textField($model, 'port', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.port'))); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($model, 'encryption'); ?>
        <?= $form->dropDownList($model, 'encryption', $encryptionTypes, array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.encryption'))); ?>
    </div>

    <div id="encryptionOptions">
        <div class="form-group">
            <strong>Encryption Options</strong>
            <div class="checkbox">
                <label>
                    <?= $form->checkbox($model, 'allowSelfSignedCerts', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.allowSelfSignedCerts'))); ?>
                    <?= $model->getAttributeLabel('allowSelfSignedCerts'); ?>
                </label>
            </div>
        </div>
    </div>
</div>

<hr>

<?= CHtml::submitButton(Yii::t('AdminModule.views_setting_mailing_server', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<script>
    if ($("#mailingsettingsform-transporttype option:selected").val() != 'smtp') {
        $("#smtpOptions").hide();
    }

    $('#mailingsettingsform-transporttype').on('change', function () {
        if ($("#mailingsettingsform-transporttype option:selected").val() != 'smtp') {
            $("#smtpOptions").hide();
        } else {
            $("#smtpOptions").show();
        }
    });

    if ($("#mailingsettingsform-encryption option:selected").val() == '') {
        $("#encryptionOptions").hide();
    }

    $('#mailingsettingsform-encryption').on('change', function () {
        if ($("#mailingsettingsform-encryption option:selected").val() == '') {
            $("#encryptionOptions").hide();
        } else {
            $("#encryptionOptions").show();
        }
    });
</script>

<?php $this->endContent(); ?>