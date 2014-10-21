<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_emailing', '<strong>Email</strong> Notifications'); ?>
</div>
<div class="panel-body">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => false,
    ));
    ?>

    <?php echo $form->errorSummary($model); ?>

    <strong><?php echo Yii::t('UserModule.views_account_emailing', 'Notifications'); ?></strong><br />

    <?php echo Yii::t('UserModule.views_account_emailing', 'Get an email, when other users comment or like your posts.'); ?>
    <?php
    echo $form->dropDownList($model, 'receive_email_notifications', array(
        User::RECEIVE_EMAIL_NEVER => Yii::t('UserModule.views_account_emailing', 'Never'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('UserModule.views_account_emailing', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('UserModule.views_account_emailing', 'Always'),
    ), array('id' => 'reg_group', 'class' => 'form-control'));
    ?>

    <hr>


    <strong><?php echo Yii::t('UserModule.views_account_emailing', 'Activities'); ?></strong><br />
    <?php echo Yii::t('UserModule.views_account_emailing', 'Get an email, by every activity from other users you follow or work<br>together in workspaces.'); ?>
    <?php
    echo $form->dropDownList($model, 'receive_email_activities', array(
        User::RECEIVE_EMAIL_NEVER => Yii::t('UserModule.views_account_emailing', 'Never'),
        User::RECEIVE_EMAIL_DAILY_SUMMARY => Yii::t('UserModule.views_account_emailing', 'Daily summary'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('UserModule.views_account_emailing', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('UserModule.views_account_emailing', 'Always'),
    ), array('id' => 'reg_group', 'class' => 'form-control'));
    ?>

    <hr>

    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_emailing', 'Save'), array('class' => 'btn btn-primary')); ?>

    <!-- show flash message after saving -->
    <?php $this->widget('application.widgets.DataSavedWidget'); ?>

    <?php $this->endWidget(); ?>
</div>


