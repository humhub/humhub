<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\helpers\Url;

?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_authentication', '<strong>Authentication</strong> - Basic'); ?></div>
    <div class="panel-body">

        <ul class="nav nav-pills">
            <li class="active"><a
                    href="<?php echo Url::toRoute('authentication'); ?>"><?php echo Yii::t('AdminModule.views_setting_authentication', 'Basic'); ?></a>
            </li>
            <?php if (humhub\modules\user\libs\Ldap::isAvailable()): ?>
                <li>
                    <a href="<?php echo Url::toRoute('authentication-ldap'); ?>"><?php echo Yii::t('AdminModule.views_setting_authentication', 'LDAP'); ?></a>
                </li>
            <?php endif; ?>
        </ul>


        <br/>

        <?php $form = ActiveForm::begin(['id' => 'authentication-settings-form']); ?>


        <?php echo $form->errorSummary($model); ?>

        <?php echo $form->field($model, 'allowGuestAccess')->checkbox(); ?>


        <?php echo $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>

        <?php echo $form->field($model, 'internalUsersCanInvite')->checkbox(); ?>

        <?php echo $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>

        <?php echo $form->field($model, 'defaultUserGroup')->dropdownList($groups, ['readonly' => Setting::IsFixed('defaultUserGroup', 'authentication_internal')]); ?>

        <?php echo $form->field($model, 'defaultUserIdleTimeoutSec')->textInput(['readonly' => Setting::IsFixed('defaultUserIdleTimeoutSec', 'authentication_internal')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication', 'Min value is 20 seconds. If not set, session will timeout after 1400 seconds (24 minutes) regardless of activity (default session timeout)'); ?></p>

        <?php echo $form->field($model, 'defaultUserProfileVisibility')->dropdownList([1 => 'Visible for members only', 2 => 'Visible for members+guests'], ['readonly' => (!Setting::Get('allowGuestAccess', 'authentication_internal'))]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication', 'Only applicable when limited access for non-authenticated users is enabled. Only affects new users.'); ?></p>

        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_authentication', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>



