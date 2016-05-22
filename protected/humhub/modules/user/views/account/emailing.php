<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use humhub\modules\user\models\User;

?>
<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_emailing', '<strong>Desktop</strong> Notifications'); ?>
</div>
<div class="panel-body">
    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'enable_html5_desktop_notifications')->checkbox(); ?>

    <hr>
</div>

<div class="panel-heading" style="padding-top:0px;">
    <?php echo Yii::t('UserModule.views_account_emailing', '<strong>Email</strong> Notifications'); ?>
</div>
<div class="panel-body">
    <div class="help-block">
    <?php echo Yii::t('UserModule.views_account_emailing', 'Get an email, when other users comment or like your posts.'); ?>
    </div>

    <?php echo $form->field($model, 'receive_email_notifications')->dropdownList([User::RECEIVE_EMAIL_NEVER => Yii::t('UserModule.views_account_emailing', 'Never'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('UserModule.views_account_emailing', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('UserModule.views_account_emailing', 'Always')]); ?>
    <hr>

    <strong><?php echo Yii::t('UserModule.views_account_emailing', 'Activities'); ?></strong><br/>
    <div class="help-block">
        <?php echo Yii::t('UserModule.views_account_emailing', 'Get an email, by every activity from other users you follow or work together in workspaces.'); ?>
    </div>
    
    <?php echo $form->field($model, 'receive_email_activities')->dropdownList([
        User::RECEIVE_EMAIL_NEVER => Yii::t('UserModule.views_account_emailing', 'Never'),
        User::RECEIVE_EMAIL_DAILY_SUMMARY => Yii::t('UserModule.views_account_emailing', 'Daily summary'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('UserModule.views_account_emailing', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('UserModule.views_account_emailing', 'Always')]); ?>

    <hr>

    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_emailing', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>


