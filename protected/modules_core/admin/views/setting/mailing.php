<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_mailing', '<strong>Mailing</strong> defaults'); ?></div>
    <div class="panel-body">

        <ul class="nav nav-pills">
            <li class="active"><a
                    href="<?php echo $this->createUrl('mailing'); ?>"><?php echo Yii::t('AdminModule.views_setting_mailing', 'Defaults'); ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('mailingServer'); ?>"><?php echo Yii::t('AdminModule.views_setting_mailing', 'Server Settings'); ?></a>
            </li>
        </ul>

        <br />

        <p><?php echo Yii::t('AdminModule.views_setting_mailing', 'Define defaults when a user receive e-mails about notifications or new activities. This settings can be overwritten by users in account settings.'); ?></p>

        <br />

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'mailing-defaults-form',
            'enableAjaxValidation' => false,
        ));
        ?>

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

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_mailing', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>
    </div>


</div> 