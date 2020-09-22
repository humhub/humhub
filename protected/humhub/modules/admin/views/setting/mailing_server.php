<?php

use humhub\libs\Html;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var $this \yii\web\View */
/* @var $transportTypes array */
/* @var $encryptionTypes array */
/* @var \humhub\components\SettingsManager $settings */

?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<?= $form->field($model, 'systemEmailAddress')->textInput(['readonly' => $settings->isFixed('mailer.systemEmailAddress')]); ?>
<?= $form->field($model, 'systemEmailName')->textInput(['readonly' => $settings->isFixed('mailer.systemEmailName')]); ?>
<?= $form->field($model, 'transportType')->dropDownList($transportTypes, ['readonly' => $settings->isFixed('mailer.transportType')]); ?>


<div id="smtpOptions">
    <hr>
    <h4> <?= Yii::t('AdminModule.settings', 'SMTP Options'); ?> </h4>

    <?= $form->field($model, 'hostname')->textInput(['readonly' => $settings->isFixed('mailer.hostname')]); ?>
    <?= $form->field($model, 'username')->textInput(['readonly' => $settings->isFixed('mailer.username')]); ?>
    <?= $form->field($model, 'password')->textInput(['readonly' => $settings->isFixed('mailer.password')])->passwordInput(); ?>
    <?= $form->field($model, 'port')->textInput(['readonly' => $settings->isFixed('mailer.port')]); ?>
    <?= $form->field($model, 'encryption')->dropDownList($encryptionTypes, ['readonly' => $settings->isFixed('mailer.encryption')]); ?>

    <div id="encryptionOptions">
        <?= $form->field($model, 'allowSelfSignedCerts')->checkbox(); ?>
    </div>
</div>
<hr>

<?= Button::primary(Yii::t('AdminModule.settings', 'Save & Test'))->submit() ?>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>

<script <?= Html::nonce() ?>>
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
