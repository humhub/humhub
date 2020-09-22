<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>

<div class="panel-heading">
    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Settings'); ?>
</div>
<div class="panel-body">
    <div class="help-block">
        <?= Yii::t('NotificationModule.base', 'Notifications are sent instantly to you to inform you about new activities in your network.'); ?><br />
        <?= Yii::t('NotificationModule.base', 'This view allows you to configure your notification settings by selecting the desired targets for the given notification categories.'); ?>
    </div>
    
    <?php $form = ActiveForm::begin(); ?>

    <?= humhub\modules\notification\widgets\NotificationSettingsForm::widget([
        'model' => $model,
        'form' => $form
    ]) ?>
    
    <br />
    <button type="submit" class="btn btn-primary" data-ui-loader><?= Yii::t('base', 'Save'); ?></button>
    <?php if ($model->isUserSettingLoaded()): ?>
        <a href="#" class="btn btn-default pull-right" data-action-click="post" data-action-url="<?= Url::to(['reset']) ?>" data-ui-loader><?= Yii::t('ActivityModule.base', 'Reset to defaults')?></a>
    <?php endif; ?>
<?php ActiveForm::end(); ?>
</div>


