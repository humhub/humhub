<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['id' => 'authentication-settings-form']); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'allowGuestAccess')->checkbox(); ?>

    <?= $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>

    <?= $form->field($model, 'internalUsersCanInvite')->checkbox(); ?>

    <?= $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>

    <?= $form->field($model, 'defaultUserGroup')->dropDownList($groups, ['readonly' => Setting::IsFixed('auth.defaultUserGroup', 'user')]); ?>

    <?= $form->field($model, 'MinUserNameLength')->textInput(['readonly' => Setting::IsFixed('auth.MinUserNameLength', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication', 'Min value is 4. If not set, Minimum Username Length is 4'); ?></p>

    <?= $form->field($model, 'MinPasswordLength')->textInput(['readonly' => Setting::IsFixed('auth.MinPasswordLength', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication', 'Min value is 5. If not set, Minimum Password Length is 5'); ?></p>

    <?= $form->field($model, 'activePasswordSecurity')->checkbox(); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication', 'The password must contains characters from all of the following categories : Uppercase, Lowercase, Number and Special'); ?></p>

    <?= $form->field($model, 'defaultUserIdleTimeoutSec')->textInput(['readonly' => Setting::IsFixed('auth.defaultUserIdleTimeoutSec', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication', 'Min value is 20 seconds. If not set, session will timeout after 1400 seconds (24 minutes) regardless of activity (default session timeout)'); ?></p>

    <?= $form->field($model, 'defaultUserProfileVisibility')->dropDownList([1 => Yii::t('AdminModule.views_setting_authentication', 'Visible for members only'), 2 => Yii::t('AdminModule.views_setting_authentication', 'Visible for members+guests')], ['readonly' => (!Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess'))]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication', 'Only applicable when limited access for non-authenticated users is enabled. Only affects new users.'); ?></p>

    <hr>

    <?= CHtml::submitButton(Yii::t('AdminModule.views_setting_authentication', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?= \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>
