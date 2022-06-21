<?php

use humhub\modules\admin\models\forms\AuthenticationSettingsForm;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use yii\helpers\Html;

/* @var AuthenticationSettingsForm $model */

/* @var Module $userModule */
$userModule = Yii::$app->getModule('user');

?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['id' => 'authentication-settings-form', 'acknowledge' => true]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'allowGuestAccess')->checkbox(); ?>

    <?= $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>

    <?= $form->field($model, 'showCaptureInRegisterForm')->checkbox(); ?>

    <?= $form->field($model, 'internalUsersCanInvite')->checkbox(); ?>

    <?= $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>

    <?= $form->field($model, 'showRegistrationUserGroup')->checkbox(); ?>

    <?= $form->field($model, 'blockUsers')->checkbox(); ?>

    <?= $form->field($model, 'defaultUserIdleTimeoutSec')->textInput(['readonly' => $userModule->settings->isFixed('auth.defaultUserIdleTimeoutSec')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.user', 'Min value is 20 seconds. If not set, session will timeout after 1400 seconds (24 minutes) regardless of activity (default session timeout)'); ?></p>

    <?= $form->field($model, 'defaultUserProfileVisibility')->dropDownList(User::getVisibilityOptions(false), ['readonly' => (!Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess'))]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.user', 'Only applicable when limited access for non-authenticated users is enabled. Only affects new users.'); ?></p>

    <?php if (Yii::$app->getModule('user')->settings->get('auth.needApproval')): ?>
        <?= $form->field($model, 'registrationApprovalMailContent')->widget(RichTextField::class, ['exclude' => ['oembed', 'upload']]); ?>
        <?= $form->field($model, 'registrationDenialMailContent')->widget(RichTextField::class, ['exclude' => ['oembed', 'upload']]); ?>
        <p class="help-block"><?= Yii::t('AdminModule.user', 'Do not change placeholders like {displayName} if you want them to be automatically filled by the system. To reset the email content fields with the system default, leave them empty.'); ?></p>
    <?php endif; ?>

    <hr>

    <?= Html::submitButton(Yii::t('AdminModule.user', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?= \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>
