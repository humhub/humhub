<?php

use humhub\libs\Html;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \humhub\modules\admin\models\forms\MailingSettingsForm */
/* @var \humhub\components\SettingsManager $settings */

?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->errorSummary($model); ?>

<?= $form->field($model, 'systemEmailName')->textInput(['readonly' => $settings->isFixed('mailer.systemEmailName')]); ?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'systemEmailAddress')->textInput(['readonly' => $settings->isFixed('mailer.systemEmailAddress')]); ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'systemEmailReplyTo')->textInput(['readonly' => $settings->isFixed('mailer.systemEmailReplyTo')]); ?>
    </div>

</div>

<?= $form->field($model, 'transportType')->dropDownList($model->getTransportTypes(), ['readonly' => $settings->isFixed('mailer.transportType')]); ?>


<div id="smtpOptions">

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'hostname')->textInput(['readonly' => $settings->isFixed('mailer.hostname')]); ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'port')->textInput(['readonly' => $settings->isFixed('mailer.port')]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'useSmtps')->checkbox(); ?>
        </div>
        <div class="col-md-6" id="encryptionOptions">
            <?= $form->field($model, 'allowSelfSignedCerts')->checkbox(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'username')->textInput(['readonly' => $settings->isFixed('mailer.username')]); ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'password')->textInput(['readonly' => $settings->isFixed('mailer.password')])->passwordInput(); ?>
        </div>
    </div>
</div>
<div id="dsnOptions">
    <?= $form->field($model, 'dsn')->textInput(['readonly' => $settings->isFixed('mailer.dsn')]); ?>

    <strong><?= Yii::t('AdminModule.settings', 'DSN Examples:'); ?></strong>
    <ul>
        <li>SMTP <code>smtp://user:pass@smtp.example.com:25</code></li>
        <li>Sendmail <code>sendmail://default</code></li>
        <li>Amazon SES <code>ses+smtp://USERNAME:PASSWORD@default</code>, <code>ses+https://ACCESS_KEY:SECRET_KEY@default</code>,
            <code>ses+api://ACCESS_KEY:SECRET_KEY@default</code></li>
        <li>Google Gmail <code>gmail+smtp://USERNAME:PASSWORD@default</code></li>
        <li>Mailchimp Mandrill <code>mandrill+smtp://USERNAME:PASSWORD@default</code>
            <code>mandrill+https://KEY@default</code></li>
        <code>mandrill+api://KEY@default</code></li>
        <li>Mailgun <code>mailgun+smtp://USERNAME:PASSWORD@default</code>,
            <code>mailgun+https://KEY:DOMAIN@default</code>,
            <code>mailgun+api://KEY:DOMAIN@default</code></li>
        <li>Mailjet <code>mailjet+smtp://ACCESS_KEY:SECRET_KEY@default</code>, <code>mailjet+api://ACCESS_KEY:SECRET_KEY@default</code>
        </li>
        <li>Postmark <code>postmark+smtp://ID@default</code>, <code>postmark+api://KEY@default</code></li>
        <li>Sendgrid <code>sendgrid+smtp://KEY@default</code>, <code>sendgrid+api://KEY@default</code></li>
        <li>Sendinblue <code>sendinblue+smtp://USERNAME:PASSWORD@default</code>,
            <code>sendinblue+api://KEY@default</code></li>
        <li>OhMySMTP <code>ohmysmtp+smtp://API_TOKEN@default</code></li>
    </ul>

    <?= Yii::t('AdminModule.settings', 'You can find more configuration options here:'); ?> <a
        href="https://symfony.com/doc/5.4/mailer.html#transport-setup" target="_blank">Symfony Mailer - Transport
        Setup</a>

</div>
<hr>

<?= Button::primary(Yii::t('AdminModule.settings', 'Save & Test'))->submit() ?>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>

<script <?= Html::nonce() ?>>
    if ($("#mailingsettingsform-transporttype option:selected").val() != 'smtp') {
        $("#smtpOptions").hide();
    }
    if ($("#mailingsettingsform-transporttype option:selected").val() != 'dsn') {
        $("#dsnOptions").hide();
    }

    $('#mailingsettingsform-transporttype').on('change', function () {
        if ($("#mailingsettingsform-transporttype option:selected").val() != 'smtp') {
            $("#smtpOptions").hide();
        } else {
            $("#smtpOptions").show();
        }
        if ($("#mailingsettingsform-transporttype option:selected").val() != 'dsn') {
            $("#dsnOptions").hide();
        } else {
            $("#dsnOptions").show();
        }
    });

    if (!$("#mailingsettingsform-usesmtps").prop("checked")) {
        $("#encryptionOptions").hide();
    }

    $('#mailingsettingsform-usesmtps').on('change', function () {
        if (!$("#mailingsettingsform-usesmtps").prop("checked")) {
            $("#encryptionOptions").hide();
        } else {
            $("#encryptionOptions").show();
        }
    });
</script>
