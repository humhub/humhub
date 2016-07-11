<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['id' => 'authentication-settings-form']); ?>


    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'allowGuestAccess')->checkbox(); ?>


    <?php echo $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>

    <?php echo $form->field($model, 'internalUsersCanInvite')->checkbox(); ?>

    <?php echo $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>

    <?php echo $form->field($model, 'defaultUserGroup')->dropdownList($groups, ['readonly' => Setting::IsFixed('auth.defaultUserGroup', 'user')]); ?>

    <?php echo $form->field($model, 'defaultUserIdleTimeoutSec')->textInput(['readonly' => Setting::IsFixed('auth.defaultUserIdleTimeoutSec', 'user')]); ?>
    <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication', 'Min value is 20 seconds. If not set, session will timeout after 1400 seconds (24 minutes) regardless of activity (default session timeout)'); ?></p>

    <?php echo $form->field($model, 'defaultUserProfileVisibility')->dropdownList([1 => Yii::t('AdminModule.views_setting_authentication', 'Visible for members only'), 2 => Yii::t('AdminModule.views_setting_authentication', 'Visible for members+guests')], ['readonly' => (!Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess'))]); ?>
    <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication', 'Only applicable when limited access for non-authenticated users is enabled. Only affects new users.'); ?></p>

    <hr>

    <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_authentication', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

    <?php echo \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>