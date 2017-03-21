<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\modules\user\models\User;
?>

<?php $this->beginContent('@admin/views/setting/_emailLayout.php'); ?>

<div class="help-block">
    <?= Yii::t('AdminModule.views_setting_mailing', 'Define the default behaviour for sending user e-mails. These settings can be overwritten by users in their account settings.'); ?>
</div>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <label class="control-label" for="auth_notification"><?= Yii::t('AdminModule.views_setting_mailing', 'Notifications'); ?>:</label>
    <?= $form->dropDownList($model, 'receive_email_notifications', array(
            User::RECEIVE_EMAIL_NEVER => Yii::t('AdminModule.views_setting_mailing', 'Never'),
            User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('AdminModule.views_setting_mailing', 'When I´m offline'),
            User::RECEIVE_EMAIL_ALWAYS => Yii::t('AdminModule.views_setting_mailing', 'Always'),
        ), array(
            'id' => 'auth_notification',
            'class' => 'form-control'
        ));
    ?>
</div>

<div class="help-block">
    <?= Yii::t('AdminModule.views_setting_mailing', 'Notifications are user related information (e.g. new comments on own posts or a new follower). Notifications will also be created when an user-action is required (e.g. friendship request).'); ?>
</div>

<div class="form-group">
    <label class="control-label" for="auth_activities"><?= Yii::t('AdminModule.views_setting_mailing', 'Activities'); ?>:</label>
    <?= $form->dropDownList($model, 'receive_email_activities', array(
        User::RECEIVE_EMAIL_NEVER => Yii::t('AdminModule.views_setting_mailing', 'Never'),
        User::RECEIVE_EMAIL_DAILY_SUMMARY => Yii::t('AdminModule.views_setting_mailing', 'Daily summary'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('AdminModule.views_setting_mailing', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('AdminModule.views_setting_mailing', 'Always')
    ), array(
        'id' => 'auth_activities',
        'class' => 'form-control'
    ));
    ?>
</div>

<div class="help-block">
    <?= Yii::t('AdminModule.views_setting_mailing', 'Activities provide an overview of taken actions in context of a space or other users. (e.g. a new post was written or a new member joined the space).'); ?>
</div>

<br>

<?= CHtml::submitButton(Yii::t('AdminModule.views_setting_mailing', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>

<?php CActiveForm::end(); ?>
<?php $this->endContent(); ?>