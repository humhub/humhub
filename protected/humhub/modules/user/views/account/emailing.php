<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use humhub\modules\user\models\User;

?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
    <?php $form = ActiveForm::begin(); ?>

    <label class="control-label" for="accountemailing-receive_email_notifications">
        <?php echo $model->attributeLabels()['receive_email_notifications']; ?>
    </label>

    <div class="help-block">
        <?php echo Yii::t('UserModule.views_account_emailing', 'Get an email, when other users comment or like your posts.'); ?>
    </div>

    <?php echo $form->field($model, 'receive_email_notifications')->dropdownList([User::RECEIVE_EMAIL_NEVER => Yii::t('UserModule.views_account_emailing', 'Never'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('UserModule.views_account_emailing', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('UserModule.views_account_emailing', 'Always')])->label(false); ?>

    <label class="control-label" for="accountemailing-receive_email_activities">
        <?php echo $model->attributeLabels()['receive_email_activities']; ?>
    </label>
    
    <div class="help-block">
        <?php echo Yii::t('UserModule.views_account_emailing', 'Get an email, for every activity of other users you follow or work together in a workspace.'); ?>
    </div>
    
    <?php echo $form->field($model, 'receive_email_activities')->dropdownList([
        User::RECEIVE_EMAIL_NEVER => Yii::t('UserModule.views_account_emailing', 'Never'),
        User::RECEIVE_EMAIL_DAILY_SUMMARY => Yii::t('UserModule.views_account_emailing', 'Daily summary'),
        User::RECEIVE_EMAIL_WHEN_OFFLINE => Yii::t('UserModule.views_account_emailing', 'When I´m offline'),
        User::RECEIVE_EMAIL_ALWAYS => Yii::t('UserModule.views_account_emailing', 'Always')])->label(false); ?>

    
    <?php echo $form->field($model, 'enable_html5_desktop_notifications')->checkbox(); ?>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_emailing', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent() ?>


