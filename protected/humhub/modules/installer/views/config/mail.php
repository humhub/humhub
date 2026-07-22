<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\SettingsManager;
use humhub\helpers\Html;
use humhub\modules\installer\forms\MailingSettingsForm;
use humhub\widgets\form\ActiveForm;
use yii\helpers\Url;

/* @var MailingSettingsForm $model */
/* @var SettingsManager $settings */

$transportTypeIsFixed = $settings->isFixed('mailerTransportType');
$testUrl = Url::to(['/installer/config/mail-test']);
?>
<div id="mail-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', 'E-Mail <strong>Delivery</strong>'); ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Working email delivery is required for account activation, invitations, notifications and password recovery. Configure it now, or keep the default and adjust it later in the administration.'); ?></p>

        <?php $form = ActiveForm::begin(['id' => 'installer-mail-form']); ?>

        <?= $form->field($model, 'systemEmailName')->textInput(['readonly' => $settings->isFixed('mailerSystemEmailName')]) ?>

        <div class="container gx-0 overflow-x-hidden">
            <div class="row">
                <div class="col-lg-6">
                    <?= $form->field($model, 'systemEmailAddress')->textInput(['readonly' => $settings->isFixed('mailerSystemEmailAddress')]) ?>
                </div>
                <div class="col-lg-6">
                    <?= $form->field($model, 'systemEmailReplyTo')->textInput(['readonly' => $settings->isFixed('mailerSystemEmailReplyTo')]) ?>
                </div>
            </div>
        </div>

        <?= $form->field($model, 'transportType')->dropDownList($model->getTransportTypes(), ['disabled' => $transportTypeIsFixed, 'readonly' => $transportTypeIsFixed]) ?>

        <div id="smtpOptions">
            <div class="container gx-0 overflow-x-hidden">
                <div class="row">
                    <div class="col-lg-8">
                        <?= $form->field($model, 'hostname')->textInput(['readonly' => $settings->isFixed('mailerHostname')]) ?>
                    </div>
                    <div class="col-lg-4">
                        <?= $form->field($model, 'port')->textInput(['readonly' => $settings->isFixed('mailerPort')]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <?= $form->field($model, 'useSmtps')->checkbox() ?>
                    </div>
                    <div class="col-lg-6" id="encryptionOptions">
                        <?= $form->field($model, 'allowSelfSignedCerts')->checkbox() ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <?= $form->field($model, 'username')->textInput(['readonly' => $settings->isFixed('mailerUsername')]) ?>
                    </div>
                    <div class="col-lg-6">
                        <?= $form->field($model, 'password')->textInput(['readonly' => $settings->isFixed('mailerPassword')])->passwordInput() ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="dsnOptions">
            <?= $form->field($model, 'dsn')->textInput(['readonly' => $settings->isFixed('mailerDsn')]) ?>
            <p class="help-block">
                <?= Yii::t('InstallerModule.base', 'You can find more configuration options here:') ?>
                <a href="https://symfony.com/doc/current/mailer.html#transport-setup" target="_blank">Symfony Mailer</a>
            </p>
        </div>

        <hr>

        <?= $form->field($model, 'testEmail', [
            'template' => "{label}\n<div class=\"input-group\">{input}\n<button type=\"button\" id=\"mail-test-btn\" class=\"btn btn-default\">" . Html::encode(Yii::t('InstallerModule.base', 'Test')) . "</button>\n</div>\n{error}\n{hint}",
        ])->textInput() ?>

        <div id="mail-test-result" class="mb-3"></div>

        <hr>

        <?= Html::submitButton(Yii::t('InstallerModule.base', 'Next'), ['id' => 'mail-next-btn', 'class' => 'btn btn-primary']); ?>

        <?php $form::end(); ?>
    </div>
</div>

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

    var mailTestAttempted = false;
    var $mailTestEmail = $('#mailingsettingsform-testemail');

    function sendTestEmail() {
        mailTestAttempted = true;

        var $btn = $('#mail-test-btn');
        var $result = $('#mail-test-result');
        var busy = <?= json_encode(Yii::t('InstallerModule.base', 'Sending test email…')) ?>;
        var failed = <?= json_encode(Yii::t('InstallerModule.base', 'Could not send the test email.')) ?>;

        $btn.prop('disabled', true);
        $result.html($('<div>').addClass('alert alert-info').text(busy));

        $.post('<?= $testUrl ?>', $('#installer-mail-form').serialize())
            .done(function (data) {
                var cls = (data && data.success) ? 'alert-success' : 'alert-danger';
                var msg = (data && data.message) ? data.message : failed;
                $result.html($('<div>').addClass('alert ' + cls).text(msg));
            })
            .fail(function () {
                $result.html($('<div>').addClass('alert alert-danger').text(failed));
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
    }

    $('#mail-test-btn').on('click', sendTestEmail);

    // Re-arm the reminder whenever the recipient changes.
    $mailTestEmail.on('input', function () {
        mailTestAttempted = false;
    });

    // Guard against the common mistake of typing a test recipient and then
    // pressing "Next" instead of "Test": offer to send the test instead of
    // continuing. Confirm = send test (stay), cancel = continue. Never blocks.
    // Hooked on the button click (not the form submit) so it also short-circuits
    // ActiveForm's own submit handling, and covers Enter via implicit submission.
    $('#mail-next-btn').on('click', function (e) {
        if (!mailTestAttempted && $.trim($mailTestEmail.val()) !== '') {
            if (confirm(<?= json_encode(Yii::t('InstallerModule.base', 'You entered a test recipient but haven’t sent a test email yet. Send the test email now instead of continuing?')) ?>)) {
                e.preventDefault();
                sendTestEmail();
            }
        }
    });

    <?php if ($model->hasErrors()): ?>
    $('#mail-form').removeClass('fadeIn').addClass('shake');
    <?php endif; ?>
</script>
