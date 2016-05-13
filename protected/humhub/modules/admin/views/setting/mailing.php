<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use yii\helpers\Url;
use humhub\modules\user\models\User;
?>
<?php $this->beginContent('@admin/views/setting/_emailLayout.php') ?>
<p><?php echo Yii::t('AdminModule.views_setting_mailing', 'Define defaults when a user receive e-mails about notifications or new activities. This settings can be overwritten by users in account settings.'); ?></p>

<br />


<?php $form = CActiveForm::begin(); ?>

<?php echo $form->errorSummary($model); ?>

<strong><?php echo Yii::t('AdminModule.views_setting_mailing', 'Notifications'); ?></strong><br />
<?php
echo $form->dropDownList($model, 'receive_email_notifications', array(
    User::RECEIVE_EMAIL_NEVER => Yii::t('AdminModule.views_setting_mailing', 'Never'),
    User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('AdminModule.views_setting_mailing', 'When I´m offline'),
    User::RECEIVE_EMAIL_ALWAYS => Yii::t('AdminModule.views_setting_mailing', 'Always'),
        ), array('id' => 'reg_group', 'class' => 'form-control'));
?>

<br />

<strong><?php echo Yii::t('AdminModule.views_setting_mailing', 'Activities'); ?></strong><br />
<?php
echo $form->dropDownList($model, 'receive_email_activities', array(
    User::RECEIVE_EMAIL_NEVER => Yii::t('AdminModule.views_setting_mailing', 'Never'),
    User::RECEIVE_EMAIL_DAILY_SUMMARY => Yii::t('AdminModule.views_setting_mailing', 'Daily summary'),
    User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('AdminModule.views_setting_mailing', 'When I´m offline'),
    User::RECEIVE_EMAIL_ALWAYS => Yii::t('AdminModule.views_setting_mailing', 'Always'),
        ), array('id' => 'reg_group', 'class' => 'form-control'));
?>

<br />

<?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_mailing', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?php echo \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>