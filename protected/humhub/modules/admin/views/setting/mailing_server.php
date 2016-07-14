<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_mailing_server', '<strong>Mailing</strong> settings'); ?></div>
    <div class="panel-body">

        <ul class="nav nav-pills">
            <li><a
                    href="<?php echo Url::to(['mailing']); ?>"><?php echo Yii::t('AdminModule.views_setting_mailing_server', 'Defaults'); ?></a>
            </li>
            <li class="active">
                <a href="<?php echo Url::to(['mailing-server']); ?>"><?php echo Yii::t('AdminModule.views_setting_mailing_server', 'Server Settings'); ?></a>
            </li>
        </ul>
        <br />


        <?php $form = CActiveForm::begin(); ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'systemEmailAddress'); ?>
            <?php echo $form->textField($model, 'systemEmailAddress', array('class' => 'form-control', 'readonly' => Setting::IsFixed('systemEmailAddress', 'mailing'))); ?>
        </div>


        <div class="form-group">
            <?php echo $form->labelEx($model, 'systemEmailName'); ?>
            <?php echo $form->textField($model, 'systemEmailName', array('class' => 'form-control', 'readonly' => Setting::IsFixed('systemEmailName', 'mailing'))); ?>
        </div>


        <div class="form-group">
            <?php echo $form->labelEx($model, 'transportType'); ?>
            <?php echo $form->dropDownList($model, 'transportType', $transportTypes, array('class' => 'form-control', 'readonly' => Setting::IsFixed('transportType', 'mailing'))); ?>
        </div>

        <div id="smtpOptions">
            <hr>
            <h4> <?php echo Yii::t('AdminModule.views_setting_mailing_server', 'SMTP Options'); ?> </h4>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'hostname'); ?>
                <?php echo $form->textField($model, 'hostname', array('class' => 'form-control', 'readonly' => Setting::IsFixed('hostname', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'username'); ?>
                <?php echo $form->textField($model, 'username', array('class' => 'form-control', 'readonly' => Setting::IsFixed('username', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'password'); ?>
                <?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'readonly' => Setting::IsFixed('password', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'port'); ?>
                <?php echo $form->textField($model, 'port', array('class' => 'form-control', 'readonly' => Setting::IsFixed('port', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'encryption'); ?>
                <?php echo $form->dropDownList($model, 'encryption', $encryptionTypes, array('class' => 'form-control', 'readonly' => Setting::IsFixed('encryption', 'mailing'))); ?>
            </div>

            <div id="encryptionOptions">
                <div class="form-group">
                    <strong>Encryption Options</strong>
                    <div class="checkbox">
                        <label>
                            <?php echo $form->checkbox($model, 'allowSelfSignedCerts', array('class' => 'form-control', 'readonly' => Setting::IsFixed('allowSelfSignedCerts', 'mailing'))); ?>
                            <?php echo $model->getAttributeLabel('allowSelfSignedCerts'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_mailing_server', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>
        <?php CActiveForm::end(); ?>

    </div>
</div>

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



