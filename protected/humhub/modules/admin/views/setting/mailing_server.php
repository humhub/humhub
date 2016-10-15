<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;

?>
<?php $this->beginContent('@admin/views/setting/_emailLayout.php') ?>

<?php $form = CActiveForm::begin(); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'systemEmailAddress'); ?>
    <?php echo $form->textField($model, 'systemEmailAddress', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.systemEmailAddress'))); ?>
</div>


<div class="form-group">
    <?php echo $form->labelEx($model, 'systemEmailName'); ?>
    <?php echo $form->textField($model, 'systemEmailName', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.systemEmailName'))); ?>
</div>


<div class="form-group">
    <?php echo $form->labelEx($model, 'transportType'); ?>
    <?php echo $form->dropDownList($model, 'transportType', $transportTypes, array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.transportType'))); ?>
</div>

<div id="smtpOptions">
    <hr>
    <h4> <?php echo Yii::t('AdminModule.views_setting_mailing_server', 'SMTP Options'); ?> </h4>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'hostname'); ?>
        <?php echo $form->textField($model, 'hostname', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.hostname'))); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'username'); ?>
        <?php echo $form->textField($model, 'username', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.username'))); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'password'); ?>
        <?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.password'))); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'port'); ?>
        <?php echo $form->textField($model, 'port', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.port'))); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'encryption'); ?>
        <?php echo $form->dropDownList($model, 'encryption', $encryptionTypes, array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.encryption'))); ?>
    </div>

    <div id="encryptionOptions">
        <div class="form-group">
            <strong>Encryption Options</strong>
            <div class="checkbox">
                <label>
                    <?php echo $form->checkbox($model, 'allowSelfSignedCerts', array('class' => 'form-control', 'readonly' => Setting::IsFixed('mailer.allowSelfSignedCerts'))); ?>
                    <?php echo $model->getAttributeLabel('allowSelfSignedCerts'); ?>
                </label>
            </div>
        </div>
    </div>
</div>
<hr>
<?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_mailing_server', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?php echo \humhub\widgets\DataSaved::widget(); ?>
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


