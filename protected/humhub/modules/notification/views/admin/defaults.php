<?php
use yii\widgets\ActiveForm;
/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'Notification Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('NotificationModule.base', 'Notifications are sent directly to your users to inform them about new activities in your network.'); ?><br />
        <?= Yii::t('NotificationModule.base', 'In this view, you can define the default behavior for your users. These settings can be overwritten by users in their account settings page.'); ?>
        <br />
    </div>
    <?php $form = ActiveForm::begin() ?>
        <?= humhub\modules\notification\widgets\NotificationSettingsForm::widget([
            'model' => $model,
            'form' => $form,
            'showSpaces' => true
        ]) ?>
        <br />
        <button type="submit" class="btn btn-primary" data-ui-loader><?= Yii::t('base', 'Save');?></button>
    <?php ActiveForm::end(); ?> 
</div>

