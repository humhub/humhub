<div class="panel-heading">
    <?php echo Yii::t('UserModule.account', '<strong>Email</strong> Notifications'); ?>
</div>
<div class="panel-body">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => false,
    ));
    ?>

    <?php echo $form->errorSummary($model); ?>

    <strong><?php echo Yii::t('UserModule.account', 'Notifications'); ?></strong><br />

    <?php echo Yii::t('UserModule.account', 'Get an email, when other users comment or like your posts.'); ?>
    <?php
    echo $form->dropDownList($model, 'receive_email_notifications', array(
        User::RECEIVE_EMAIL_NEVER => 'Never',
        User::RECEIVE_EMAIL_WHEN_OFFLINE => 'When I´m Offline',
        User::RECEIVE_EMAIL_ALWAYS => 'Always',
    ), array('id' => 'reg_group', 'class' => 'form-control'));
    ?>

    <hr>


    <strong><?php echo Yii::t('UserModule.account', 'Activities'); ?></strong><br />
    <?php echo Yii::t('UserModule.account', 'Get an email, by every activity from other users you follow or work<br>together in workspaces.'); ?>
    <?php
    echo $form->dropDownList($model, 'receive_email_activities', array(
        User::RECEIVE_EMAIL_NEVER => Yii::t('UserModule.base', 'Never'),
        User::RECEIVE_EMAIL_DAILY_SUMMARY => Yii::t('UserModule.base', 'Daily summary'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('UserModule.base', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('UserModule.base', 'Always'),
    ), array('id' => 'reg_group', 'class' => 'form-control'));
    ?>

    <hr>

    <?php echo CHtml::submitButton(Yii::t('base', 'Save'), array('class' => 'btn btn-primary')); ?>

    <!-- show flash message after saving -->
    <?php $this->widget('application.widgets.DataSavedWidget'); ?>

    <?php $this->endWidget(); ?>
</div>


