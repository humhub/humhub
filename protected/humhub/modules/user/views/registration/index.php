<?php

use humhub\libs\Html;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;

/**
 * @var $hForm Registration
 * @var $hasAuthClient bool
 * @var $showRegistrationForm bool
 */

$this->pageTitle = Yii::t('UserModule.auth', 'Create Account');
?>

<div class="container" style="text-align: center;">
    <?= SiteLogo::widget(['place' => 'login']) ?>
    <br/>
    <div class="row">
        <div id="create-account-form" class="panel panel-default animated bounceIn"
             data-has-auth-client="<?= $hasAuthClient ? '1' : '0' ?>"
             style="max-width: 500px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading">
                <?= Yii::t('UserModule.auth', '<strong>Account</strong> registration') ?>
            </div>
            <div class="panel-body">
                <?php if (!$hasAuthClient && AuthChoice::hasClients()): ?>
                    <?= AuthChoice::widget(['showOrDivider' => $showRegistrationForm]) ?>
                <?php endif; ?>

                <?php if ($showRegistrationForm): ?>
                    <?php $form = ActiveForm::begin(['id' => 'registration-form', 'enableClientValidation' => false]); ?>
                    <?= Html::hiddenInput('ChooseLanguage[language]', Yii::$app->language) ?>
                    <?= $hForm->render($form); ?>
                    <?php ActiveForm::end(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?= LanguageChooser::widget() ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        // set cursor to login field
        $('#User_username').focus();

        // set user time zone val
        $('#user-time_zone').val(Intl.DateTimeFormat().resolvedOptions().timeZone);
    })

    // Shake panel after wrong validation
    <?php foreach ($hForm->models as $model) : ?>
    <?php if ($model->hasErrors()) : ?>
    $('#create-account-form').removeClass('bounceIn');
    $('#create-account-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
    <?php endforeach; ?>

</script>
